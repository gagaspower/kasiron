<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/manajemen-produk', function () {
        return view('products.index', [
            'navigation' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => false],
                [
                    'label' => 'Transaksi',
                    'children' => [
                        ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                        ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
                    ],
                ],
                ['label' => 'Manajemen Produk', 'href' => route('products.index'), 'active' => true],
                [
                    'label' => 'Laporan',
                    'children' => [
                        ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                        ['label' => 'Pembayaran', 'href' => '#ringkasan'],
                    ],
                ],
            ],
        ]);
    })->name('products.index');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
