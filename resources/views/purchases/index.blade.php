@extends('layouts.products')

@section('title', 'Pembelian | KasirOn')

@section('product-content')
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700/80">Manajemen toko</p>
        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Pembelian</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Kelola proses pengadaan barang dari supplier, mulai dari pencatatan pembelian hingga pembayaran.</p>
            </div>
        </div>
    </div>

    <section aria-label="Menu pembelian" class="grid gap-4 lg:grid-cols-3">
        <a href="{{ route('purchases.transactions.index') }}" class="group flex min-h-[112px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-900/[0.06] focus:outline-none focus:ring-4 focus:ring-blue-500/10 sm:p-6">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100/80 transition group-hover:bg-blue-100">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 4.75h14A1.75 1.75 0 0 1 20.75 6.5v13A1.75 1.75 0 0 1 19 21.25H5A1.75 1.75 0 0 1 3.25 19.5v-13A1.75 1.75 0 0 1 5 4.75Z" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M8 3v3.5M16 3v3.5M3.5 9h17M8 13h8M8 16.5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1"><span class="block text-base font-semibold text-slate-900">Transaksi Pembelian</span><span class="mt-1 block text-sm leading-5 text-slate-500">Catat barang yang dibeli dari supplier, termasuk rincian produk, jumlah, harga, dan total pembelian.</span></span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition group-hover:translate-x-1 group-hover:bg-blue-50 group-hover:text-blue-700"><svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a href="{{ route('purchases.receipts.index') }}" class="group flex min-h-[112px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/[0.06] focus:outline-none focus:ring-4 focus:ring-emerald-500/10 sm:p-6">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100/80 transition group-hover:bg-emerald-100">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3.5 7.25h11v11h-11zM14.5 10.25h3.25l2.75 3v5h-6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <circle cx="7.5" cy="18.25" r="1.75" stroke="currentColor" stroke-width="1.7"/>
                    <circle cx="17.5" cy="18.25" r="1.75" stroke="currentColor" stroke-width="1.7"/>
                    <path d="m7 11 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1"><span class="block text-base font-semibold text-slate-900">Penerimaan Barang</span><span class="mt-1 block text-sm leading-5 text-slate-500">Catat dan konfirmasi barang yang benar-benar diterima. Stok bertambah setelah penerimaan dikonfirmasi.</span></span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition group-hover:translate-x-1 group-hover:bg-emerald-50 group-hover:text-emerald-700"><svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a href="{{ route('purchases.payments.index') }}" class="group flex min-h-[112px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-900/[0.06] focus:outline-none focus:ring-4 focus:ring-violet-500/10 sm:p-6">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-100/80 transition group-hover:bg-violet-100">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="3.25" y="5.25" width="17.5" height="13.5" rx="2" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M3.75 10h16.5M7 14.25h3.5M7 7.75h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1"><span class="block text-base font-semibold text-slate-900">Pembayaran Pembelian</span><span class="mt-1 block text-sm leading-5 text-slate-500">Catat pembayaran pembelian, baik pembayaran langsung maupun pembayaran bertahap kepada supplier.</span></span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-400 transition group-hover:translate-x-1 group-hover:bg-violet-50 group-hover:text-violet-700"><svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>
    </section>
@endsection
