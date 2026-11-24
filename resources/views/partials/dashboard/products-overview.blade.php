<section id="produk" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Produk terlaris</h2>
            <p class="mt-1 text-sm text-slate-500">Paling banyak terjual hari ini.</p>
        </div>
        <span class="flex size-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </div>

    <div class="mt-4 divide-y divide-slate-100">
        @foreach ($topProducts as $product)
            <div class="flex items-center gap-3 py-3.5 first:pt-1 last:pb-1">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl text-xs font-bold {{ $product['tone'] }}">{{ $product['initials'] }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-800">{{ $product['name'] }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $product['category'] }}</p>
                </div>
                <span class="shrink-0 text-xs font-medium text-slate-500">{{ $product['sold'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50/70 p-3.5">
        <div class="flex items-center gap-2">
            <svg class="size-4 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 9v4m0 4h.01M10.3 4.9 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="text-xs font-semibold text-amber-800">Stok perlu diperiksa</p>
        </div>
        <ul class="mt-2 space-y-1.5 pl-6 text-xs text-amber-800/80">
            @foreach ($stockAlerts as $stockAlert)
                <li class="flex justify-between gap-3">
                    <span>{{ $stockAlert['name'] }}</span>
                    <span class="shrink-0 font-medium">{{ $stockAlert['quantity'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
