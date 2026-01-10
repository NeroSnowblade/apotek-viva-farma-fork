<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;

use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    // Jika user belum login, arahkan ke halaman login
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Jika sudah login, arahkan ke dashboard sesuai level
    $level = auth()->user()->level ?? '';
    if ($level === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($level === 'apoteker') {
        return redirect()->route('apoteker.dashboard');
    }
    if ($level === 'customer') {
        return redirect()->route('customer.index');
    }
    // default untuk kasir atau lainnya
    return redirect()->route('kasir.dashboard');
});

// Rute yang bisa diakses SEMUA user yang sudah login
Route::middleware(['auth'])->group(function () {
    Route::get('/invoice/{transaksi}', [TransaksiController::class, 'showInvoice'])->name('transaksi.invoice');
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/obat', [ObatController::class, 'index'])->name('obat.index');
});

// Rute khusus ADMIN (Manajemen User)
Route::middleware(['auth', 'check.level:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    
    // 2. Kita akan letakkan CRUD User di sini
    Route::resource('/admin/users', UserController::class);
});

// Rute khusus APOTEKER (Manajemen Obat) - allow admin as well for management
Route::middleware(['auth', 'check.level:apoteker,admin'])->group(function () {
    // Apoteker bisa ikut pakai dashboard admin
    Route::get('/apoteker/dashboard', [DashboardController::class, 'apoteker'])->name('apoteker.dashboard');

    // 3. Hanya Apoteker yang bisa CUD (Create, Update, Delete) Obat
    Route::get('/obat/create', [ObatController::class, 'create'])->name('obat.create');
    Route::post('/obat', [ObatController::class, 'store'])->name('obat.store');
    Route::get('/obat/{obat}/edit', [ObatController::class, 'edit'])->name('obat.edit');
    Route::put('/obat/{obat}', [ObatController::class, 'update'])->name('obat.update');
    Route::delete('/obat/{obat}', [ObatController::class, 'destroy'])->name('obat.destroy');
});

// Grup Rute untuk Kasir (allow admin to create transactions)
Route::middleware(['auth', 'check.level:kasir,admin'])->group(function () {
    Route::get('/kasir/dashboard', [DashboardController::class, 'kasir'])->name('kasir.dashboard');

    Route::get('/transaksi/baru', [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
    // Temporary URL-accessible delete route (protected by auth and role)
    Route::get('/transaksi/{transaksi}/delete', [TransaksiController::class, 'destroy'])->name('transaksi.delete');
});

// Routes for customers to create transactions (pesan obat)
Route::middleware(['auth', 'check.level:customer'])->group(function () {
    Route::get('/customer/transaksi/baru', [TransaksiController::class, 'create'])->name('transaksi.create.customer');
    Route::post('/customer/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store.customer');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Customer public routes: register and browsing
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;

Route::get('/customer/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register.form');
Route::post('/customer/register', [CustomerAuthController::class, 'register'])->name('customer.register');

Route::get('/customer', [TransaksiController::class, 'create'])->name('customer.index');
