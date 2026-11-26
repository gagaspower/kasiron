@extends('layouts.products')

@section('title', $formTitle.' | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-emerald-700">← Daftar Pembelian</a>
        <div class="mt-4"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Transaksi pembelian</p><h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $formTitle }}</h1><p class="mt-2 text-sm text-slate-500">Anda dapat menyimpan sebagai draft untuk diproses nanti, atau langsung menyetujui pembelian.</p></div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><p class="font-semibold">Periksa kembali data pembelian.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-6" data-purchase-form>
        @csrf
        @if($formMethod !== 'POST')@method($formMethod)@endif
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5"><h2 class="font-semibold text-slate-900">Informasi Pembelian</h2><p class="mt-1 text-sm text-slate-500">Pilih supplier dan tanggal transaksi.</p></div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="relative" data-purchase-supplier-picker data-options-url="{{ route('suppliers.options') }}">
                    <label for="purchase-supplier-search" class="mb-2 block text-sm font-medium text-slate-700">Supplier <span class="text-rose-500">*</span></label>
                    <input type="hidden" name="supplier_id" value="{{ old('supplier_id', $pembelian->supplier_id) }}" data-purchase-supplier-value>
                    <div class="relative">
                        <input id="purchase-supplier-search" type="text" autocomplete="off" value="{{ old('supplier_id') ? $suppliers->firstWhere('id', old('supplier_id'))?->nama : $pembelian->supplier?->nama }}" placeholder="Cari supplier..." data-purchase-supplier-search aria-controls="purchase-supplier-options" aria-expanded="false" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 pr-10 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10">
                        <button type="button" data-purchase-supplier-clear aria-label="Hapus supplier" class="absolute inset-y-0 right-0 hidden items-center px-3 text-slate-400 hover:text-rose-600">×</button>
                    </div>
                    <div id="purchase-supplier-options" data-purchase-supplier-options class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg" role="listbox"></div>
                </div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Nomor faktur supplier</label><input name="nomor_faktur_supplier" value="{{ old('nomor_faktur_supplier', $pembelian->nomor_faktur_supplier) }}" maxlength="100" placeholder="Opsional" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm"></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tanggal pembelian <span class="text-rose-500">*</span></label><input type="date" name="tanggal_pembelian" value="{{ old('tanggal_pembelian', $pembelian->tanggal_pembelian?->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm"></div>
                <div>
                    <span class="mb-2 block text-sm font-medium text-slate-700">Jenis pembayaran <span class="text-rose-500">*</span></span>
                    @php($jenisPembayaran = old('jenis_pembayaran', $pembelian->jenis_pembayaran ?? 'tunai'))
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis_pembayaran" value="tunai" data-purchase-payment-type required @checked($jenisPembayaran === 'tunai') class="peer sr-only">
                            <span class="flex items-center justify-start gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-left text-sm font-medium text-slate-600 transition hover:border-emerald-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/20">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 10h18M7 15h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                Tunai
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis_pembayaran" value="kredit" data-purchase-payment-type required @checked($jenisPembayaran === 'kredit') class="peer sr-only">
                            <span class="flex items-center justify-start gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-left text-sm font-medium text-slate-600 transition hover:border-emerald-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/20">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Kredit / Tempo
                            </span>
                        </label>
                    </div>
                </div>
                <div data-purchase-due-date><label for="purchase-due-date" class="mb-2 block text-sm font-medium text-slate-700">Tanggal jatuh tempo <span class="text-rose-500" data-due-date-required>*</span></label><input id="purchase-due-date" type="date" name="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo', $pembelian->tanggal_jatuh_tempo?->format('Y-m-d')) }}" data-purchase-due-date-input class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm"></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-slate-700">Catatan</label><textarea name="catatan" rows="2" maxlength="5000" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm" placeholder="Catatan pembelian (opsional)">{{ old('catatan', $pembelian->catatan) }}</textarea></div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-semibold text-slate-900">Rincian Produk</h2><p class="mt-1 text-sm text-slate-500">Pilih produk, satuan pembelian, jumlah, dan harga beli.</p></div><button type="button" data-purchase-add-row class="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">+ Tambah Produk</button></div>
            <div class="space-y-3" data-purchase-rows></div>
            <template data-purchase-row-template>
                <div class="rounded-xl border border-slate-200 p-4" data-purchase-row>
                    <div class="mb-3 flex items-center justify-between"><span class="text-sm font-semibold text-slate-700">Item <span data-purchase-row-number></span></span><button type="button" data-purchase-remove-row class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" title="Hapus item" aria-label="Hapus item"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M5.5 7l1 13h11l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></button></div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="relative sm:col-span-2" data-purchase-product-picker data-options-url="{{ route('purchases.products.options') }}">
                            <label class="mb-1.5 block text-xs font-medium text-slate-600">Produk</label>
                            <input type="hidden" data-field="produk_id" required>
                            <div class="relative">
                                <input type="text" autocomplete="off" placeholder="Cari nama produk, SKU, atau barcode..." data-purchase-product-search aria-expanded="false" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 pr-10 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10">
                                <button type="button" data-purchase-product-clear aria-label="Hapus produk" class="absolute inset-y-0 right-0 hidden items-center px-3 text-slate-400 hover:text-rose-600">×</button>
                            </div>
                            <div data-purchase-product-options class="absolute z-30 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg" role="listbox"></div>
                        </div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Satuan</label><select data-field="satuan_produk_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">Pilih satuan</option></select></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Jumlah</label><input data-field="jumlah" type="number" min="0.000001" step="0.000001" value="1" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Harga beli / satuan (Rp)</label><input type="hidden" data-field="harga_beli" value="0"><input data-money-display type="text" inputmode="numeric" value="0" autocomplete="off" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Diskon item (Rp)</label><input type="hidden" data-field="diskon" value="0"><input data-money-display type="text" inputmode="numeric" value="0" autocomplete="off" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                        <div class="sm:col-span-2"><label class="mb-1.5 block text-xs font-medium text-slate-600">Catatan item</label><input data-field="catatan" type="text" maxlength="1000" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Opsional"></div>
                    </div>
                    <p class="mt-3 text-right text-sm font-semibold text-slate-700">Subtotal item: <span data-purchase-row-subtotal>Rp 0</span></p>
                </div>
            </template>
            <div class="mt-5 border-t border-slate-100 pt-5">
                <div class="ml-auto grid max-w-sm gap-3">
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Subtotal produk</span><strong data-purchase-subtotal>Rp 0</strong></div>
                    <div class="flex items-center justify-between gap-4"><label for="purchase-discount" class="text-sm text-slate-600">Diskon transaksi (Rp)</label><input type="hidden" name="diskon" value="{{ old('diskon', $pembelian->diskon ?? 0) }}" data-purchase-discount><input id="purchase-discount" data-money-display type="text" inputmode="numeric" value="{{ old('diskon', $pembelian->diskon ?? 0) }}" autocomplete="off" class="w-36 rounded-lg border border-slate-200 px-3 py-2 text-right text-sm"></div>
                    <div class="flex items-center justify-between gap-4"><label for="purchase-extra" class="text-sm text-slate-600">Biaya lain (Rp)</label><input type="hidden" name="biaya_lain" value="{{ old('biaya_lain', $pembelian->biaya_lain ?? 0) }}" data-purchase-extra><input id="purchase-extra" data-money-display type="text" inputmode="numeric" value="{{ old('biaya_lain', $pembelian->biaya_lain ?? 0) }}" autocomplete="off" class="w-36 rounded-lg border border-slate-200 px-3 py-2 text-right text-sm"></div>
                    <div class="flex justify-between border-t border-slate-200 pt-3"><span class="font-semibold text-slate-800">Total Pembelian</span><strong data-purchase-total class="text-lg text-emerald-700">Rp 0</strong></div>
                </div>
            </div>
        </section>
        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">@if($formMethod === 'POST')<div class="flex items-start gap-2"><svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 11v5m0-8h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><div class="min-w-0 space-y-2"><p class="leading-6"><span class="font-semibold">Simpan Draft:</span> Simpan transaksi sementara jika data masih perlu diperiksa atau dilengkapi. Draft belum berstatus disetujui dan belum dapat diproses sebagai penerimaan barang.</p><p class="leading-6"><span class="font-semibold">Simpan &amp; Setujui:</span> Simpan transaksi sekaligus menyetujuinya agar bisa langsung dilanjutkan ke penerimaan barang. Persetujuan tidak menambah stok; stok bertambah setelah barang diterima.</p></div></div>@endif</div><div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>@if($formMethod === 'POST')<button type="submit" name="proses" value="draft" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Simpan Draft</button><button type="submit" name="proses" value="setujui" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Simpan &amp; Setujui</button>@else<button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Perubahan Draft</button>@endif</div>
    </form>
@endsection

@section('script')
    <script>
        window.purchaseInitialRows = @json($initialPurchaseRows);

        (() => {
            const paymentTypes = document.querySelectorAll('[data-purchase-payment-type]');
            const dueDateInput = document.querySelector('[data-purchase-due-date-input]');
            const dueDateRequired = document.querySelector('[data-due-date-required]');
            const dueDateWrapper = document.querySelector('[data-purchase-due-date]');
            const today = @json(now()->toDateString());

            if (!paymentTypes.length || !dueDateInput || !dueDateWrapper) return;

            const syncDueDateField = () => {
                const selectedPaymentType = document.querySelector('[data-purchase-payment-type]:checked');
                const isCredit = selectedPaymentType?.value === 'kredit';
                dueDateInput.required = isCredit;
                dueDateInput.disabled = !isCredit;
                dueDateWrapper.classList.toggle('opacity-50', !isCredit);
                if (dueDateRequired) dueDateRequired.classList.toggle('hidden', !isCredit);
                if (isCredit && !dueDateInput.value) dueDateInput.value = today;
                if (!isCredit) dueDateInput.value = '';
            };

            paymentTypes.forEach((paymentType) => paymentType.addEventListener('change', syncDueDateField));
            syncDueDateField();
        })();
    </script>
    @vite('resources/js/pages/purchases.js')
@endsection
