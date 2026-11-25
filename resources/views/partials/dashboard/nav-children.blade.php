@foreach ($items as $child)
    @if ($child['disabled'] ?? false)
        <div class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm {{ $mobile ? 'text-slate-400' : 'font-medium text-slate-400' }}">
            <span>{{ $child['label'] }}</span>
            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-300">{{ $child['status'] ?? 'Segera' }}</span>
        </div>
    @elseif (isset($child['children']))
        <details data-dropdown class="group {{ $mobile ? '' : 'relative' }}" @if ($child['active'] ?? false) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition [&::-webkit-details-marker]:hidden {{ ($child['active'] ?? false) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                {{ $child['label'] }}
                <svg class="size-3.5 shrink-0 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </summary>
            <div class="{{ $mobile ? 'ml-3 border-l border-slate-100 py-1 pl-2' : 'absolute left-full top-0 z-50 ml-1 w-56 rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10' }}">
                @include('partials.dashboard.nav-children', ['items' => $child['children'], 'mobile' => $mobile])
            </div>
        </details>
    @else
        <a href="{{ $child['href'] }}" @if ($child['active'] ?? false) aria-current="page" @endif class="block rounded-xl px-3 py-2.5 text-sm font-medium transition {{ ($child['active'] ?? false) ? 'bg-blue-50 text-blue-700' : ($mobile ? 'text-slate-500 hover:bg-blue-50 hover:text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700') }}">
            {{ $child['label'] }}
        </a>
    @endif
@endforeach
