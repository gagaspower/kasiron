@extends('layouts.products')

@section('title', 'Pembayaran Pembelian | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">Manajemen Toko / Pembelian</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Pembayaran Pembelian</h1>
        <p class="mt-2 text-sm text-slate-500">Catat pembayaran dan pantau kewajiban kepada supplier.</p>
    </div>
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <p class="text-sm text-slate-600">Layout pembayaran pembelian sudah disiapkan. Fungsi pencatatan pembayaran dan pelunasan akan dibuat pada tahap berikutnya.</p>
    </section>
@endsection
