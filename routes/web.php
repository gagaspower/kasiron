<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\KategoriController;
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

    Route::get('/kategori', [KategoriController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('categories.update');
    Route::patch('/kategori/{kategori}/status', [KategoriController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('categories.destroy');
    Route::patch('/kategori/{id}/restore', [KategoriController::class, 'restore'])->name('categories.restore');
    Route::delete('/kategori/{id}/permanen', [KategoriController::class, 'forceDelete'])->name('categories.force-delete');
    Route::post('/kategori/bulk-action', [KategoriController::class, 'bulkAction'])->name('categories.bulk-action');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
