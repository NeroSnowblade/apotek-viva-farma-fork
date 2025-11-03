<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View; // Jangan lupa import View

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Admin dan Apoteker.
     */
    public function admin(): View
    {
        return view('dashboards.admin');
    }

    /**
     * Menampilkan dashboard khusus untuk Apoteker.
     */
    public function apoteker(): View
    {
        return view('dashboards.apoteker');
    }

    /**
     * Menampilkan dashboard untuk Kasir.
     */
    public function kasir(): View
    {
        return view('dashboards.kasir');
    }
}