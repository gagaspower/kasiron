<header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/85 backdrop-blur-xl">
    <div class="mx-auto flex h-[68px] max-w-[1440px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5" aria-label="KasirOn, ke dashboard">
            <span class="flex size-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 8.5h14l1 11H4l1-11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9 9V6a3 3 0 0 1 6 0v3M8 13h8M9 16.5h.01M15 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="text-lg font-bold tracking-tight text-slate-900">Kasir<span class="text-blue-600">On</span></span>
        </a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
            @foreach ($navigation as $item)
                @if (isset($item['children']))
                    <details data-dropdown class="group relative">
                        <summary class="{{ ($item['active'] ?? false) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-950' }} flex cursor-pointer list-none items-center gap-1.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition [&::-webkit-details-marker]:hidden">
                            {{ $item['label'] }}
                            <svg class="size-3.5 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </summary>
                        <div class="absolute left-0 top-full z-40 mt-2 w-56 rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10">
                            <p class="px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $item['label'] }}</p>
                            @foreach ($item['children'] as $child)
                                @if ($child['disabled'] ?? false)
                                    <div class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400">
                                        <span>{{ $child['label'] }}</span>
                                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-300">{{ $child['status'] ?? 'Segera' }}</span>
                                    </div>
                                @else
                                    <a href="{{ $child['href'] }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                        {{ $child['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @else
                    <a
                        href="{{ $item['href'] }}"
                        @if ($item['active']) aria-current="page" @endif
                        class="{{ $item['active'] ? 'bg-blue-50 text-blue-700 shadow-sm shadow-blue-950/[0.03]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-950' }} rounded-xl px-3.5 py-2.5 text-sm font-semibold transition"
                    >
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="hidden shrink-0 items-center gap-3 md:flex">
            <button type="button" data-pwa-install class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-blue-100 bg-blue-50/80 px-3 text-sm font-semibold text-blue-700 transition hover:border-blue-200 hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 16v4h14v-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Install aplikasi
            </button>
            <button type="button" class="relative flex size-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Notifikasi">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="absolute right-2.5 top-2.5 size-1.5 rounded-full bg-blue-600 ring-2 ring-white"></span>
            </button>
            @include('partials.dashboard.account-menu', ['mobile' => false])
        </div>

        <details data-dropdown class="relative md:hidden">
            <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200/80 bg-white/70 text-slate-600 transition hover:bg-white [&::-webkit-details-marker]:hidden" aria-label="Buka navigasi">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </summary>
            <nav class="absolute right-0 top-12 z-40 w-[min(20rem,calc(100vw-2rem))] rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10" aria-label="Navigasi mobile">
                @foreach ($navigation as $item)
                    @if (isset($item['children']))
                        <details data-dropdown class="group">
                            <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl px-3.5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                                {{ $item['label'] }}
                                <svg class="size-4 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </summary>
                            <div class="ml-3 border-l border-slate-100 py-1 pl-2">
                                @foreach ($item['children'] as $child)
                                    @if ($child['disabled'] ?? false)
                                        <div class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm text-slate-400">
                                            <span>{{ $child['label'] }}</span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-300">{{ $child['status'] ?? 'Segera' }}</span>
                                        </div>
                                    @else
                                        <a href="{{ $child['href'] }}" class="block rounded-lg px-3 py-2.5 text-sm text-slate-500 transition hover:bg-blue-50 hover:text-blue-700">
                                            {{ $child['label'] }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </details>
                    @else
                        <a
                            href="{{ $item['href'] }}"
                            @if ($item['active']) aria-current="page" @endif
                            class="{{ $item['active'] ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }} block rounded-xl px-3.5 py-3 text-sm font-semibold"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
                <div class="my-2 border-t border-slate-100"></div>
                <button type="button" data-pwa-install class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 16v4h14v-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Install aplikasi
                </button>
                <div class="my-2 border-t border-slate-100"></div>
                @include('partials.dashboard.account-menu', ['mobile' => true])
            </nav>
        </details>
    </div>

    <dialog data-pwa-install-help class="m-auto w-[min(26rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl shadow-slate-950/20 backdrop:bg-slate-950/30">
        <div class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 16v4h14v-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h2 class="mt-4 text-lg font-semibold">Pasang KasirOn</h2>
                </div>
                <form method="dialog">
                    <button type="submit" class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </button>
                </form>
            </div>
            <p class="mt-2 text-sm leading-6 text-slate-500">Jika prompt instalasi belum muncul, buka menu browser lalu pilih <span class="font-medium text-slate-700">Install app</span> atau <span class="font-medium text-slate-700">Tambahkan ke layar utama</span>.</p>
            <p class="mt-3 text-xs leading-5 text-slate-400">Instalasi desktop tersedia di Chrome atau Edge. Situs harus dibuka melalui HTTPS atau localhost.</p>
        </div>
    </dialog>
</header>

<div data-account-notice hidden role="status" aria-live="polite" class="hidden fixed right-4 top-20 z-50 w-[min(24rem,calc(100vw-2rem))] items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl shadow-slate-900/10">
    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 9v4m0 4h.01M10.3 4.9 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    <p data-account-notice-message class="flex-1 pt-1 text-sm leading-5 text-slate-600"></p>
    <button type="button" data-account-notice-dismiss class="flex size-7 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup pemberitahuan">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
    </button>
</div>
