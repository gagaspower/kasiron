@extends('layouts.products')

@section('title', 'Manajemen Produk | KasirOn')

@section('product-content')
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700/80">Pengaturan katalog</p>
        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Manajemen Produk</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Kelola etalase, satuan, dan informasi produk toko Anda dari satu tempat.</p>
            </div>
        </div>
    </div>

    <section aria-label="Menu manajemen produk" class="grid gap-4 lg:grid-cols-3">
        <a href="{{ route('categories.index') }}" class="group flex min-h-[112px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-900/[0.06] focus:outline-none focus:ring-4 focus:ring-blue-500/10 sm:p-6">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100/80 transition group-hover:bg-blue-100"><svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.75A1.75 1.75 0 0 1 5.75 4h12.5A1.75 1.75 0 0 1 20 5.75v12.5A1.75 1.75 0 0 1 18.25 20H5.75A1.75 1.75 0 0 1 4 18.25V5.75Z" stroke="currentColor" stroke-width="1.7"/><path d="M4.5 9h15M9 9v10.5M9 13h10.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
            <span class="min-w-0 flex-1"><span class="block text-base font-semibold text-slate-900">Etalase / Kategori</span><span class="mt-1 block text-sm leading-5 text-slate-500">Atur pengelompokan dan tampilan kategori produk.</span></span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition group-hover:translate-x-1 group-hover:bg-blue-50 group-hover:text-blue-700"><svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a href="{{ route('units.index') }}" class="group flex min-h-[112px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-900/[0.06] focus:outline-none focus:ring-4 focus:ring-violet-500/10 sm:p-6">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-100/80 transition group-hover:bg-violet-100"><svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.75 7.5 4.3 7.5-4.3M12 12.25V20.5M8 5.25l8 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <span class="min-w-0 flex-1"><span class="block text-base font-semibold text-slate-900">Satuan Produk</span><span class="mt-1 block text-sm leading-5 text-slate-500">Kelola satuan seperti pcs, kg, liter, dan lainnya.</span></span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition group-hover:translate-x-1 group-hover:bg-violet-50 group-hover:text-violet-700"><svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a href="{{ route('products-master.index') }}" class="group flex min-h-[112px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition duration-200 hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-900/[0.06] focus:outline-none focus:ring-4 focus:ring-teal-500/10 sm:p-6">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 ring-1 ring-inset ring-teal-100/80 transition group-hover:bg-teal-100"><svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4.75h14A1.75 1.75 0 0 1 20.75 6.5v11A1.75 1.75 0 0 1 19 19.25H5A1.75 1.75 0 0 1 3.25 17.5v-11A1.75 1.75 0 0 1 5 4.75Z" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 8.5h9M7.5 12h5M7.5 15.5h7M17 14.5v4m-2-2h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
            <span class="min-w-0 flex-1"><span class="block text-base font-semibold text-slate-900">Produk</span><span class="mt-1 block text-sm leading-5 text-slate-500">Kelola daftar produk, harga, dan informasi stok.</span></span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition group-hover:translate-x-1 group-hover:bg-teal-50 group-hover:text-teal-700"><svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>
    </section>
@endsection
