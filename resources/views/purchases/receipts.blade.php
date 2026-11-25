@extends('layouts.products')

@section('title', 'Penerimaan Barang | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">Manajemen Toko / Pembelian</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Penerimaan Barang</h1>
        <p class="mt-2 text-sm text-slate-500">Catat barang yang datang berdasarkan transaksi pembelian yang telah disetujui.</p>
    </div>
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <p class="text-sm text-slate-600">Layout penerimaan barang sudah disiapkan. Fungsi pencatatan penerimaan dan penambahan stok akan dibuat pada tahap berikutnya.</p>
    </section>
@endsection
