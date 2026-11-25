<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'navigation' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => true],
                ['label' => 'Transaksi', 'children' => [
                    ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                    ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
                ]],
                ['label' => 'Manajemen Toko', 'active' => false, 'children' => [
                    ['label' => 'Master', 'href' => route('products.index')],
                    ['label' => 'Pembelian', 'href' => route('purchases.index')],
                    ['label' => 'Persediaan', 'href' => route('inventory.index')],
                ]],
                ['label' => 'Laporan', 'children' => [
                    ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                    ['label' => 'Pembayaran', 'href' => '#ringkasan'],
                ]],
            ],
            'stats' => [
                [
                    'label' => 'Penjualan hari ini',
                    'value' => 'Rp 8.450.000',
                    'change' => '+12,8%',
                    'comparison' => 'dari kemarin',
                    'icon' => 'revenue',
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Transaksi',
                    'value' => '124',
                    'change' => '+8,2%',
                    'comparison' => 'dari kemarin',
                    'icon' => 'transactions',
                    'tone' => 'violet',
                ],
                [
                    'label' => 'Rata-rata transaksi',
                    'value' => 'Rp 68.145',
                    'change' => '+3,4%',
                    'comparison' => 'dari kemarin',
                    'icon' => 'average',
                    'tone' => 'teal',
                ],
                [
                    'label' => 'Stok menipis',
                    'value' => '8 produk',
                    'change' => 'Perlu dicek',
                    'comparison' => 'stok di bawah batas',
                    'icon' => 'stock',
                    'tone' => 'amber',
                ],
            ],
            'transactions' => [
                [
                    'id' => 'TRX-2401',
                    'customer' => 'Andi Pratama',
                    'initials' => 'AP',
                    'items' => '3 produk',
                    'time' => '10.15',
                    'payment' => 'QRIS',
                    'total' => 'Rp 245.000',
                    'status' => 'Selesai',
                ],
                [
                    'id' => 'TRX-2400',
                    'customer' => 'Siti Rahma',
                    'initials' => 'SR',
                    'items' => '2 produk',
                    'time' => '10.02',
                    'payment' => 'Tunai',
                    'total' => 'Rp 128.500',
                    'status' => 'Selesai',
                ],
                [
                    'id' => 'TRX-2399',
                    'customer' => 'Budi Santoso',
                    'initials' => 'BS',
                    'items' => '5 produk',
                    'time' => '09.48',
                    'payment' => 'Transfer',
                    'total' => 'Rp 512.000',
                    'status' => 'Diproses',
                ],
                [
                    'id' => 'TRX-2398',
                    'customer' => 'Maya Putri',
                    'initials' => 'MP',
                    'items' => '1 produk',
                    'time' => '09.31',
                    'payment' => 'QRIS',
                    'total' => 'Rp 75.000',
                    'status' => 'Selesai',
                ],
                [
                    'id' => 'TRX-2397',
                    'customer' => 'Dimas Saputra',
                    'initials' => 'DS',
                    'items' => '4 produk',
                    'time' => '09.17',
                    'payment' => 'Tunai',
                    'total' => 'Rp 386.000',
                    'status' => 'Dibatalkan',
                ],
            ],
            'paymentMethods' => [
                ['label' => 'QRIS', 'amount' => 'Rp 3.802.500', 'percentage' => 45, 'color' => 'bg-blue-600'],
                ['label' => 'Tunai', 'amount' => 'Rp 2.957.500', 'percentage' => 35, 'color' => 'bg-violet-500'],
                ['label' => 'Transfer', 'amount' => 'Rp 1.690.000', 'percentage' => 20, 'color' => 'bg-teal-500'],
            ],
            'topProducts' => [
                ['name' => 'Kopi Arabika 250g', 'category' => 'Minuman', 'sold' => '38 terjual', 'initials' => 'KA', 'tone' => 'bg-amber-100 text-amber-700'],
                ['name' => 'Susu UHT 1L', 'category' => 'Minuman', 'sold' => '31 terjual', 'initials' => 'SU', 'tone' => 'bg-sky-100 text-sky-700'],
                ['name' => 'Roti Gandum', 'category' => 'Makanan', 'sold' => '24 terjual', 'initials' => 'RG', 'tone' => 'bg-orange-100 text-orange-700'],
            ],
            'stockAlerts' => [
                ['name' => 'Kantong Kertas', 'quantity' => '3 tersisa'],
                ['name' => 'Gula Aren 500g', 'quantity' => '5 tersisa'],
            ],
        ]);
    }
}
