@extends('layouts.products')

@section('title', $payment->nomor_pembayaran.' | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.payments.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">← Pembayaran Pembelian</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Detail pembayaran</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $payment->nomor_pembayaran }}</h1>
    </div>
    <div class="grid gap-5 lg:grid-cols-3">
        <section class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Informasi pembayaran</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><p class="text-xs text-slate-400">Supplier</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $payment->supplier?->nama }}</p></div>
                    <div><p class="text-xs text-slate-400">Tanggal</p><p class="mt-1 text-sm text-slate-800">{{ $payment->tanggal_pembayaran?->format('d M Y') }}</p></div>
                    <div><p class="text-xs text-slate-400">Metode</p><p class="mt-1 text-sm capitalize text-slate-800">{{ $payment->metode_pembayaran }}</p></div>
                    <div><p class="text-xs text-slate-400">Referensi</p><p class="mt-1 text-sm text-slate-800">{{ $payment->referensi_pembayaran ?: '—' }}</p></div>
                </div>
                @if($payment->catatan)<p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $payment->catatan }}</p>@endif
            </div>
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Alokasi ke nota pembelian</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[600px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Nomor nota</th><th class="px-3 py-3">Tanggal pembelian</th><th class="px-5 py-3 text-right">Nominal dialokasikan</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @foreach($payment->detail as $detail)<tr><td class="px-5 py-4"><a href="{{ route('purchases.show', $detail->pembelian) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $detail->pembelian?->nomor_pembelian }}</a></td><td class="px-3 py-4 text-slate-600">{{ $detail->pembelian?->tanggal_pembelian?->format('d M Y') }}</td><td class="px-5 py-4 text-right font-semibold tabular-nums">Rp {{ number_format((float) $detail->jumlah_bayar, 0, ',', '.') }}</td></tr>@endforeach
                </tbody></table></div>
            </section>
        </section>
        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Ringkasan</h2>
                <div class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><span class="text-slate-500">Total uang dibayarkan</span><strong>Rp {{ number_format((float) $payment->total_bayar, 0, ',', '.') }}</strong></div><div class="flex justify-between"><span class="text-slate-500">Alokasi nota</span><strong>Rp {{ number_format((float) $payment->detail->sum('jumlah_bayar'), 0, ',', '.') }}</strong></div>
                    @if($payment->kelebihan)<div class="border-t border-slate-100 pt-3"><p class="text-xs text-slate-500">Kelebihan pembayaran</p><p class="mt-1 text-lg font-bold text-amber-700">Rp {{ number_format((float) $payment->kelebihan->jumlah_kelebihan, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">Sudah diselesaikan: Rp {{ number_format((float) $payment->kelebihan->penyelesaian->sum('jumlah'), 0, ',', '.') }}</p></div>@endif
                </div>
            </section>
        </aside>
    </div>
@endsection
