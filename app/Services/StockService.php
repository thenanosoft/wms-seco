<?php

namespace App\Services;

use App\Models\Issue;
use App\Models\IssueLine;
use App\Models\StockLedger;
use App\Models\StockBatch;
use Illuminate\Validation\ValidationException;
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

    public function addIssueReturnInLedgerEntry(array $data): StockLedger
    {
        return StockLedger::create([
            'txn_date' => $data['txn_date'],
            'txn_type' => 'ISSUE_RETURN_IN',
            'ref_table' => 'issue_return_transactions',
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

    public function addPurchaseReturnOutLedgerEntry(array $data): StockLedger
    {
        return StockLedger::create([
            'txn_date' => $data['txn_date'],
            'txn_type' => 'PURCHASE_RETURN_OUT',
            'ref_table' => 'purchase_return_transactions',
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


    /**
     * Returns available stock (qty_in - qty_out). Supports decimal quantities.
     */
    public function getAvailableStock(int $itemId): float
    {
        $row = StockLedger::query()
            ->selectRaw('COALESCE(SUM(qty_in),0) as qty_in_sum, COALESCE(SUM(qty_out),0) as qty_out_sum')
            ->where('item_id', $itemId)
            ->first();

        $in = (float) ($row->qty_in_sum ?? 0);
        $out = (float) ($row->qty_out_sum ?? 0);

        return round($in - $out, 4);
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
        'available' => round($in - $out, 4),
        'total_in' => $in,
        'total_out' => $out,
    ];
}
// Legacy manual return ledger methods removed (manual returns are disabled).



public function stockSummaryWithLowFlag(): array
{
    $defaultThreshold = (float) \App\Models\AppSetting::get('default_low_stock_threshold', 0);

    $rows = \App\Models\StockLedger::query()
        ->selectRaw('
            items.id as item_id,
            items.item_code,
            items.name as item_name,
            items.low_stock_threshold,
            groups.group_code,
            COALESCE(SUM(qty_in),0) as total_in,
            COALESCE(SUM(qty_out),0) as total_out,
            (COALESCE(SUM(qty_in),0) - COALESCE(SUM(qty_out),0)) as balance
        ')
        ->join('items', 'items.id', '=', 'stock_ledger.item_id')
        ->join('groups', 'groups.id', '=', 'items.group_id')
        ->groupBy('items.id','items.item_code','items.name','items.low_stock_threshold','groups.group_code')
        ->orderBy('groups.group_code')
        ->orderBy('items.item_code')
        ->get();

    return $rows->map(function ($r) use ($defaultThreshold) {
        $threshold = $r->low_stock_threshold !== null ? (float)$r->low_stock_threshold : $defaultThreshold;
        $r->threshold_used = $threshold;
        $r->is_low = $threshold > 0 ? ((float)$r->balance <= $threshold) : false;
        return $r;
    })->all();
}




    /**
     * Returns the unit price of the NEXT FIFO batch that will be issued (oldest available batch).
     * If that batch price is still pending (null), pending=true and price=0 for display.
     */
    public function getNextFifoIssueUnitPrice(int $itemId): array
    {
        $batch = StockBatch::query()
            ->where('item_id', $itemId)
            ->where('qty_available', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->first();

        if (!$batch) {
            return ['price' => 0, 'pending' => false];
        }

        $pending = $batch->unit_price === null;
        return ['price' => $pending ? null : (float)$batch->unit_price, 'pending' => $pending];
    }


    /**
     * Strict FIFO issue for a single item.
     *
     * Rules:
     * - Consume oldest available batches first
     * - If a batch price is pending (null), we store issue_price=0 (pending)
     * - Each issued chunk becomes its own IssueLine linked to purchase_line_id (batch)
     *
     * NOTE: Must be called inside a DB transaction.
     */
    public function issueItemFIFO(Issue $issue, int $itemId, float $qty, ?string $specification = null): void
    {
        $qty = round((float) $qty, 4);
        if ($qty <= 0) {
            throw ValidationException::withMessages(['lines' => 'Quantity must be at least 0.0001.']);
        }

        // Lock FIFO batches for this item.
        $batches = StockBatch::query()
            ->where('item_id', $itemId)
            ->where('qty_available', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $remaining = $qty;
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $available = (float) $batch->qty_available;
            if ($available <= 0) continue;

            $take = round(min($remaining, $available), 4);
            if ($take <= 0) continue;

            // Decrease batch available qty
            $batch->qty_available = round($available - $take, 4);
            if ((float)$batch->qty_available < 0) {
                throw ValidationException::withMessages(['lines' => 'Batch stock became negative.']);
            }
            $batch->save();

            $price = $batch->unit_price === null ? null : round((float)$batch->unit_price, 4);
            $lineTotal = $price === null ? 0 : round($take * $price, 4);

            $issueLine = IssueLine::create([
                'issue_id' => $issue->id,
                'purchase_line_id' => $batch->purchase_line_id,
                'item_id' => $itemId,
                'specification' => $specification ?? $batch->specification,
                'issue_price' => $price,
                'quantity' => $take,
                'line_total' => $lineTotal,
            ]);

            $this->addIssueLedgerEntry([
                'txn_date' => $issue->issue_date,
                'ref_id' => $issue->id,
                'ref_line_id' => $issueLine->id,
                'item_id' => $itemId,
                'qty_out' => $take,
                'unit_price' => $price,
                'specification_snapshot' => $issueLine->specification,
                'created_by' => $issue->created_by ?? auth()->id(),
            ]);

            $remaining = round($remaining - $take, 4);
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'lines' => "Not enough stock available for this item. Missing: {$remaining}",
            ]);
        }
    }


    /**
     * Issue additional qty from the SAME purchase_line batch (if available).
     * Returns how much was actually issued from this batch.
     */
    public function issueFromPurchaseLine(Issue $issue, int $purchaseLineId, float $qty, ?string $specification = null): float
    {
        $qty = round((float)$qty, 4);
        if ($qty <= 0) return 0;

        $batch = StockBatch::query()
            ->where('purchase_line_id', $purchaseLineId)
            ->lockForUpdate()
            ->first();

        if (!$batch || (float)$batch->qty_available <= 0) {
            return 0;
        }

        $avail = (float)$batch->qty_available;
        $take = round(min($qty, $avail), 4);
        if ($take <= 0) return 0;

        $batch->qty_available = round($avail - $take, 4);
        $batch->save();

        $price = $batch->unit_price === null ? 0 : round((float)$batch->unit_price, 4);

        IssueLine::create([
            'issue_id' => $issue->id,
            'purchase_line_id' => $batch->purchase_line_id,
            'item_id' => $batch->item_id,
            'specification' => $specification,
            'issue_price' => $price,
            'quantity' => $take,
            'line_total' => round($take * $price, 4),
        ]);

        return $take;
    }

}