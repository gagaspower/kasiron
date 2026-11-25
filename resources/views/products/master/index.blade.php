@extends('layouts.products')

@section('title', 'Daftar Produk | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-700"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m14 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Manajemen Produk</a>
        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-700/80">Pengaturan katalog</p><h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Daftar Produk</h1><p class="mt-2 text-sm text-slate-500">Kelola informasi, harga jual, satuan, dan stok produk.</p></div>
            @unless($trash)<a href="{{ route('products-master.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-500/15"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>Tambah Produk</a>@endunless
        </div>
    </div>
    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>@endif
    <div data-product-results>@include('products.master.partials.results')</div>
    <dialog data-product-confirm-modal class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
        <div class="p-6">
            <div class="mb-4 flex size-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M10.3 3.9 2.9 17a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900">Konfirmasi tindakan</h2>
            <p data-product-confirm-message class="mt-2 text-sm leading-6 text-slate-500"></p>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
            <button type="button" data-product-confirm-cancel class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
            <button type="button" data-product-confirm-accept class="rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">Ya, lanjutkan</button>
        </div>
    </dialog>
@endsection

@section('script')
    @vite('resources/js/pages/products.js')
@endsection
