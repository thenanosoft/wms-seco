<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use Illuminate\Http\Request;

class ReturnsHomeController extends Controller
{
    public function index(Request $request)
    {
        $type = strtoupper((string) $request->query('type', ''));
        $from = (string) $request->query('from', '');
        $to   = (string) $request->query('to', '');

        $q = StockLedger::query()
            ->with(['item', 'creator'])
            ->whereIn('txn_type', ['ISSUE_RETURN_IN', 'PURCHASE_RETURN_OUT'])
            ->orderByDesc('txn_date')
            ->orderByDesc('id');

        if (in_array($type, ['ISSUE_RETURN_IN', 'PURCHASE_RETURN_OUT'], true)) {
            $q->where('txn_type', $type);
        }

        if ($from !== '') {
            $q->whereDate('txn_date', '>=', $from);
        }
        if ($to !== '') {
            $q->whereDate('txn_date', '<=', $to);
        }

        $returns = $q->paginate(15)->appends($request->query());

        return view('returns.home', compact('returns', 'type', 'from', 'to'));
    }
}
