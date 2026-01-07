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
    $row = StockLedger::query()
        ->selectRaw('
            COALESCE(SUM(qty_in),0) as qty_in_sum,
            COALESCE(SUM(qty_out),0) as qty_out_sum
        ')
        ->where('item_id', $itemId)
        ->first();

    $in = (float) ($row->qty_in_sum ?? 0);
    $out = (float) ($row->qty_out_sum ?? 0);

    return [
        'available' => $in - $out,
        'total_in' => $in,
        'total_out' => $out,
    ];
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




}
