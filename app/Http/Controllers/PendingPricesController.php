<?php

namespace App\Http\Controllers;

use App\Models\PurchaseLine;
use Illuminate\Http\Request;

class PendingPricesController extends Controller
{
    public function index(Request $request)
    {
        $q = PurchaseLine::query()
            ->with(['purchase.creator','item.group'])
            ->whereNull('purchase_price')
            ->orderByDesc('id');

        if ($request->filled('supplier')) {
            $q->whereHas('purchase', function ($sub) use ($request) {
                $sub->where('supplier_name','like','%' . $request->string('supplier')->toString() . '%');
            });
        }

        $lines = $q->paginate(20)->withQueryString();

        return view('reports.pending-prices', compact('lines'));
    }
}
