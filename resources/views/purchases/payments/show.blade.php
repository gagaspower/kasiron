@extends('layouts.products')

@section('title', $payment->nomor_pembayaran.' | KasirOn')

@section('product-content')
    <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('purchases.payments.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">← Pembayaran Pembelian</a>
            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Detail pembayaran</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $payment->nomor_pembayaran }}</h1>
                @if($payment->status === 'dibatalkan')
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Dibatalkan</span>
                @else
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                @endif
            </div>
        </div>
        @if($payment->status === 'aktif')
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchases.payments.edit', $payment) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Revisi Pembayaran</a>
                <button type="button" data-open-cancel-modal class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50">Batalkan Pembayaran</button>
            </div>
        @endif
    </div>

    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    @if($payment->status === 'dibatalkan')
        <section class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-5">
            <h2 class="font-semibold text-rose-900">Pembayaran ini telah dibatalkan</h2>
            <p class="mt-2 text-sm text-rose-800">Alasan: {{ $payment->alasan_pembatalan }}</p>
            <p class="mt-1 text-xs text-rose-700">Dibatalkan {{ $payment->dibatalkan_pada?->format('d M Y H:i') }} oleh {{ $payment->dibatalkanOleh?->name ?? 'pengguna yang tidak lagi tersedia' }}</p>
        </section>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Informasi pembayaran</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><p class="text-xs text-slate-400">Supplier</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $payment->supplier?->nama }}</p></div>
                    <div><p class="text-xs text-slate-400">Tanggal</p><p class="mt-1 text-sm text-slate-800">{{ $payment->tanggal_pembayaran?->format('d M Y') }}</p></div>
                    <div><p class="text-xs text-slate-400">Metode</p><p class="mt-1 text-sm capitalize text-slate-800">{{ $payment->metode_pembayaran }}</p></div>
                    <div><p class="text-xs text-slate-400">Referensi</p><p class="mt-1 text-sm text-slate-800">{{ $payment->referensi_pembayaran ?: '—' }}</p></div>
                </div>
                @if($payment->catatan)<p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $payment->catatan }}</p>@endif
            </div>
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Alokasi ke nota pembelian</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[600px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Nomor nota</th><th class="px-3 py-3">Tanggal pembelian</th><th class="px-5 py-3 text-right">Nominal dialokasikan</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @foreach($payment->detail as $detail)<tr><td class="px-5 py-4"><a href="{{ route('purchases.show', $detail->pembelian) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $detail->pembelian?->nomor_pembelian }}</a></td><td class="px-3 py-4 text-slate-600">{{ $detail->pembelian?->tanggal_pembelian?->format('d M Y') }}</td><td class="px-5 py-4 text-right font-semibold tabular-nums">Rp {{ number_format((float) $detail->jumlah_bayar, 0, ',', '.') }}</td></tr>@endforeach
                </tbody></table></div>
            </section>
        </section>
        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Ringkasan</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Total uang dibayarkan</span><strong>Rp {{ number_format((float) $payment->total_bayar, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Alokasi nota</span><strong>Rp {{ number_format((float) $payment->detail->sum('jumlah_bayar'), 0, ',', '.') }}</strong></div>
                    @if($payment->kelebihan)<div class="border-t border-slate-100 pt-3"><p class="text-xs text-slate-500">Kelebihan pembayaran</p><p class="mt-1 text-lg font-bold text-amber-700">Rp {{ number_format((float) $payment->kelebihan->jumlah_kelebihan, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">Sudah diselesaikan: Rp {{ number_format((float) $payment->kelebihan->penyelesaian->sum('jumlah'), 0, ',', '.') }}</p></div>@endif
                </div>
            </section>
        </aside>
    </div>

    @if($payment->status === 'aktif')
        <div data-cancel-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="cancel-payment-title">
                <h2 id="cancel-payment-title" class="text-lg font-bold text-slate-900">Batalkan Pembayaran?</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Pembayaran {{ $payment->nomor_pembayaran }} akan dibatalkan. Alokasinya tidak lagi dihitung sebagai pembayaran nota dan sisa tagihan akan diperbarui.</p>
                <form method="POST" action="{{ route('purchases.payments.cancel', $payment) }}" class="mt-5">
                    @csrf
                    @method('PATCH')
                    <label for="cancel-payment-reason" class="mb-2 block text-sm font-medium text-slate-700">Alasan pembatalan <span class="text-rose-500">*</span></label>
                    <textarea id="cancel-payment-reason" name="alasan_pembatalan" rows="3" maxlength="2000" required class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none focus:border-rose-400 focus:ring-4 focus:ring-rose-500/10" placeholder="Tuliskan alasan pembatalan..."></textarea>
                    @error('alasan_pembatalan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" data-close-cancel-modal class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Kembali</button>
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Ya, Batalkan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        (() => {
            const modal = document.querySelector('[data-cancel-modal]');
            if (!modal) return;
            const open = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.querySelector('textarea')?.focus();
            };
            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };
            document.querySelector('[data-open-cancel-modal]')?.addEventListener('click', open);
            modal.querySelector('[data-close-cancel-modal]')?.addEventListener('click', close);
            modal.addEventListener('click', event => {
                if (event.target === modal) close();
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') close();
            });
        })();
    </script>
@endsection
