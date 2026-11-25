<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
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
                ['label' => 'Transaksi', 'children' => [
                    ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                    ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
                ]],
                ['label' => 'Manajemen Produk', 'href' => route('products.index'), 'active' => true],
                ['label' => 'Laporan', 'children' => [
                    ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                    ['label' => 'Pembayaran', 'href' => '#ringkasan'],
                ]],
            ],
        ]);
    })->name('products.index');

    Route::get('/produk', [ProdukController::class, 'index'])->name('products-master.index');
    Route::get('/produk/tambah', [ProdukController::class, 'create'])->name('products-master.create');
    Route::post('/produk', [ProdukController::class, 'store'])->name('products-master.store');
    Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('products-master.edit');
    Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('products-master.update');
    Route::patch('/produk/{produk}/status', [ProdukController::class, 'toggleStatus'])->name('products-master.toggle-status');
    Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('products-master.destroy');
    Route::patch('/produk/{id}/restore', [ProdukController::class, 'restore'])->name('products-master.restore');
    Route::delete('/produk/{id}/permanen', [ProdukController::class, 'forceDelete'])->name('products-master.force-delete');
    Route::post('/produk/bulk-action', [ProdukController::class, 'bulkAction'])->name('products-master.bulk-action');

    Route::get('/kategori/options', [ProdukController::class, 'categoryOptions'])->name('categories.options');
    Route::get('/kategori', [KategoriController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('categories.update');
    Route::patch('/kategori/{kategori}/status', [KategoriController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('categories.destroy');
    Route::patch('/kategori/{id}/restore', [KategoriController::class, 'restore'])->name('categories.restore');
    Route::delete('/kategori/{id}/permanen', [KategoriController::class, 'forceDelete'])->name('categories.force-delete');
    Route::post('/kategori/bulk-action', [KategoriController::class, 'bulkAction'])->name('categories.bulk-action');

    Route::get('/satuan', [SatuanController::class, 'index'])->name('units.index');
    Route::post('/satuan', [SatuanController::class, 'store'])->name('units.store');
    Route::put('/satuan/{satuan}', [SatuanController::class, 'update'])->name('units.update');
    Route::patch('/satuan/{satuan}/status', [SatuanController::class, 'toggleStatus'])->name('units.toggle-status');
    Route::delete('/satuan/{satuan}', [SatuanController::class, 'destroy'])->name('units.destroy');
    Route::patch('/satuan/{id}/restore', [SatuanController::class, 'restore'])->name('units.restore');
    Route::delete('/satuan/{id}/permanen', [SatuanController::class, 'forceDelete'])->name('units.force-delete');
    Route::post('/satuan/bulk-action', [SatuanController::class, 'bulkAction'])->name('units.bulk-action');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('/supplier/{supplier}/status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::patch('/supplier/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    Route::delete('/supplier/{id}/permanen', [SupplierController::class, 'forceDelete'])->name('suppliers.force-delete');
    Route::post('/supplier/bulk-action', [SupplierController::class, 'bulkAction'])->name('suppliers.bulk-action');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
