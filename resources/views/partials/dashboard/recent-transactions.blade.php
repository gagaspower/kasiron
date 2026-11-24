<section id="transaksi" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-900/[0.02]">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Transaksi terbaru</h2>
            <p class="mt-1 text-sm text-slate-500">Pantau aktivitas penjualan toko Anda.</p>
        </div>
        <label class="relative block w-full sm:max-w-56">
            <span class="sr-only">Cari transaksi</span>
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.7"/>
                <path d="m16 16 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
            <input
                type="search"
                data-transaction-search
                placeholder="Cari transaksi..."
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10"
            >
        </label>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th scope="col" class="px-6 py-3.5">Pelanggan</th>
                    <th scope="col" class="px-4 py-3.5">Waktu</th>
                    <th scope="col" class="px-4 py-3.5">Pembayaran</th>
                    <th scope="col" class="px-4 py-3.5">Total</th>
                    <th scope="col" class="px-6 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($transactions as $transaction)
                    @php
                        $statusTone = match ($transaction['status']) {
                            'Selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                            'Diproses' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                            default => 'bg-rose-50 text-rose-700 ring-rose-600/10',
                        };
                    @endphp
                    <tr
                        data-transaction-row
                        data-search="{{ \Illuminate\Support\Str::lower($transaction['id'].' '.$transaction['customer'].' '.$transaction['payment'].' '.$transaction['status']) }}"
                        class="transition hover:bg-slate-50/70"
                    >
                        <th scope="row" class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">{{ $transaction['initials'] }}</span>
                                <span>
                                    <span class="block font-semibold">{{ $transaction['customer'] }}</span>
                                    <span class="mt-0.5 block text-xs font-normal text-slate-400">{{ $transaction['id'] }} · {{ $transaction['items'] }}</span>
                                </span>
                            </div>
                        </th>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-500">Hari ini, {{ $transaction['time'] }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $transaction['payment'] }}</td>
                        <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-800">{{ $transaction['total'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusTone }}">{{ $transaction['status'] }}</span>
                        </td>
                    </tr>
                @endforeach
                <tr data-transaction-empty hidden>
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Transaksi tidak ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between border-t border-slate-100 px-5 py-4 sm:px-6">
        <p class="text-xs text-slate-500">Menampilkan {{ count($transactions) }} transaksi terakhir</p>
        <a href="#transaksi" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 transition hover:text-blue-700">
            Lihat semua
            <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
</section>
