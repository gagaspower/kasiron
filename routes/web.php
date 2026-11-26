<?php

use App\\Http\\Controllers\\DashboardController;
use App\\Http\\Controllers\\KategoriController;
use App\\Http\\Controllers\\LoginController;
use App\\Http\\Controllers\\ProdukController;
use App\\Http\\Controllers\\SatuanController;
use App\\Http\\Controllers\\SupplierController;
use App\\Http\\Controllers\\PembelianController;
use App\\Http\\Controllers\\PenerimaanPembelianController;
use App\\Http\\Controllers\\PembayaranPembelianController;
use App\\Http\\Controllers\\SaldoSupplierController;
use Illuminate\\Support\\Facades\\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Main navigation pages
    |--------------------------------------------------------------------------
    */

    Route::get('/manajemen-produk', function () {
        return view('products.index', [
            'navigation' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => false],
                ['label' => 'Transaksi', 'children' => [
                    ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                    ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
                ]],
                ['label' => 'Manajemen Toko', 'active' => true, 'children' => [
                    ['label' => 'Master', 'href' => route('products.index'), 'active' => true],
                    ['label' => 'Pembelian', 'href' => route('purchases.index')],
                    ['label' => 'Persediaan', 'href' => route('inventory.index')],
                ]],
                ['label' => 'Laporan', 'children' => [
                    ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                    ['label' => 'Pembayaran', 'href' => '#ringkasan'],
                ]],
            ],
        ]);
    })->name('products.index');

    Route::get('/persediaan', function () {
        return view('inventory.index', [
            'navigation' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => false],
                ['label' => 'Transaksi', 'children' => [
                    ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                    ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
                ]],
                ['label' => 'Manajemen Toko', 'active' => true, 'children' => [
                    ['label' => 'Master', 'href' => route('products.index')],
                    ['label' => 'Pembelian', 'href' => route('purchases.index')],
                    ['label' => 'Persediaan', 'href' => route('inventory.index'), 'active' => true],
                ]],
                ['label' => 'Laporan', 'children' => [
                    ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                    ['label' => 'Pembayaran', 'href' => '#ringkasan'],
                ]],
            ],
        ]);
    })->name('inventory.index');

    /*
    |--------------------------------------------------------------------------
    | Purchase management
    |--------------------------------------------------------------------------
    */

    Route::prefix('pembelian')->name('purchases.')->group(function () {
        Route::get('/', [PembelianController::class, 'index'])->name('index');
        Route::get('/transaksi', [PembelianController::class, 'transactions'])->name('transactions.index');

        Route::get('/penerimaan', [PenerimaanPembelianController::class, 'index'])
            ->name('receipts.index');
        Route::get('/{pembelian}/penerimaan/tambah', [PenerimaanPembelianController::class, 'create'])
            ->name('receipts.create');
        Route::post('/{pembelian}/penerimaan', [PenerimaanPembelianController::class, 'store'])
            ->name('receipts.store');

        Route::get('/pembayaran', [PembayaranPembelianController::class, 'index'])->name('payments.index');
        Route::get('/pembayaran/tambah', [PembayaranPembelianController::class, 'create'])->name('payments.create');
        Route::post('/pembayaran', [PembayaranPembelianController::class, 'store'])->name('payments.store');

        Route::get('/saldo-kelebihan', [SaldoSupplierController::class, 'index'])->name('balances.index');
        Route::post('/saldo-kelebihan/{kelebihan}/selesaikan', [SaldoSupplierController::class, 'settle'])->name('balances.settle');

        Route::get('/tambah', [PembelianController::class, 'create'])->name('create');
        Route::post('/', [PembelianController::class, 'store'])->name('store');
        Route::get('/{pembelian}/edit', [PembelianController::class, 'edit'])->name('edit');
        Route::put('/{pembelian}', [PembelianController::class, 'update'])->name('update');
        Route::get('/{pembelian}', [PembelianController::class, 'show'])->name('show');
        Route::patch('/{pembelian}/setujui', [PembelianController::class, 'approve'])->name('approve');
        Route::delete('/{pembelian}', [PembelianController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Goods receipt actions
    |--------------------------------------------------------------------------
    */

    Route::prefix('penerimaan')->name('purchases.receipts.')->group(function () {
        Route::get('/{penerimaan}', [PenerimaanPembelianController::class, 'show'])->name('show');
        Route::get('/{penerimaan}/revisi', [PenerimaanPembelianController::class, 'edit'])->name('edit');
        Route::put('/{penerimaan}', [PenerimaanPembelianController::class, 'update'])->name('update');
        Route::patch('/{penerimaan}/batalkan', [PenerimaanPembelianController::class, 'cancel'])->name('cancel');
        Route::patch('/{penerimaan}/konfirmasi', [PenerimaanPembelianController::class, 'confirm'])->name('confirm');
    });

    /*
    |--------------------------------------------------------------------------
    | Supplier and product lookup options
    |--------------------------------------------------------------------------
    */

    Route::get('/supplier/options', [SupplierController::class, 'options'])->name('suppliers.options');
    Route::get('/produk/options', [PembelianController::class, 'productSearch'])->name('purchases.products.options');
    Route::get('/kategori/options', [ProdukController::class, 'categoryOptions'])->name('categories.options');

    /*
    |--------------------------------------------------------------------------
    | Product master
    |--------------------------------------------------------------------------
    */

    Route::prefix('produk')->name('products-master.')->group(function () {
        Route::get('/', [ProdukController::class, 'index'])->name('index');
        Route::get('/tambah', [ProdukController::class, 'create'])->name('create');
        Route::post('/', [ProdukController::class, 'store'])->name('store');
        Route::post('/bulk-action', [ProdukController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/{produk}/edit', [ProdukController::class, 'edit'])->name('edit');
        Route::put('/{produk}', [ProdukController::class, 'update'])->name('update');
        Route::patch('/{produk}/status', [ProdukController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/restore', [ProdukController::class, 'restore'])->name('restore');
        Route::delete('/{id}/permanen', [ProdukController::class, 'forceDelete'])->name('force-delete');
        Route::delete('/{produk}', [ProdukController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Category master
    |--------------------------------------------------------------------------
    */

    Route::prefix('kategori')->name('categories.')->group(function () {
        Route::get('/', [KategoriController::class, 'index'])->name('index');
        Route::post('/', [KategoriController::class, 'store'])->name('store');
        Route::post('/bulk-action', [KategoriController::class, 'bulkAction'])->name('bulk-action');
        Route::put('/{kategori}', [KategoriController::class, 'update'])->name('update');
        Route::patch('/{kategori}/status', [KategoriController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/restore', [KategoriController::class, 'restore'])->name('restore');
        Route::delete('/{id}/permanen', [KategoriController::class, 'forceDelete'])->name('force-delete');
        Route::delete('/{kategori}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Unit master
    |--------------------------------------------------------------------------
    */

    Route::prefix('satuan')->name('units.')->group(function () {
        Route::get('/', [SatuanController::class, 'index'])->name('index');
        Route::post('/', [SatuanController::class, 'store'])->name('store');
        Route::put('/{satuan}', [SatuanController::class, 'update'])->name('update');
        Route::patch('/{satuan}/status', [SatuanController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/restore', [SatuanController::class, 'restore'])->name('restore');
        Route::delete('/{id}/permanen', [SatuanController::class, 'forceDelete'])->name('force-delete');
        Route::delete('/{satuan}', [SatuanController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [SatuanController::class, 'bulkAction'])->name('bulk-action');
    });

    /*
    |--------------------------------------------------------------------------
    | Supplier master
    |--------------------------------------------------------------------------
    */

    Route::prefix('supplier')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::patch('/{supplier}/status', [SupplierController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/restore', [SupplierController::class, 'restore'])->name('restore');
        Route::delete('/{id}/permanen', [SupplierController::class, 'forceDelete'])->name('force-delete');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [SupplierController::class, 'bulkAction'])->name('bulk-action');
    });

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
