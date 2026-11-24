<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan usaha">
    @foreach ($stats as $stat)
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.02]">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                </div>
                @php
                    $iconTone = [
                        'blue' => 'bg-blue-50 text-blue-600',
                        'violet' => 'bg-violet-50 text-violet-600',
                        'teal' => 'bg-teal-50 text-teal-600',
                        'amber' => 'bg-amber-50 text-amber-600',
                    ][$stat['tone']];
                @endphp
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $iconTone }}">
                    @if ($stat['icon'] === 'revenue')
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6M16 7h3v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @elseif ($stat['icon'] === 'transactions')
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3h10v18H7zM10 7h4M10 11h4M10 15h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @elseif ($stat['icon'] === 'average')
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3v18m4-14H9.5a3 3 0 0 0 0 6h5a3 3 0 0 1 0 6H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    @else
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @endif
                </span>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                @if ($stat['icon'] !== 'stock')
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                        <svg class="size-3.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="m3 10 5-5 5 5M8 5v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ $stat['change'] }}
                    </span>
                @else
                    <span class="font-semibold text-amber-700">{{ $stat['change'] }}</span>
                @endif
                <span class="text-slate-400">{{ $stat['comparison'] }}</span>
            </div>
        </article>
    @endforeach
</section>
