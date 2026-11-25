@extends('layouts.products')

@section('title', 'Supplier | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-emerald-700">
            <svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m14 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Manajemen Produk
        </a>
        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/80">Pengaturan katalog</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Supplier</h1>
                <p class="mt-2 text-sm text-slate-500">Kelola data pemasok untuk transaksi pembelian toko Anda.</p>
            </div>
            @unless($trash)
                <button type="button" data-supplier-create class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/15">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Tambah Supplier
                </button>
            @endunless
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
    @endif

    <div data-supplier-results>
        @include('products.suppliers.partials.results')
    </div>

    @if($errors->any())<span data-supplier-validation-error class="hidden"></span>@endif

    <dialog data-supplier-modal class="fixed inset-0 m-auto max-h-[calc(100%-2rem)] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-2xl border-0 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
        <form method="POST" action="{{ session('supplier_form_action', route('suppliers.store')) }}" data-supplier-form>
            @csrf
            <input type="hidden" name="_method" value="{{ session('supplier_form_method', 'POST') }}" data-supplier-method>
            <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                <div><h2 data-supplier-modal-title class="text-lg font-bold text-slate-900">Tambah supplier</h2><p class="mt-1 text-sm text-slate-500">Lengkapi informasi pemasok.</p></div>
                <button type="button" data-supplier-close class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="space-y-4 px-6 py-5">
                <div>
                    <label for="supplier-name" class="mb-2 block text-sm font-medium text-slate-700">Nama supplier <span class="text-rose-500">*</span></label>
                    <input id="supplier-name" name="nama" value="{{ old('nama') }}" maxlength="255" required placeholder="Contoh: Toko Grosir Makmur" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10">
                    @error('nama')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="supplier-phone" class="mb-2 block text-sm font-medium text-slate-700">Nomor telepon</label>
                    <input id="supplier-phone" name="nomor_telepon" type="tel" value="{{ old('nomor_telepon') }}" maxlength="30" placeholder="Contoh: 081234567890" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10">
                    @error('nomor_telepon')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="supplier-address" class="mb-2 block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea id="supplier-address" name="alamat" rows="3" maxlength="2000" placeholder="Alamat supplier (opsional)" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10">{{ old('alamat') }}</textarea>
                    @error('alamat')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <p data-supplier-field-error class="hidden text-xs text-rose-600"></p>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                <button type="button" data-supplier-close class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan supplier</button>
            </div>
        </form>
    </dialog>

    <dialog data-supplier-confirm-modal class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
        <div class="p-6">
            <div class="mb-4 flex size-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"><svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M10.3 3.9 2.9 17a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h2 class="text-lg font-bold text-slate-900">Konfirmasi tindakan</h2>
            <p data-supplier-confirm-message class="mt-2 text-sm leading-6 text-slate-500"></p>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
            <button type="button" data-supplier-confirm-cancel class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button>
            <button type="button" data-supplier-confirm-accept class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">Ya, lanjutkan</button>
        </div>
    </dialog>
@endsection

@section('script')
    @vite('resources/js/pages/suppliers.js')
@endsection
