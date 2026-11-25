@extends('layouts.products')

@section('title', 'Persediaan | KasirOn')

@section('product-content')
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700/80">Manajemen toko</p>
        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Persediaan</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Kelola dan pantau pergerakan stok barang toko Anda dari satu tempat.</p>
            </div>
        </div>
    </div>

    <section aria-label="Menu persediaan" class="grid gap-4 lg:grid-cols-3">
        <div class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19.25V4.75h16v14.5H4Z" stroke="currentColor" stroke-width="1.7"/><path d="M8 15v-3m4 3V8m4 7v-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Ringkasan Stok</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Pantau jumlah stok tersedia dan kondisi persediaan seluruh produk.</p>
            </div>
        </div>

        <div class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5h16v14H4z" stroke="currentColor" stroke-width="1.7"/><path d="M8 9h8m-8 4h5m-5 4h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Barang Keluar</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Catat barang keluar selain dari transaksi penjualan.</p>
            </div>
        </div>

        <div class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3.75v16.5M3.75 12h16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.7"/></svg>
                </span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Penyesuaian Stok</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Sesuaikan jumlah stok berdasarkan hasil stok opname atau koreksi.</p>
            </div>
        </div>

        <div class="group flex min-h-[172px] flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.025] transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100/80">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5h14v14H5z" stroke="currentColor" stroke-width="1.7"/><path d="M8 8h8m-8 4h8m-8 4h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
            </div>
            <div class="mt-5">
                <h2 class="text-base font-semibold text-slate-900">Riwayat Mutasi</h2>
                <p class="mt-1 text-sm leading-5 text-slate-500">Telusuri riwayat perubahan stok dari seluruh aktivitas toko.</p>
            </div>
        </div>
    </section>
@endsection
