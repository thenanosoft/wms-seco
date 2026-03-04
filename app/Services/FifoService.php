<?php

namespace App\Services;

use App\Models\IssueLine;
use App\Models\IssueReturnLine;
use App\Models\PurchaseLine;
use App\Models\PurchaseReturnLine;
use App\Models\StockBatch;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FifoService
{
    public function createBatchFromPurchaseLine(PurchaseLine $line, string $purchaseDate): StockBatch
    {
        return StockBatch::create([
            'purchase_line_id' => $line->id,
            'purchase_date' => $purchaseDate,
            'item_id' => $line->item_id,
            'specification' => $line->specification,
            'qty_purchased' => round((float)$line->quantity, 4),
            'qty_available' => round((float)$line->quantity, 4),
            'unit_price' => $line->purchase_price,
        ]);
    }

    /**
     * FIFO allocation with row locking.
     */
    public function allocateBatchesForIssue(int $itemId, float $qtyNeeded): array
    {
        if ($qtyNeeded <= 0) return [];

        $batches = StockBatch::query()
            ->where('item_id', $itemId)
            ->where('qty_available', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $out = [];
        $remaining = round((float)$qtyNeeded, 4);
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $avail = (float)$batch->qty_available;
            $take = round(min($remaining, $avail), 4);
            if ($take <= 0) continue;
            $out[] = ['batch' => $batch, 'qty' => $take];
            $remaining = round($remaining - $take, 4);
        }

        if ($remaining > 0) return [];
        return $out;
    }

    /**
     * Quantity protection: cannot set purchased qty lower than already consumed.
     */
    public function updateBatchQuantityFromPurchaseLine(StockBatch $batch, float $newPurchasedQty): void
    {
        $newPurchasedQty = max(0, round((float)$newPurchasedQty, 4));

        $consumed = (float)$batch->qty_purchased - (float)$batch->qty_available;
        if ($newPurchasedQty < $consumed) {
            throw ValidationException::withMessages(["quantity" => "Cannot reduce purchase quantity below already issued/consumed qty ({$consumed})."]);
        }

        $batch->qty_purchased = $newPurchasedQty;
        $batch->qty_available = round($newPurchasedQty - $consumed, 4);
        $batch->save();
    }

    /**
     * When purchase price is confirmed/changed, propagate to all dependent records.
     */
    public function propagateBatchPrice(int $purchaseLineId, ?float $newUnitPrice): void
    {
        $price = $newUnitPrice !== null ? round($newUnitPrice, 4) : null;
        DB::transaction(function () use ($purchaseLineId, $price) {
            // Batch price
            StockBatch::query()
                ->where('purchase_line_id', $purchaseLineId)
                ->update(['unit_price' => $price]);

            // Purchase line
            $pl = PurchaseLine::query()->where('id', $purchaseLineId)->lockForUpdate()->first();
            if ($pl) {
                $pl->purchase_price = $price;
                $pl->line_total = $price === null ? 0 : round((float)$pl->quantity * $price, 4);
                $pl->save();
            }

            // Stock ledger purchase entry
            StockLedger::query()
                ->where('ref_table', 'purchases')
                ->where('ref_line_id', $purchaseLineId)
                ->update(['unit_price' => $price]);

            // Issue lines allocated from this purchase line
            $issueLines = IssueLine::query()
                ->where('purchase_line_id', $purchaseLineId)
                ->lockForUpdate()
                ->get();

            foreach ($issueLines as $il) {
                $il->issue_price = $price;
                $il->line_total = $price === null ? 0 : round((float)$il->quantity * $price, 4);
                $il->save();

                StockLedger::query()
                    ->where('ref_table', 'issues')
                    ->where('ref_line_id', $il->id)
                    ->update(['unit_price' => $price]);
            }

            // Issue return lines created from those issue lines
            $issueReturnLines = IssueReturnLine::query()
                ->whereIn('issue_line_id', $issueLines->pluck('id')->all())
                ->lockForUpdate()
                ->get();
            foreach ($issueReturnLines as $irl) {
                $irl->issue_price = $price;
                $irl->line_total = $price === null ? 0 : round((float)$irl->quantity * $price, 4);
                $irl->save();

                StockLedger::query()
                    // Ledger ref_table used by StockService::addIssueReturnInLedgerEntry()
                    ->where('ref_table', 'issue_return_transactions')
                    ->where('ref_line_id', $irl->id)
                    ->update(['unit_price' => $price]);
            }

            // Purchase return lines from this purchase line
            $purchaseReturnLines = PurchaseReturnLine::query()
                ->where('purchase_line_id', $purchaseLineId)
                ->lockForUpdate()
                ->get();
            foreach ($purchaseReturnLines as $prl) {
                $prl->purchase_price = $price;
                $prl->line_total = $price === null ? 0 : round((float)$prl->quantity * $price, 4);
                $prl->save();

                StockLedger::query()
                    // Ledger ref_table used by StockService::addPurchaseReturnOutLedgerEntry()
                    ->where('ref_table', 'purchase_return_transactions')
                    ->where('ref_line_id', $prl->id)
                    ->update(['unit_price' => $price]);
            }
        });
    }
}
