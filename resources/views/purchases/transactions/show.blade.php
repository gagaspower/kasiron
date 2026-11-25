@extends('layouts.products')

@section('title', $pembelian->nomor_pembelian.' | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">← Daftar Pembelian</a>
        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Detail transaksi pembelian</p><h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $pembelian->nomor_pembelian }}</h1><p class="mt-2 text-sm text-slate-500">Tanggal {{ $pembelian->tanggal_pembelian?->format('d M Y') }}</p></div>
            <span class="inline-flex w-fit rounded-full bg-{{ $pembelian->status === 'draft' ? 'amber' : 'emerald' }}-50 px-3 py-1.5 text-xs font-semibold text-{{ $pembelian->status === 'draft' ? 'amber' : 'emerald' }}-700">{{ str_replace('_', ' ', ucfirst($pembelian->status)) }}</span>
        </div>
    </div>
    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    <div class="grid gap-5 lg:grid-cols-3">
        <section class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Informasi Pembelian</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><p class="text-xs text-slate-400">Supplier</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $pembelian->supplier?->nama ?? '—' }}</p></div>
                    <div><p class="text-xs text-slate-400">Nomor faktur supplier</p><p class="mt-1 text-sm text-slate-800">{{ $pembelian->nomor_faktur_supplier ?: '—' }}</p></div>
                    <div><p class="text-xs text-slate-400">Jatuh tempo</p><p class="mt-1 text-sm text-slate-800">{{ $pembelian->tanggal_jatuh_tempo?->format('d M Y') ?? '—' }}</p></div>
                    <div><p class="text-xs text-slate-400">Dibuat oleh</p><p class="mt-1 text-sm text-slate-800">{{ $pembelian->dibuatOleh?->name ?? '—' }}</p></div>
                </div>
                @if($pembelian->catatan)<p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $pembelian->catatan }}</p>@endif
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Rincian Produk</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[650px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Produk</th><th class="px-3 py-3 text-right">Jumlah</th><th class="px-3 py-3">Satuan</th><th class="px-3 py-3 text-right">Harga</th><th class="px-5 py-3 text-right">Subtotal</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach($pembelian->detail as $detail)<tr><td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $detail->nama_produk }}</p>@if($detail->kode_sku)<p class="mt-1 text-xs text-slate-400">{{ $detail->kode_sku }}</p>@endif</td><td class="px-3 py-4 text-right tabular-nums">{{ number_format((float)$detail->jumlah, 3, ',', '.') }}</td><td class="px-3 py-4 text-slate-600">{{ $detail->nama_satuan }}</td><td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format((float)$detail->harga_beli, 0, ',', '.') }}</td><td class="px-5 py-4 text-right font-semibold tabular-nums">Rp {{ number_format((float)$detail->subtotal, 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div>
            </div>
        </section>
        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Ringkasan</h2>
                <div class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>Rp {{ number_format((float)$pembelian->subtotal,0,',','.') }}</span></div><div class="flex justify-between"><span class="text-slate-500">Diskon</span><span>Rp {{ number_format((float)$pembelian->diskon,0,',','.') }}</span></div><div class="flex justify-between"><span class="text-slate-500">Biaya lain</span><span>Rp {{ number_format((float)$pembelian->biaya_lain,0,',','.') }}</span></div><div class="flex justify-between border-t border-slate-100 pt-3 text-base font-bold"><span>Total</span><span class="text-emerald-700">Rp {{ number_format((float)$pembelian->total,0,',','.') }}</span></div></div>
            </section>
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Tindakan</h2>
                @if($pembelian->status === 'draft')
                    <a href="{{ route('purchases.edit', $pembelian) }}" class="mt-4 flex w-full justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit Draft</a>
                    <form method="POST" action="{{ route('purchases.approve', $pembelian) }}" data-purchase-approve-form class="mt-2">@csrf @method('PATCH')<button class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Setujui Pembelian</button></form>
                    <form method="POST" action="{{ route('purchases.destroy', $pembelian) }}" data-purchase-delete-form class="mt-2">@csrf @method('DELETE')<button class="w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">Hapus Draft</button></form>
                @elseif($pembelian->disetujui_pada)
                    <p class="mt-3 text-sm text-slate-500">Disetujui oleh {{ $pembelian->disetujuiOleh?->name ?? '—' }} pada {{ $pembelian->disetujui_pada->format('d M Y H:i') }}.</p>
                    <p class="mt-3 rounded-xl bg-blue-50 p-3 text-xs leading-5 text-blue-800">Persetujuan tidak menambah stok. Stok bertambah setelah proses penerimaan barang dikonfirmasi.</p>
                @endif
            </section>
        </aside>
    </div>
    <dialog data-purchase-confirm class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40"><div class="p-6"><h2 class="text-lg font-bold text-slate-900">Konfirmasi tindakan</h2><p data-purchase-confirm-message class="mt-2 text-sm leading-6 text-slate-500"></p></div><div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4"><button type="button" data-purchase-confirm-cancel class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button><button type="button" data-purchase-confirm-accept class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">Ya, lanjutkan</button></div></dialog>
@endsection

@section('script')
    @vite('resources/js/pages/purchases.js')
@endsection
