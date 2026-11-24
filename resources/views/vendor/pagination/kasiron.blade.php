@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="Previous" class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300">
                <svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m14 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous" class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                <svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m14 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span aria-disabled="true" class="inline-flex size-9 items-center justify-center text-sm text-slate-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex size-9 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-sm font-semibold text-blue-700">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex size-9 items-center justify-center rounded-lg border border-transparent bg-white text-sm font-medium text-slate-600 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-900">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next" class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                <svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m10 6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @else
            <span aria-disabled="true" aria-label="Next" class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300">
                <svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m10 6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        @endif
    </nav>
@endif
