@extends('layouts.app')

@section('title', 'Dashboard | KasirOn')

@section('content')
    <main id="dashboard" class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700/80">Ringkasan operasional</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Dashboard</h1>
                <p class="mt-2 text-sm text-slate-500">Pantau penjualan dan aktivitas toko dalam satu tampilan.</p>
            </div>
            <a href="#transaksi" class="inline-flex min-h-11 items-center justify-center gap-2 self-start rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-slate-900/10 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 sm:self-auto">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Lihat transaksi
            </a>
        </div>

        @include('partials.dashboard.summary-cards', ['stats' => $stats])

        <div class="mt-6 grid grid-cols-1 items-start gap-6 xl:grid-cols-12">
            <div class="min-w-0 xl:col-span-8">
                @include('partials.dashboard.recent-transactions', ['transactions' => $transactions])
            </div>
            <div class="grid gap-6 sm:grid-cols-2 xl:col-span-4 xl:grid-cols-1">
                @include('partials.dashboard.sales-overview', ['paymentMethods' => $paymentMethods])
                @include('partials.dashboard.products-overview', ['topProducts' => $topProducts, 'stockAlerts' => $stockAlerts])
            </div>
        </div>

        <footer class="mt-8 border-t border-slate-200/80 py-5 text-center text-xs text-slate-400">
            KasirOn · Ringkasan ini menampilkan data contoh dan siap dihubungkan ke data toko Anda.
        </footer>
    </main>
@endsection
