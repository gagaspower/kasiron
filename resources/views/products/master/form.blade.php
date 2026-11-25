@extends('layouts.products')

@section('title', $formTitle.' | KasirOn')

@section('product-content')
    <div class="mb-7">
        <a href="{{ route('products-master.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-700"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m14 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Daftar Produk</a>
        <div class="mt-4"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-700/80">Pengaturan katalog</p><h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $formTitle }}</h1><p class="mt-2 text-sm text-slate-500">Lengkapi informasi produk dan satuan yang dapat digunakan saat penjualan.</p></div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><p class="font-semibold">Periksa kembali data produk.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-6" data-product-form>
        @csrf
        @if($formMethod !== 'POST')@method($formMethod)@endif

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5"><h2 class="font-semibold text-slate-900">Informasi Produk</h2><p class="mt-1 text-sm text-slate-500">Informasi dasar untuk identifikasi produk di katalog.</p></div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2"><label for="product-name" class="mb-2 block text-sm font-medium text-slate-700">Nama produk <span class="text-rose-500">*</span></label><input id="product-name" name="nama" value="{{ old('nama', $product->nama) }}" required maxlength="255" placeholder="Contoh: Air Mineral 600 ml" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10"></div>
                <div><label for="product-sku" class="mb-2 block text-sm font-medium text-slate-700">Kode SKU</label><input id="product-sku" name="kode_sku" value="{{ old('kode_sku', $product->kode_sku) }}" maxlength="255" placeholder="Opsional" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10"></div>
                <div><label for="product-category" class="mb-2 block text-sm font-medium text-slate-700">Kategori</label><select id="product-category" name="kategori_id" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10"><option value="">Tanpa kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('kategori_id', $product->kategori_id) === $category->id)>{{ $category->nama }}</option>@endforeach</select></div>
                <div><label for="product-base-unit" class="mb-2 block text-sm font-medium text-slate-700">Satuan dasar <span class="text-rose-500">*</span></label><select id="product-base-unit" name="satuan_dasar_id" required data-product-base-unit class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10"><option value="">Pilih satuan dasar</option>@foreach($units as $unit)<option value="{{ $unit->id }}" data-unit-name="{{ $unit->nama }}" @selected(old('satuan_dasar_id', $product->satuan_dasar_id) === $unit->id)>{{ $unit->nama }} ({{ $unit->kode }})</option>@endforeach</select><p class="mt-1.5 text-xs text-slate-400">Stok produk disimpan menggunakan satuan dasar ini.</p></div>
                <div class="flex flex-wrap items-center gap-5 pt-2"><label class="inline-flex items-center gap-2 text-sm text-slate-700"><input type="hidden" name="kelola_stok" value="0"><input type="checkbox" name="kelola_stok" value="1" @checked(old('kelola_stok', $product->kelola_stok ?? true)) class="size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">Kelola stok produk</label><label class="inline-flex items-center gap-2 text-sm text-slate-700"><input type="hidden" name="aktif" value="0"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $product->aktif ?? true)) class="size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">Produk aktif</label></div>
                <div class="grid grid-cols-2 gap-3 md:col-span-2">
                    <div><label for="product-stock" class="mb-2 block text-sm font-medium text-slate-700">Stok saat ini</label><input id="product-stock" type="number" name="jumlah_stok" value="{{ old('jumlah_stok', $product->jumlah_stok ?? 0) }}" min="0" step="0.000001" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10"></div>
                    <div><label for="product-min-stock" class="mb-2 block text-sm font-medium text-slate-700">Stok minimum</label><input id="product-min-stock" type="number" name="stok_minimum" value="{{ old('stok_minimum', $product->stok_minimum ?? 0) }}" min="0" step="0.000001" class="w-full rounded-xl border border-slate-200 px-3.5 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10"></div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-semibold text-slate-900">Satuan Penjualan & Harga</h2><p class="mt-1 text-sm text-slate-500">Atur harga dan konversi untuk setiap satuan yang bisa dijual.</p></div><button type="button" data-product-add-unit class="inline-flex items-center justify-center gap-2 rounded-xl border border-teal-200 bg-teal-50 px-3.5 py-2.5 text-sm font-semibold text-teal-700 hover:bg-teal-100"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>Tambah Satuan</button></div>
            <div class="mb-4 rounded-xl bg-blue-50 px-4 py-3 text-xs leading-5 text-blue-800">Konversi menunjukkan jumlah satuan dasar. Contoh: satuan dasar PCS, lalu 1 DUS = 12 PCS, isi konversi DUS dengan 12.</div>
            <div class="space-y-4" data-product-units>
                @php($unitRows = old('satuan_penjualan', $saleUnits->map(fn ($item) => ['id' => $item->id, 'satuan_id' => $item->satuan_id, 'konversi' => $item->konversi_ke_satuan_dasar, 'presisi' => $item->presisi_kuantitas, 'harga_jual' => $item->harga_jual, 'barcode' => $item->barcode, 'utama' => $item->satuan_utama])->all()))
                @foreach($unitRows as $index => $row)
                    <div class="rounded-xl border border-slate-200 p-4" data-product-unit-row>
                        <input type="hidden" name="satuan_penjualan[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                        <div class="mb-3 flex items-center justify-between"><p class="text-sm font-semibold text-slate-700">Satuan penjualan <span data-product-unit-number>{{ $index + 1 }}</span></p><button type="button" data-product-remove-unit class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="Hapus satuan"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M5.5 7l1 13h11l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Satuan</label><select name="satuan_penjualan[{{ $index }}][satuan_id]" data-product-unit-select required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">Pilih satuan</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected(($row['satuan_id'] ?? '') === $unit->id)>{{ $unit->nama }} ({{ $unit->kode }})</option>@endforeach</select></div>
                            <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Konversi ke satuan dasar</label><input type="number" name="satuan_penjualan[{{ $index }}][konversi]" value="{{ $row['konversi'] ?? 1 }}" min="0.000001" step="0.000001" required data-product-conversion class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                            <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Harga jual (Rp)</label><input type="number" name="satuan_penjualan[{{ $index }}][harga_jual]" value="{{ $row['harga_jual'] ?? 0 }}" min="0" step="1" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                            <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Barcode</label><input type="text" name="satuan_penjualan[{{ $index }}][barcode]" value="{{ $row['barcode'] ?? '' }}" maxlength="255" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Opsional"></div>
                            <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Presisi kuantitas</label><select name="satuan_penjualan[{{ $index }}][presisi]" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">@for($precision = 0; $precision <= 6; $precision++)<option value="{{ $precision }}" @selected((int) ($row['presisi'] ?? 0) === $precision)>{{ $precision }} angka desimal</option>@endfor</select></div>
                            <div class="flex items-end"><label class="inline-flex items-center gap-2 pb-2 text-sm text-slate-700"><input type="checkbox" name="satuan_penjualan[{{ $index }}][utama]" value="1" @checked(!empty($row['utama'])) data-product-primary class="size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">Satuan utama</label></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <template data-product-unit-template>
                <div class="rounded-xl border border-slate-200 p-4" data-product-unit-row>
                    <div class="mb-3 flex items-center justify-between"><p class="text-sm font-semibold text-slate-700">Satuan penjualan <span data-product-unit-number></span></p><button type="button" data-product-remove-unit class="rounded-lg p-2 text-rose-600 hover:bg-rose-50" aria-label="Hapus satuan"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M5.5 7l1 13h11l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Satuan</label><select data-field="satuan_id" data-product-unit-select required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">Pilih satuan</option>@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->nama }} ({{ $unit->kode }})</option>@endforeach</select></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Konversi ke satuan dasar</label><input data-field="konversi" type="number" value="1" min="0.000001" step="0.000001" required data-product-conversion class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Harga jual (Rp)</label><input data-field="harga_jual" type="number" value="0" min="0" step="1" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Barcode</label><input data-field="barcode" type="text" maxlength="255" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Opsional"></div>
                        <div><label class="mb-1.5 block text-xs font-medium text-slate-600">Presisi kuantitas</label><select data-field="presisi" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">@for($precision = 0; $precision <= 6; $precision++)<option value="{{ $precision }}">{{ $precision }} angka desimal</option>@endfor</select></div>
                        <div class="flex items-end"><label class="inline-flex items-center gap-2 pb-2 text-sm text-slate-700"><input data-field="utama" type="checkbox" value="1" data-product-primary class="size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">Satuan utama</label></div>
                    </div>
                </div>
            </template>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('products-master.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a><button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">Simpan Produk</button></div>
    </form>
@endsection

@section('script')
    @vite('resources/js/pages/products.js')
@endsection
