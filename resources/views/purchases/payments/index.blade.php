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
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <form method="GET" class="flex flex-col gap-2 border-b border-slate-100 p-4 sm:flex-row sm:items-center">
            <select name="supplier_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">Semua supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id') === $supplier->id)>{{ $supplier->nama }}</option>@endforeach</select>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            <button class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Filter</button>
            @if(request()->hasAny(['supplier_id','tanggal_mulai','tanggal_selesai']))<a href="{{ route('purchases.payments.index') }}" class="px-3 py-2.5 text-sm text-slate-500">Reset</a>@endif
        </form>
        <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Nomor pembayaran</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3">Metode</th><th class="px-3 py-3 text-right">Nota</th><th class="px-5 py-3 text-right">Total dibayar</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($payments as $payment)<tr><td class="px-5 py-4 font-semibold text-slate-800">{{ $payment->nomor_pembayaran }}</td><td class="px-3 py-4 text-slate-600">{{ $payment->tanggal_pembayaran?->format('d M Y') }}</td><td class="px-3 py-4">{{ $payment->supplier?->nama }}</td><td class="px-3 py-4 capitalize text-slate-600">{{ $payment->metode_pembayaran }}</td><td class="px-3 py-4 text-right tabular-nums">{{ $payment->detail->count() }}</td><td class="px-5 py-4 text-right font-semibold tabular-nums">Rp {{ number_format((float) $payment->total_bayar, 0, ',', '.') }}</td></tr>
            @empty<tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada pembayaran pembelian.</td></tr>@endforelse
        </tbody></table></div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $payments->onEachSide(1)->links('vendor.pagination.kasiron') }}</div>
    </section>
@endsection
