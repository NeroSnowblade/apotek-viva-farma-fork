<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Obat;

class CustomerController extends Controller
{
    public function index()
    {
        $obats = Obat::all();
        return view('customer.index', compact('obats'));
    }
}
