<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\StockBatch;
use App\Models\Issue;
use App\Models\IssueLine;
use App\Services\FifoService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImportController extends Controller
{
    public function downloadSample(string $type)
    {
        $type = strtolower($type);

        $samples = [
            'groups' => "group_code,group_name\nG001,General\nG002,Electrical\n",
            'items' => "group_code,item_code,name,default_spec,low_stock_threshold\nG001,ITM001,Sugar,1kg,10\nG002,ITM010,Wire,Coil,5\n",
            // Purchase import: group by reference_no
            'purchases' => "purchase_date,supplier_name,reference_no,item_code,specification,quantity,purchase_price\n2026-02-07,ABC Supplier,PO-1001,ITM001,1kg,20,150\n2026-02-07,ABC Supplier,PO-1001,ITM010,Coil,5,0,\n",
            // Issue import: system will allocate FIFO automatically
            'issues' => "issue_date,issued_to,reference_no,item_code,specification,quantity\n2026-02-07,Counter,ISS-2001,ITM001,1kg,3\n",
        ];

        if (!isset($samples[$type])) {
            abort(404);
        }

        $filename = "sample_{$type}.csv";
        return response($samples[$type], 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request, string $type, StockService $stock, FifoService $fifo)
    {
        $type = strtolower($type);

        $request->validate([
            'file' => ['required','file','mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        if (!$path) {
            throw ValidationException::withMessages(['file' => 'Invalid upload.']);
        }

        $rows = $this->readCsv($path);
        if (count($rows) < 1) {
            return back()->with('status', 'No rows found in CSV.');
        }

        return DB::transaction(function () use ($type, $rows, $request, $stock, $fifo) {
            $userId = $request->user()->id;

            if ($type === 'groups') {
                $count = 0;
                foreach ($rows as $r) {
                    $code = trim((string)($r['group_code'] ?? ''));
                    if ($code === '') continue;
                    Group::updateOrCreate(
                        ['group_code' => $code],
                        ['group_name' => trim((string)($r['group_name'] ?? ''))]
                    );
                    $count++;
                }
                return back()->with('status', "Imported {$count} group rows.");
            }

            if ($type === 'items') {
                $count = 0;
                foreach ($rows as $r) {
                    $groupCode = trim((string)($r['group_code'] ?? ''));
                    $itemCode = trim((string)($r['item_code'] ?? ''));
                    if ($groupCode === '' || $itemCode === '') continue;

                    $group = Group::where('group_code', $groupCode)->first();
                    if (!$group) {
                        throw ValidationException::withMessages(['file' => "Unknown group_code: {$groupCode}"]);
                    }

                    Item::updateOrCreate(
                        ['item_code' => $itemCode],
                        [
                            'group_id' => $group->id,
                            'name' => trim((string)($r['name'] ?? '')),
                            'default_spec' => trim((string)($r['default_spec'] ?? '')) ?: null,
                            'low_stock_threshold' => ($r['low_stock_threshold'] ?? null) !== null && $r['low_stock_threshold'] !== '' ? (int)$r['low_stock_threshold'] : null,
                        ]
                    );
                    $count++;
                }
                return back()->with('status', "Imported {$count} item rows.");
            }

            if ($type === 'purchases') {
                // group by reference_no + purchase_date + supplier_name
                $grouped = [];
                foreach ($rows as $r) {
                    $key = trim((string)($r['reference_no'] ?? '')) . '|' . trim((string)($r['purchase_date'] ?? '')) . '|' . trim((string)($r['supplier_name'] ?? ''));
                    $grouped[$key][] = $r;
                }

                $createdPurchases = 0;

                foreach ($grouped as $key => $lines) {
                    $first = $lines[0];
                    $purchaseDate = (string)($first['purchase_date'] ?? '');
                    if ($purchaseDate === '') {
                        throw ValidationException::withMessages(['file' => 'purchase_date is required.']);
                    }

                    $purchase = Purchase::create([
                        'purchase_date' => $purchaseDate,
                        'supplier_name' => trim((string)($first['supplier_name'] ?? '')) ?: null,
                        'reference_no' => trim((string)($first['reference_no'] ?? '')) ?: null,
                        'created_by' => $userId,
                    ]);

                    foreach ($lines as $ln) {
                        $itemCode = trim((string)($ln['item_code'] ?? ''));
                        if ($itemCode === '') continue;
                        $item = Item::where('item_code', $itemCode)->first();
                        if (!$item) {
                            throw ValidationException::withMessages(['file' => "Unknown item_code: {$itemCode}"]);
                        }

                        $qty = (int)($ln['quantity'] ?? 0);
                        if ($qty <= 0) {
                            throw ValidationException::withMessages(['file' => "Invalid quantity for item {$itemCode}"]);
                        }

                        $priceRaw = $ln['purchase_price'] ?? null;
                        $price = null;
                        if ($priceRaw !== null && $priceRaw !== '') {
                            $price = (int)$priceRaw;
                        }

                        $lineTotal = $price !== null ? ($qty * $price) : 0;

                        $purchaseLine = PurchaseLine::create([
                            'purchase_id' => $purchase->id,
                            'item_id' => $item->id,
                            'specification' => trim((string)($ln['specification'] ?? '')) ?: null,
                            'purchase_price' => $price,
                            'quantity' => $qty,
                            'line_total' => $lineTotal,
                        ]);

                        // Batch
                        StockBatch::create([
                            'purchase_line_id' => $purchaseLine->id,
                            'purchase_date' => $purchase->purchase_date,
                            'item_id' => $item->id,
                            'specification' => $purchaseLine->specification,
                            'qty_purchased' => $qty,
                            'qty_available' => $qty,
                            'unit_price' => $price,
                        ]);

                        // Ledger
                        $stock->addPurchaseLedgerEntry([
                            'txn_date' => $purchase->purchase_date,
                            'ref_id' => $purchase->id,
                            'ref_line_id' => $purchaseLine->id,
                            'item_id' => $item->id,
                            'qty_in' => $qty,
                            'unit_price' => $price ?? 0,
                            'specification_snapshot' => $purchaseLine->specification,
                            'created_by' => $userId,
                        ]);
                    }

                    $createdPurchases++;
                }

                return back()->with('status', "Imported {$createdPurchases} purchases.");
            }

            if ($type === 'issues') {
                $createdIssues = 0;

                foreach ($rows as $r) {
                    $issueDate = (string)($r['issue_date'] ?? '');
                    $itemCode = trim((string)($r['item_code'] ?? ''));
                    $qty = (int)($r['quantity'] ?? 0);

                    if ($issueDate === '' || $itemCode === '' || $qty <= 0) {
                        continue;
                    }

                    $item = Item::where('item_code', $itemCode)->first();
                    if (!$item) {
                        throw ValidationException::withMessages(['file' => "Unknown item_code: {$itemCode}"]);
                    }

                    // Create single-issue per row if reference differs; otherwise we can group by reference
                    $ref = trim((string)($r['reference_no'] ?? '')) ?: ('ISS-' . Str::upper(Str::random(6)));

                    $issue = Issue::create([
                        'issue_date' => $issueDate,
                        'issued_to' => trim((string)($r['issued_to'] ?? '')) ?: null,
                        'reference_no' => $ref,
                        'notes' => null,
                        'created_by' => $userId,
                    ]);

                    // Validate available stock
                    $available = (int)$stock->getAvailableStock($item->id);
                    if ($qty > $available) {
                        throw ValidationException::withMessages(['file' => "Not enough stock for {$itemCode}. Available {$available}"]);
                    }

                    $allocations = $fifo->allocateBatchesForIssue($item->id, $qty);
                    if (empty($allocations)) {
                        throw ValidationException::withMessages(['file' => "Not enough FIFO batches for {$itemCode}"]);
                    }

                    foreach ($allocations as $a) {
                        $batch = $a['batch'];
                        $take = (int)$a['qty'];

                        $batch->qty_available = (int)$batch->qty_available - $take;
                        if ($batch->qty_available < 0) {
                            throw ValidationException::withMessages(['file' => 'Batch stock became negative.']);
                        }
                        $batch->save();

                        $price = $batch->unit_price !== null ? (int)$batch->unit_price : 0;

                        $line = IssueLine::create([
                            'issue_id' => $issue->id,
                            'purchase_line_id' => $batch->purchase_line_id,
                            'item_id' => $item->id,
                            'specification' => trim((string)($r['specification'] ?? '')) ?: $batch->specification,
                            'issue_price' => $price,
                            'quantity' => $take,
                            'line_total' => $take * $price,
                        ]);

                        $stock->addIssueLedgerEntry([
                            'txn_date' => $issue->issue_date,
                            'ref_id' => $issue->id,
                            'ref_line_id' => $line->id,
                            'item_id' => $item->id,
                            'qty_out' => $take,
                            'unit_price' => $price,
                            'specification_snapshot' => $line->specification,
                            'created_by' => $userId,
                        ]);
                    }

                    $createdIssues++;
                }

                return back()->with('status', "Imported {$createdIssues} issues.");
            }

            throw ValidationException::withMessages(['file' => 'Unknown import type. Use: groups, items, purchases, issues']);
        });
    }

    private function readCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        if (!$fh) return [];

        $header = null;
        $rows = [];

        while (($data = fgetcsv($fh)) !== false) {
            if ($header === null) {
                $header = array_map(function ($h) {
                    return strtolower(trim((string)$h));
                }, $data);
                continue;
            }

            if (count($data) === 1 && trim((string)$data[0]) === '') {
                continue;
            }

            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $data[$i] ?? null;
            }
            $rows[] = $row;
        }

        fclose($fh);
        return $rows;
    }
}
