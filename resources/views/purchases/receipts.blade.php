@extends('layouts.products')

@section('title', 'Penerimaan Barang | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">Manajemen Toko / Pembelian</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Penerimaan Barang</h1>
        <p class="mt-2 text-sm text-slate-500">Catat barang yang datang berdasarkan transaksi pembelian yang telah disetujui.</p>
    </div>
    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>@endif
    <section class="mb-7 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="font-semibold text-slate-900">Pembelian menunggu penerimaan</h2><p class="mt-1 text-xs text-slate-500">Transaksi yang sudah disetujui dan masih memiliki barang tersisa.</p></div>
            <form method="GET" action="{{ route('purchases.receipts.index') }}" class="flex gap-2"><input type="search" name="search" value="{{ $search }}" placeholder="Cari nomor / supplier..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 sm:w-64"><button class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cari</button></form>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3.5">Nomor pembelian</th><th class="px-3 py-3.5">Tanggal</th><th class="px-3 py-3.5">Supplier</th><th class="px-3 py-3.5 text-right">Item tersisa</th><th class="px-3 py-3.5 text-right">Qty tersisa</th><th class="px-5 py-3.5 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pembelians as $item)<tr class="hover:bg-slate-50/70"><td class="px-5 py-4 font-semibold text-slate-800">{{ $item->nomor_pembelian }}<p class="mt-1 text-xs font-normal text-slate-400">{{ str_replace('_', ' ', ucfirst($item->status)) }}</p></td><td class="px-3 py-4 text-slate-600">{{ $item->tanggal_pembelian?->format('d M Y') }}</td><td class="px-3 py-4 text-slate-700">{{ $item->supplier?->nama ?? '—' }}</td><td class="px-3 py-4 text-right tabular-nums">{{ $item->total_sisa_item }}</td><td class="px-3 py-4 text-right tabular-nums">{{ rtrim(rtrim(number_format($item->total_sisa_qty, 6, ',', '.'), '0'), ',') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('purchases.receipts.create', $item) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Terima Barang</a></td></tr>
                @empty<tr><td colspan="6" class="px-5 py-12 text-center"><p class="font-semibold text-slate-700">Tidak ada pembelian yang menunggu penerimaan</p><p class="mt-1 text-sm text-slate-500">Transaksi pembelian yang disetujui akan muncul di sini.</p></td></tr>@endforelse
            </tbody>
        </table></div>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Riwayat penerimaan</h2><p class="mt-1 text-xs text-slate-500">Daftar penerimaan barang, termasuk draft dan penerimaan yang sudah dikonfirmasi.</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3.5">Nomor penerimaan</th><th class="px-3 py-3.5">Tanggal</th><th class="px-3 py-3.5">Nomor pembelian</th><th class="px-3 py-3.5">Supplier</th><th class="px-3 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayat as $receipt)<tr class="hover:bg-slate-50/70"><td class="px-5 py-4 font-semibold text-slate-800">{{ $receipt->nomor_penerimaan }}</td><td class="px-3 py-4 text-slate-600">{{ $receipt->tanggal_penerimaan?->format('d M Y') }}</td><td class="px-3 py-4 text-slate-700">{{ $receipt->pembelian?->nomor_pembelian ?? '—' }}</td><td class="px-3 py-4 text-slate-700">{{ $receipt->pembelian?->supplier?->nama ?? '—' }}</td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $receipt->status === 'diterima' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($receipt->status) }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('purchases.receipts.show', $receipt) }}" class="rounded-lg p-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Detail</a></td></tr>
                @empty<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada riwayat penerimaan barang.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $riwayat->onEachSide(1)->links('vendor.pagination.kasiron') }}</div>
    </section>
@endsection
