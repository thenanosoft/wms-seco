<?php

namespace App\Services;

use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function addPurchaseLedgerEntry(array $data): StockLedger
    {
        return StockLedger::create([
            'txn_date' => $data['txn_date'],
            'txn_type' => 'PURCHASE',
            'ref_table' => 'purchases',
            'ref_id' => $data['ref_id'],
            'ref_line_id' => $data['ref_line_id'],
            'item_id' => $data['item_id'],
            'qty_in' => $data['qty_in'],
            'qty_out' => 0,
            'unit_price' => $data['unit_price'],
            'specification_snapshot' => $data['specification_snapshot'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }

    public function addIssueLedgerEntry(array $data): StockLedger
    {
        return StockLedger::create([
            'txn_date' => $data['txn_date'],
            'txn_type' => 'ISSUE',
            'ref_table' => 'issues',
            'ref_id' => $data['ref_id'],
            'ref_line_id' => $data['ref_line_id'],
            'item_id' => $data['item_id'],
            'qty_in' => 0,
            'qty_out' => $data['qty_out'],
            'unit_price' => $data['unit_price'],
            'specification_snapshot' => $data['specification_snapshot'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }


    public function getAvailableStock(int $itemId): float
    {
        $row = StockLedger::query()
            ->selectRaw('COALESCE(SUM(qty_in),0) as qty_in_sum, COALESCE(SUM(qty_out),0) as qty_out_sum')
            ->where('item_id', $itemId)
            ->first();

        $in = (float) ($row->qty_in_sum ?? 0);
        $out = (float) ($row->qty_out_sum ?? 0);

        return $in - $out;
    }

    public function stockSummary()
{
    return \App\Models\StockLedger::query()
        ->from('stock_ledger') // force correct table
        ->selectRaw('
            items.id as item_id,
            items.item_code,
            items.name as item_name,
            groups.group_code,
            COALESCE(SUM(stock_ledger.qty_in), 0) as total_in,
            COALESCE(SUM(stock_ledger.qty_out), 0) as total_out,
            (COALESCE(SUM(stock_ledger.qty_in), 0) - COALESCE(SUM(stock_ledger.qty_out), 0)) as balance
        ')
        ->join('items', 'items.id', '=', 'stock_ledger.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->groupBy(
            'items.id',
            'items.item_code',
            'items.name',
            'groups.group_code'
        )
        ->orderBy('groups.group_code')
        ->orderBy('items.item_code')
        ->get();
}



    public function getLastPurchasePrice(int $itemId): float
{
    $row = StockLedger::query()
        ->where('item_id', $itemId)
        ->where('txn_type', 'PURCHASE')
        ->orderByDesc('txn_date')
        ->orderByDesc('id')
        ->first();

    return $row ? (float) $row->unit_price : 0;
}

public function getAvailableStockDetailed(int $itemId): array
{
    $defaultThreshold = (float) \App\Models\AppSetting::get('default_low_stock_threshold', 0);

    $totals = StockLedger::query()
        ->where('item_id', $itemId)
        ->selectRaw('
            COALESCE(SUM(qty_in),0) as total_in,
            COALESCE(SUM(qty_out),0) as total_out,
            (COALESCE(SUM(qty_in),0) - COALESCE(SUM(qty_out),0)) as balance
        ')
        ->first();

    $totalIn = (float)($totals->total_in ?? 0);
    $totalOut = (float)($totals->total_out ?? 0);
    $balance = (float)($totals->balance ?? 0);

    // Weighted average purchase price
    $avgRow = StockLedger::query()
        ->where('item_id', $itemId)
        ->where('txn_type', 'PURCHASE')
        ->selectRaw('
            CASE
                WHEN COALESCE(SUM(qty_in),0) = 0 THEN 0
                ELSE COALESCE(SUM(qty_in * unit_price),0) / COALESCE(SUM(qty_in),0)
            END as avg_purchase_price
        ')
        ->first();

    $avgPurchase = (float)($avgRow->avg_purchase_price ?? 0);

    // Last purchase price
    $lastPurchase = (float) StockLedger::query()
        ->where('item_id', $itemId)
        ->where('txn_type', 'PURCHASE')
        ->orderByDesc('txn_date')
        ->orderByDesc('id')
        ->value('unit_price') ?? 0;

    // Low stock calculation (same logic as summary)
    $item = \App\Models\Item::query()->select(['id', 'low_stock_threshold'])->find($itemId);
    $thresholdUsed = $item && $item->low_stock_threshold !== null
        ? (float)$item->low_stock_threshold
        : $defaultThreshold;
    $isLow = $thresholdUsed > 0 ? ($balance <= $thresholdUsed) : false;

    return [
        'total_in' => $totalIn,
        'total_out' => $totalOut,
        'balance' => $balance,
        'available' => $balance,

        'avg_purchase_price' => round($avgPurchase, 4),
        'last_purchase_price' => round($lastPurchase, 4),

        'threshold_used' => $thresholdUsed,
        'is_low' => $isLow,
    ];
}

    public function addIssueReturnInLedger(array $data): StockLedger
    {
        return StockLedger::create([
            'txn_date' => $data['txn_date'],
            'txn_type' => 'ISSUE_RETURN_IN',
            'ref_table' => 'issue_returns',
            'ref_id' => $data['ref_id'],
            'ref_line_id' => $data['ref_line_id'],
            'item_id' => $data['item_id'],
            'qty_in' => $data['qty_in'],
            'qty_out' => 0,
            'unit_price' => $data['unit_price'],
            'specification_snapshot' => $data['specification_snapshot'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }
public function addReturnInLedger(array $data)
{
    return \App\Models\StockLedger::create([
        'txn_date' => $data['txn_date'],
        'txn_type' => 'RETURN_IN',
        'ref_table' => 'return_transactions',
        'ref_id' => $data['ref_id'],
        'ref_line_id' => $data['ref_line_id'],
        'item_id' => $data['item_id'],
        'qty_in' => $data['quantity'],
        'qty_out' => 0,
        'unit_price' => $data['unit_price'],
        'specification_snapshot' => $data['specification'],
        'created_by' => $data['created_by'],
    ]);
}

public function addReturnOutLedger(array $data)
{
    return \App\Models\StockLedger::create([
        'txn_date' => $data['txn_date'],
        'txn_type' => 'RETURN_OUT',
        'ref_table' => 'return_transactions',
        'ref_id' => $data['ref_id'],
        'ref_line_id' => $data['ref_line_id'],
        'item_id' => $data['item_id'],
        'qty_in' => 0,
        'qty_out' => $data['quantity'],
        'unit_price' => $data['unit_price'],
        'specification_snapshot' => $data['specification'],
        'created_by' => $data['created_by'],
    ]);
}



public function stockSummaryWithLowFlag(): array
{
    $defaultThreshold = (float) \App\Models\AppSetting::get('default_low_stock_threshold', 0);

    $rows = \App\Models\StockLedger::query()
        ->from('stock_ledger')
        ->selectRaw(' 
            items.id as item_id,
            items.item_code,
            items.name as item_name,
            items.low_stock_threshold,
            groups.group_code,
            groups.group_name,
            COALESCE(SUM(stock_ledger.qty_in),0) as total_in,
            COALESCE(SUM(stock_ledger.qty_out),0) as total_out,
            (COALESCE(SUM(stock_ledger.qty_in),0) - COALESCE(SUM(stock_ledger.qty_out),0)) as balance,

            (SELECT COALESCE(sl2.unit_price,0)
                FROM stock_ledger sl2
                WHERE sl2.item_id = items.id AND sl2.txn_type = "PURCHASE"
                ORDER BY sl2.txn_date DESC, sl2.id DESC
                LIMIT 1
            ) as last_purchase_price,

            (SELECT
                CASE WHEN COALESCE(SUM(sl3.qty_in),0) = 0 THEN 0
                     ELSE COALESCE(SUM(sl3.qty_in * sl3.unit_price),0) / COALESCE(SUM(sl3.qty_in),0)
                END
                FROM stock_ledger sl3
                WHERE sl3.item_id = items.id AND sl3.txn_type = "PURCHASE"
            ) as avg_purchase_price
        ')
        ->join('items', 'items.id', '=', 'stock_ledger.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->groupBy('items.id','items.item_code','items.name','items.low_stock_threshold','groups.group_code','groups.group_name')
        ->orderBy('groups.group_code')
        ->orderBy('items.item_code')
        ->get();

    return $rows->map(function ($r) use ($defaultThreshold) {
        $threshold = $r->low_stock_threshold !== null ? (float)$r->low_stock_threshold : $defaultThreshold;
        $r->threshold_used = $threshold;
        $r->is_low = $threshold > 0 ? ((float)$r->balance <= $threshold) : false;

        $r->last_purchase_price = (float)($r->last_purchase_price ?? 0);
        $r->avg_purchase_price = (float)($r->avg_purchase_price ?? 0);
        $r->value_last = round(((float)$r->balance) * $r->last_purchase_price, 2);
        $r->value_avg = round(((float)$r->balance) * $r->avg_purchase_price, 2);
        return $r;
    })->all();
}



}
