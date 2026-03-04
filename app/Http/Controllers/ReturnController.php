<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReturnRequest;
use App\Models\ReturnTransaction;
use App\Models\ReturnLine;
use App\Models\Group;
use App\Models\Item;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $q = ReturnTransaction::with('creator')
            ->orderByDesc('return_date')
            ->orderByDesc('id');

        // Filter by return type (IN/OUT)
        $type = strtoupper((string)$request->query('type', ''));
        if (in_array($type, ['IN', 'OUT'], true)) {
            $q->where('type', $type);
        }

        $returns = $q->paginate(15)->appends($request->query());

        return view('returns.index', compact('returns', 'type'));
    }

    public function create()
    {
        $groups = Group::orderBy('group_code')->get();
        $items = Item::orderBy('item_code')->get();

        return view('returns.create', compact('groups','items'));
    }

    public function store(StoreReturnRequest $request, StockService $stock)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $stock, $request) {
            // Stock validation for OUT returns (audit-safe)
if (($data['type'] ?? null) === 'OUT') {
    foreach ($data['lines'] as $i => $line) {
        $itemId = (int) $line['item_id'];
        $qty = (float) $line['quantity'];

        $available = $stock->getAvailableStock($itemId);

        if ($qty > $available) {
            throw ValidationException::withMessages([
                "lines.$i.quantity" => "Not enough stock to return outward. Available: {$available}",
            ]);
        }
    }
}

            
            $ret = ReturnTransaction::create([
                'return_date' => $data['return_date'],
                'type' => $data['type'],
                'party' => $data['party'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['lines'] as $line) {

                $qty = round((float)$line['quantity'], 4);
                $price = isset($line['unit_price']) ? round((float)$line['unit_price'], 4) : 0;
                $total = round($qty * $price, 4);

                $rl = ReturnLine::create([
                    'return_transaction_id' => $ret->id,
                    'item_id' => $line['item_id'],
                    'specification' => $line['specification'] ?? null,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'line_total' => $total,
                ]);

                if ($ret->type === 'IN') {
                    $stock->addReturnInLedger([
                        'txn_date' => $ret->return_date,
                        'ref_id' => $ret->id,
                        'ref_line_id' => $rl->id,
                        'item_id' => $rl->item_id,
                        'quantity' => $rl->quantity,
                        'unit_price' => $rl->unit_price,
                        'specification' => $rl->specification,
                        'created_by' => $request->user()->id,
                    ]);
                } else {
                    $stock->addReturnOutLedger([
                        'txn_date' => $ret->return_date,
                        'ref_id' => $ret->id,
                        'ref_line_id' => $rl->id,
                        'item_id' => $rl->item_id,
                        'quantity' => $rl->quantity,
                        'unit_price' => $rl->unit_price,
                        'specification' => $rl->specification,
                        'created_by' => $request->user()->id,
                    ]);
                }
            }
        });

        return redirect()->route('returns.index')->with('status','Return saved successfully');
    }
}
