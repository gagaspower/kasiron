<div data-payment-list-results>
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <form method="GET" action="{{ route('purchases.payments.index') }}" data-payment-list-filter class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_auto_auto] lg:items-end">
            <div class="relative" data-purchase-supplier-picker data-options-url="{{ route('suppliers.options') }}">
                <label for="payment-list-supplier-search" class="mb-1.5 block text-xs font-semibold text-slate-600">Pilih supplier</label>
                <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}" data-purchase-supplier-value>
                <div class="relative">
                    <input id="payment-list-supplier-search" type="text" autocomplete="off" value="{{ $suppliers->firstWhere('id', request('supplier_id'))?->nama }}" placeholder="Semua supplier / cari supplier..." data-purchase-supplier-search aria-controls="payment-list-supplier-options" aria-expanded="false" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 pr-10 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10">
                    <button type="button" data-purchase-supplier-clear aria-label="Hapus supplier" class="absolute inset-y-0 right-0 hidden items-center px-3 text-slate-400 hover:text-rose-600">×</button>
                </div>
                <div id="payment-list-supplier-options" data-purchase-supplier-options class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg" role="listbox"></div>
            </div>
            <label class="block"><span class="mb-1.5 block text-xs font-semibold text-slate-600">Tanggal pembayaran dari</span><input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
            <label class="block"><span class="mb-1.5 block text-xs font-semibold text-slate-600">Tanggal pembayaran sampai</span><input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
            <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Filter</button>
            <a href="{{ route('purchases.payments.index') }}" data-payment-list-reset class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-slate-500 hover:bg-slate-50">Reset</a>
        </form>
        <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Nomor pembayaran</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Supplier</th><th class="px-3 py-3">Metode</th><th class="px-3 py-3 text-right">Nota</th><th class="px-5 py-3 text-right">Total dibayar</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($payments as $payment)<tr><td class="px-5 py-4"><a href="{{ route('purchases.payments.show', $payment) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $payment->nomor_pembayaran }}</a></td><td class="px-3 py-4 text-slate-600">{{ $payment->tanggal_pembayaran?->format('d M Y') }}</td><td class="px-3 py-4">{{ $payment->supplier?->nama }}</td><td class="px-3 py-4 capitalize text-slate-600">{{ $payment->metode_pembayaran }}</td><td class="px-3 py-4 text-right tabular-nums">{{ $payment->detail->count() }}</td><td class="px-5 py-4 text-right font-semibold tabular-nums">Rp {{ number_format((float) $payment->total_bayar, 0, ',', '.') }}</td></tr>
            @empty<tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada pembayaran pembelian.</td></tr>@endforelse
        </tbody></table></div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $payments->onEachSide(1)->links('vendor.pagination.kasiron') }}</div>
    </section>
</div>