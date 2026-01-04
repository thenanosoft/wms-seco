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
}
