<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Later: show cards using real data (today purchases/issues/returns)
        return view('dashboard.index', [
            'user' => $user,
        ]);
    }
}
