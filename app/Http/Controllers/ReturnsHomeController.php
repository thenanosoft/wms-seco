<?php

namespace App\Http\Controllers;

class ReturnsHomeController extends Controller
{
    public function index()
    {
        return view('returns.home');
    }
}
