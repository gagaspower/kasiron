@extends('layouts.products')

@section('title', 'Catat Pembayaran Pembelian | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.payments.index') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700">← Pembayaran Pembelian</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pembelian</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Catat Pembayaran</h1>
        <p class="mt-2 text-sm text-slate-500">Satu transaksi pembayaran hanya untuk satu supplier, tetapi dapat dialokasikan ke beberapa nota.</p>
    </div>
    @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><p class="font-semibold">Periksa kembali data pembayaran.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="GET" action="{{ route('purchases.payments.create') }}" class="mb-5 flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
        <div class="w-full sm:max-w-sm"><label class="mb-1.5 block text-sm font-medium text-slate-700">Supplier</label><select name="supplier_id" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm"><option value="">Pilih supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected($selectedSupplier === $supplier->id)>{{ $supplier->nama }}</option>@endforeach</select></div>
        <button class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Tampilkan nota</button>
    </form>
    @if($selectedSupplier)
        <form method="POST" action="{{ route('purchases.payments.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="supplier_id" value="{{ $selectedSupplier }}">
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-900">Nota yang masih memiliki tagihan</h2><p class="mt-1 text-xs text-slate-500">Isi nominal yang dialokasikan pada nota. Nota dari supplier lain tidak dapat dipilih.</p></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[750px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Nomor nota</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3 text-right">Total nota</th><th class="px-3 py-3 text-right">Sudah dibayar</th><th class="px-3 py-3 text-right">Sisa tagihan</th><th class="px-5 py-3 text-right">Alokasi sekarang</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        @php($paid = (float) $invoice->sudah_dibayar)
                        @php($remaining = round((float) $invoice->total - $paid, 2))
                        <tr><td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $invoice->nomor_pembelian }}</p><p class="mt-1 text-xs text-slate-400">{{ $invoice->nomor_faktur_supplier }}</p></td><td class="px-3 py-4 text-slate-600">{{ $invoice->tanggal_pembelian?->format('d M Y') }}</td><td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}</td><td class="px-3 py-4 text-right tabular-nums">Rp {{ number_format($paid, 0, ',', '.') }}</td><td class="px-3 py-4 text-right font-semibold tabular-nums">Rp {{ number_format($remaining, 0, ',', '.') }}</td><td class="px-5 py-4"><input name="alokasi[{{ $invoice->id }}]" type="number" min="0" max="{{ $remaining }}" step="0.01" value="{{ old('alokasi.'.$invoice->id, 0) }}" class="w-40 rounded-lg border border-slate-200 px-3 py-2 text-right text-sm tabular-nums"></td></tr>
                    @empty<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Supplier ini tidak memiliki nota dengan sisa tagihan.</td></tr>@endforelse
                </tbody></table></div>
            </section>
            @if($invoices->isNotEmpty())
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Informasi pembayaran</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal pembayaran</label><input type="date" name="tanggal_pembayaran" value="{{ old('tanggal_pembayaran', now()->toDateString()) }}" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm"></div>
                    <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Uang yang benar-benar dibayarkan</label><input type="number" name="total_bayar" min="0.01" step="0.01" value="{{ old('total_bayar') }}" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm"></div>
                    <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Metode pembayaran</label><select name="metode_pembayaran" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm"><option value="transfer">Transfer</option><option value="tunai">Tunai</option><option value="qris">QRIS</option><option value="cek">Cek</option><option value="giro">Giro</option><option value="lainnya">Lainnya</option></select></div>
                    <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Referensi pembayaran</label><input name="referensi_pembayaran" value="{{ old('referensi_pembayaran') }}" maxlength="150" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm"></div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label><textarea name="catatan" rows="2" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm">{{ old('catatan') }}</textarea></div>
                </div>
                <label class="mt-5 flex items-start gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="jadikan_titipan" value="1" class="mt-0.5 rounded border-slate-300 text-emerald-600"><span><span class="block text-sm font-semibold text-slate-800">Jadikan kelebihan pembayaran sebagai titipan</span><span class="mt-1 block text-xs leading-5 text-slate-500">Jika uang yang dibayarkan lebih besar daripada total alokasi nota, kelebihannya langsung masuk saldo titipan. Jika tidak dicentang, kelebihan masuk daftar yang belum diselesaikan.</span></span></label>
                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><a href="{{ route('purchases.payments.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-slate-600">Batal</a><button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Pembayaran</button></div>
            </section>
            @endif
        </form>
    @endif
@endsection
