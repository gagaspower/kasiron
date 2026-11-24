<section id="ringkasan" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Ringkasan pembayaran</h2>
            <p class="mt-1 text-sm text-slate-500">Penjualan berdasarkan metode.</p>
        </div>
        <span class="rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-500">Hari ini</span>
    </div>

    <div class="mt-6 flex h-2.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="Komposisi pembayaran hari ini">
        @foreach ($paymentMethods as $method)
            <span class="{{ $method['color'] }}" style="width: {{ $method['percentage'] }}%"></span>
        @endforeach
    </div>

    <div class="mt-5 space-y-4">
        @foreach ($paymentMethods as $method)
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-full {{ $method['color'] }}"></span>
                    <span class="text-sm font-medium text-slate-600">{{ $method['label'] }}</span>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-800">{{ $method['amount'] }}</p>
                    <p class="text-xs text-slate-400">{{ $method['percentage'] }}% dari penjualan</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
