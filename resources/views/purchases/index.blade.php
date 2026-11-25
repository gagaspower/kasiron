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
        <a href="{{ route('purchases.transactions.index') }}" class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 4.75h14A1.75 1.75 0 0 1 20.75 6.5v13A1.75 1.75 0 0 1 19 21.25H5A1.75 1.75 0 0 1 3.25 19.5v-13A1.75 1.75 0 0 1 5 4.75Z" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M8 3v3.5M16 3v3.5M3.5 9h17M8 13h8M8 16.5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Tahap berikutnya</span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Transaksi Pembelian</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Catat barang yang dibeli dari supplier, termasuk rincian produk, jumlah, harga, dan total pembelian.</p>
            </div>
            <span class="mt-auto pt-4 text-xs font-semibold text-blue-700">Buka transaksi <span aria-hidden="true">→</span></span>
        </a>

        <a href="{{ route('purchases.receipts.index') }}" class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3.5 7.25h11v11h-11zM14.5 10.25h3.25l2.75 3v5h-6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <circle cx="7.5" cy="18.25" r="1.75" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="17.5" cy="18.25" r="1.75" stroke="currentColor" stroke-width="1.7"/>
                        <path d="m7 11 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Tahap berikutnya</span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Penerimaan Barang</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Catat dan konfirmasi barang yang benar-benar diterima. Stok bertambah setelah penerimaan dikonfirmasi.</p>
            </div>
            <span class="mt-auto pt-4 text-xs font-semibold text-emerald-700">Buka penerimaan <span aria-hidden="true">→</span></span>
        </a>

        <a href="{{ route('purchases.payments.index') }}" class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3.25" y="5.25" width="17.5" height="13.5" rx="2" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M3.75 10h16.5M7 14.25h3.5M7 7.75h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Tahap berikutnya</span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Pembayaran Pembelian</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Catat pembayaran pembelian, baik pembayaran langsung maupun pembayaran bertahap kepada supplier.</p>
            </div>
            <span class="mt-auto pt-4 text-xs font-semibold text-violet-700">Buka pembayaran <span aria-hidden="true">→</span></span>
        </a>
    </section>
@endsection
