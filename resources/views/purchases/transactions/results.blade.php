<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <form method="GET" action="{{ route('purchases.transactions.index') }}" data-purchase-search-form class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 class="font-semibold text-slate-900">Daftar pembelian</h2><p class="mt-1 text-xs text-slate-500">{{ $pembelians->total() }} transaksi ditemukan</p></div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="search" name="search" data-purchase-search value="{{ $search }}" placeholder="Cari nomor / supplier..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 sm:w-64">
                <select name="status" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-600 outline-none focus:border-emerald-400">
                    <option value="">Semua status</option>
                    @foreach(['draft' => 'Draft', 'disetujui' => 'Disetujui', 'sebagian_diterima' => 'Sebagian diterima', 'diterima' => 'Diterima', 'dibatalkan' => 'Dibatalkan'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Filter</button>
                @if($search || $status)<a data-purchase-reset href="{{ route('purchases.transactions.index') }}" class="rounded-xl px-3 py-2.5 text-center text-sm font-medium text-slate-500 hover:bg-slate-50">Reset</a>@endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <tr><th class="px-5 py-3.5">Nomor pembelian</th><th class="px-3 py-3.5">Tanggal</th><th class="px-3 py-3.5">Supplier</th><th class="px-3 py-3.5 text-right">Item</th><th class="px-3 py-3.5 text-right">Total</th><th class="px-3 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembelians as $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4"><a href="{{ route('purchases.show', $item) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $item->nomor_pembelian }}</a>@if($item->nomor_faktur_supplier)<p class="mt-1 text-xs text-slate-400">Faktur: {{ $item->nomor_faktur_supplier }}</p>@endif</td>
                            <td class="px-3 py-4 text-slate-600">{{ $item->tanggal_pembelian?->format('d M Y') }}</td>
                            <td class="px-3 py-4 text-slate-700">{{ $item->supplier?->nama ?? '—' }}</td>
                            <td class="px-3 py-4 text-right tabular-nums text-slate-600">{{ $item->detail_count }}</td>
                            <td class="px-3 py-4 text-right font-semibold tabular-nums text-slate-800">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                            <td class="px-3 py-4">
                                @php($statusClass = $item->status === 'draft' ? 'bg-amber-50 text-amber-700' : ($item->status === 'dibatalkan' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'))
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($item->status)) }}</span>
                            </td>
                            <td class="px-5 py-4"><div class="flex justify-end"><a href="{{ route('purchases.show', $item) }}" title="Lihat detail" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="Lihat detail pembelian"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg></a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-14 text-center"><p class="font-semibold text-slate-700">Belum ada transaksi pembelian</p><p class="mt-1 text-sm text-slate-500">Buat transaksi pembelian pertama untuk mulai mencatat pengadaan barang.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">Menampilkan {{ $pembelians->firstItem() ?? 0 }}–{{ $pembelians->lastItem() ?? 0 }} dari {{ $pembelians->total() }} transaksi</p>
            {{ $pembelians->onEachSide(1)->links('vendor.pagination.kasiron') }}
        </div>
    </section>
