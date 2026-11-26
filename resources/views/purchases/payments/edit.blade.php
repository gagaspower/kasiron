@extends('layouts.products')

@section('title', 'Revisi '.$payment->nomor_pembayaran.' | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.payments.show', $payment) }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">← Detail Pembayaran</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Revisi Pembayaran</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $payment->nomor_pembayaran }} · {{ $payment->supplier?->nama }}</p>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold">Periksa kembali data pembayaran.</p>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchases.payments.update', $payment) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Alokasi ke nota pembelian</h2>
                <p class="mt-1 text-xs text-slate-500">Ubah alokasi pembayaran untuk nota milik supplier ini.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                        <tr><th class="px-5 py-3">Nomor nota</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3 text-right">Total nota</th><th class="px-3 py-3 text-right">Sudah dibayar</th><th class="px-5 py-3 text-right">Alokasi pembayaran</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoices as $invoice)
                            @php($own = (float) ($currentAllocations->get($invoice->id)?->jumlah_bayar ?? 0))
                            @php($paidOther = max(0, (float) $invoice->sudah_dibayar - $own))
                            <tr>
                                <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $invoice->nomor_pembelian }}</p><p class="mt-1 text-xs text-slate-400">{{ $invoice->nomor_faktur_supplier }}</p></td>
                                <td class="px-3 py-4 text-slate-600">{{ $invoice->tanggal_pembelian?->format('d M Y') }}</td>
                                <td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td>
                                <td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format($paidOther, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <input type="hidden" name="alokasi[{{ $invoice->id }}]" value="{{ old('alokasi.'.$invoice->id, $own) }}">
                                    <input type="text" inputmode="decimal" autocomplete="off" value="{{ old('alokasi.'.$invoice->id, $own) }}" data-money-display class="ml-auto block w-40 rounded-lg border border-slate-200 px-3 py-2 text-right text-sm tabular-nums">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">Informasi pembayaran</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal pembayaran</label><input type="date" name="tanggal_pembayaran" value="{{ old('tanggal_pembayaran', $payment->tanggal_pembayaran?->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Total uang dibayarkan</label><input type="hidden" name="total_bayar" value="{{ old('total_bayar', $payment->total_bayar) }}"><input type="text" inputmode="decimal" autocomplete="off" value="{{ old('total_bayar', $payment->total_bayar) }}" data-money-display class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Metode pembayaran</label><select name="metode_pembayaran" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm"><option value="tunai" @selected(old('metode_pembayaran', $payment->metode_pembayaran) === 'tunai')>Tunai</option><option value="transfer" @selected(old('metode_pembayaran', $payment->metode_pembayaran) === 'transfer')>Transfer/QRIS</option></select></div>
                <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Referensi pembayaran</label><input name="referensi_pembayaran" value="{{ old('referensi_pembayaran', $payment->referensi_pembayaran) }}" maxlength="150" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm"></div>
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label><textarea name="catatan" rows="2" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm">{{ old('catatan', $payment->catatan) }}</textarea></div>
            </div>
        </section>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('purchases.payments.show', $payment) }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600">Kembali</a>
            <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Revisi</button>
        </div>
    </form>
@endsection

@section('script')
    @vite(['resources/js/pages/purchases.js'])
@endsection
