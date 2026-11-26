@extends('layouts.products')

@section('title', 'Pembayaran Pembelian | KasirOn')

@section('product-content')
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">Manajemen Toko / Pembelian</a>
            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Pembayaran Pembelian</h1>
            <p class="mt-2 text-sm text-slate-500">Catat pembayaran untuk satu supplier dan alokasikan ke beberapa nota sekaligus.</p>
        </div>
        <a href="{{ route('purchases.payments.create') }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-700">Catat Pembayaran</a>
    </div>
    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @include('purchases.payments.results')
@endsection

@section('script')
    @vite(['resources/js/pages/purchases.js'])
@endsection
