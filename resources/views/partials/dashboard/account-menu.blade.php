<details data-dropdown class="group relative {{ $mobile ? 'w-full' : '' }}">
    <summary class="{{ $mobile ? 'w-full justify-between rounded-xl px-3 py-2.5 hover:bg-slate-50' : 'rounded-xl px-2 py-1.5 hover:bg-slate-100/80' }} flex cursor-pointer list-none items-center gap-2.5 transition [&::-webkit-details-marker]:hidden">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 text-xs font-bold text-blue-700 ring-1 ring-blue-200/70" aria-hidden="true">AD</span>
        <span class="min-w-0 flex-1 text-left leading-tight">
            <span class="block text-sm font-semibold text-slate-800">Admin Toko</span>
            <span class="mt-0.5 block text-xs text-slate-500">Administrator</span>
        </span>
        <svg class="size-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </summary>

    <div class="{{ $mobile ? 'mt-1' : 'absolute right-0 top-full z-40 mt-2' }} w-full min-w-56 rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10">
        <p class="px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Pengaturan akun</p>
        <button type="button" data-account-action="password" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
            <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 10V7a5 5 0 0 1 10 0v3m-12 0h14v11H5V10Zm7 4v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Ganti password
        </button>
        <div class="my-1 border-t border-slate-100"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M10 17l5-5-5-5m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</details>
