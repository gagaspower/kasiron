@extends('layouts.products')

@section('title', 'Saldo & Kelebihan Pembayaran | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">Manajemen Toko / Pembelian</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Saldo & Kelebihan Pembayaran</h1>
        <p class="mt-2 text-sm text-slate-500">Kelola kelebihan pembayaran yang belum diselesaikan, saldo titipan, dan riwayat mutasi supplier.</p>
    </div>

    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif

    <form method="GET" class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center">
        <select name="supplier_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm sm:max-w-xs">
            <option value="">Semua supplier</option>
            @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id') === $supplier->id)>{{ $supplier->nama }}</option>@endforeach
        </select>
        <button class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Filter</button>
        @if(request('supplier_id'))<a href="{{ route('purchases.balances.index') }}" class="rounded-xl px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50">Reset</a>@endif
    </form>

    <section class="mb-6 rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Kelebihan pembayaran belum diselesaikan</h2><p class="mt-1 text-xs text-slate-500">Pilih untuk menjadikannya titipan atau mencatat pengembalian. Bisa diselesaikan sebagian.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Nomor</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3 text-right">Kelebihan</th><th class="px-3 py-3 text-right">Terselesaikan</th><th class="px-3 py-3 text-right">Sisa</th><th class="px-5 py-3">Tindakan</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overpayments as $item)
                        @php($remaining = max(0, round((float) $item->jumlah_kelebihan - (float) $item->sudah_diselesaikan, 2)))
                        @if($remaining > 0)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $item->nomor_kelebihan }}</p><p class="mt-1 text-xs text-slate-400">{{ $item->pembayaran?->nomor_pembayaran }}</p></td>
                            <td class="px-3 py-4 text-slate-600">{{ $item->tanggal?->format('d M Y') }}</td>
                            <td class="px-3 py-4 text-slate-700">{{ $item->supplier?->nama }}</td>
                            <td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format((float) $item->jumlah_kelebihan, 0, ',', '.') }}</td>
                            <td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format((float) $item->sudah_diselesaikan, 0, ',', '.') }}</td>
                            <td class="px-3 py-4 text-right font-semibold tabular-nums text-amber-700">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <details class="group">
                                    <summary class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50">Selesaikan</summary>
                                    <form method="POST" action="{{ route('purchases.balances.settle', $item) }}" class="mt-3 min-w-64 space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        @csrf
                                        <label class="block text-xs font-medium text-slate-600">Tindakan</label>
                                        <select name="jenis" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><option value="titipan">Jadikan titipan</option><option value="pengembalian">Catat pengembalian</option></select>
                                        <label class="block text-xs font-medium text-slate-600">Nominal (maks. Rp {{ number_format($remaining, 0, ',', '.') }})</label>
                                        <input name="jumlah" type="number" min="0.01" max="{{ $remaining }}" step="0.01" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <label class="block text-xs font-medium text-slate-600">Tanggal</label>
                                        <input name="tanggal" type="date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <input name="referensi" placeholder="Referensi (opsional)" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                        <textarea name="catatan" rows="2" placeholder="Catatan (opsional)" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></textarea>
                                        <button class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan penyelesaian</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada kelebihan pembayaran.</td></tr>
                    @endforelse
                    @if($overpayments->isEmpty())<tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada kelebihan pembayaran.</td></tr>@endif
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $overpayments->onEachSide(1)->links('vendor.pagination.kasiron') }}</div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Saldo titipan supplier</h2><p class="mt-1 text-xs text-slate-500">Saldo dihitung dari mutasi masuk dikurangi pemakaian.</p></div>
            <div class="divide-y divide-slate-100">
                @forelse($balances as $balance)
                    <div class="flex items-center justify-between gap-3 px-5 py-4"><span class="text-sm font-medium text-slate-700">{{ $balance->supplier?->nama }}</span><span class="font-semibold tabular-nums text-emerald-700">Rp {{ number_format((float) $balance->saldo, 0, ',', '.') }}</span></div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada saldo titipan supplier.</p>
                @endforelse
            </div>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Riwayat mutasi titipan</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($mutations as $mutation)
                    <div class="flex items-start justify-between gap-3 px-5 py-3"><div><p class="text-sm font-medium text-slate-700">{{ $mutation->supplier?->nama }}</p><p class="mt-1 text-xs text-slate-400">{{ $mutation->tanggal?->format('d M Y') }} · {{ ucfirst($mutation->jenis) }}</p></div><span class="text-sm font-semibold tabular-nums {{ $mutation->jenis === 'masuk' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $mutation->jenis === 'masuk' ? '+' : '−' }}Rp {{ number_format((float) $mutation->jumlah, 0, ',', '.') }}</span></div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada mutasi titipan.</p>
                @endforelse
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $mutations->onEachSide(1)->links('vendor.pagination.kasiron') }}</div>
        </section>
    </div>
@endsection
