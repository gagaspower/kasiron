<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PembelianController extends Controller
{
    public function index(): View
    {
        return view('purchases.index', [
            'navigation' => $this->navigation(),
        ]);
    }

    public function transactions(Request $request): View
    {
        $search = trim($request->string('search')->value());
        $status = $request->string('status')->value();

        $pembelians = Pembelian::query()
            ->with('supplier')
            ->withCount('detail')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('nomor_pembelian', 'like', "%{$search}%")
                    ->orWhere('nomor_faktur_supplier', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('nama', 'like', "%{$search}%"));
            }))
            ->when($status !== '' && in_array($status, ['draft', 'disetujui', 'sebagian_diterima', 'diterima', 'dibatalkan'], true), fn (Builder $query) => $query->where('status', $status))
            ->latest('dibuat_pada')
            ->paginate(10)
            ->withPath(route('purchases.index'))
            ->withQueryString();

        return view('purchases.transactions.index', [
            'pembelians' => $pembelians,
            'search' => $search,
            'status' => $status,
            'navigation' => $this->navigation(),
        ]);
    }

    public function create(): View
    {
        return view('purchases.transactions.form', [
            'pembelian' => new Pembelian([
                'tanggal_pembelian' => now()->toDateString(),
                'status' => Pembelian::STATUS_DRAFT,
            ]),
            'suppliers' => Supplier::query()->where('aktif', true)->orderBy('nama')->get(),
            'productOptions' => $this->productOptions(),
            'formTitle' => 'Buat Pembelian',
            'formAction' => route('purchases.store'),
            'formMethod' => 'POST',
            'navigation' => $this->navigation(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        $pembelian = DB::transaction(function () use ($validated, $request) {
            $subtotal = collect($validated['detail'])->sum(fn ($row) => ((float) $row['jumlah'] * (float) $row['harga_beli']) - (float) ($row['diskon'] ?? 0));
            $diskon = (float) ($validated['diskon'] ?? 0);
            if ($diskon > $subtotal) {
                throw ValidationException::withMessages(['diskon' => 'Diskon transaksi tidak boleh melebihi subtotal.']);
            }

            $pembelian = Pembelian::create([
                'nomor_pembelian' => $this->generatePurchaseNumber(),
                'supplier_id' => $validated['supplier_id'],
                'nomor_faktur_supplier' => $validated['nomor_faktur_supplier'] ?? null,
                'tanggal_pembelian' => $validated['tanggal_pembelian'],
                'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'] ?? null,
                'status' => Pembelian::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'biaya_lain' => $validated['biaya_lain'] ?? 0,
                'total' => $subtotal - $diskon + (float) ($validated['biaya_lain'] ?? 0),
                'catatan' => $validated['catatan'] ?? null,
                'dibuat_oleh' => $request->user()->id,
            ]);

            $this->syncDetails($pembelian, $validated['detail']);

            return $pembelian;
        });

        return redirect()->route('purchases.show', $pembelian)->with('success', 'Draft pembelian berhasil dibuat.');
    }

    public function show(Pembelian $pembelian): View
    {
        $pembelian->load(['supplier', 'detail', 'dibuatOleh', 'disetujuiOleh']);

        return view('purchases.transactions.show', [
            'pembelian' => $pembelian,
            'navigation' => $this->navigation(),
        ]);
    }

    public function edit(Pembelian $pembelian): View
    {
        abort_unless($pembelian->status === Pembelian::STATUS_DRAFT, 403, 'Hanya draft pembelian yang dapat diubah.');
        $pembelian->load('detail');

        return view('purchases.transactions.form', [
            'pembelian' => $pembelian,
            'suppliers' => Supplier::query()->where('aktif', true)->orWhereKey($pembelian->supplier_id)->orderBy('nama')->get(),
            'productOptions' => $this->productOptions($pembelian),
            'formTitle' => 'Edit Pembelian',
            'formAction' => route('purchases.update', $pembelian),
            'formMethod' => 'PUT',
            'navigation' => $this->navigation(),
        ]);
    }

    public function update(Request $request, Pembelian $pembelian)
    {
        abort_unless($pembelian->status === Pembelian::STATUS_DRAFT, 403, 'Hanya draft pembelian yang dapat diubah.');
        $validated = $this->validatePurchase($request, $pembelian);

        DB::transaction(function () use ($validated, $pembelian) {
            $subtotal = collect($validated['detail'])->sum(fn ($row) => ((float) $row['jumlah'] * (float) $row['harga_beli']) - (float) ($row['diskon'] ?? 0));
            $diskon = (float) ($validated['diskon'] ?? 0);
            if ($diskon > $subtotal) {
                throw ValidationException::withMessages(['diskon' => 'Diskon transaksi tidak boleh melebihi subtotal.']);
            }

            $pembelian->update([
                'supplier_id' => $validated['supplier_id'],
                'nomor_faktur_supplier' => $validated['nomor_faktur_supplier'] ?? null,
                'tanggal_pembelian' => $validated['tanggal_pembelian'],
                'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'] ?? null,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'biaya_lain' => $validated['biaya_lain'] ?? 0,
                'total' => $subtotal - $diskon + (float) ($validated['biaya_lain'] ?? 0),
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $pembelian->detail()->delete();
            $this->syncDetails($pembelian, $validated['detail']);
        });

        return redirect()->route('purchases.show', $pembelian)->with('success', 'Draft pembelian berhasil diperbarui.');
    }

    public function approve(Request $request, Pembelian $pembelian)
    {
        abort_unless($pembelian->status === Pembelian::STATUS_DRAFT, 403, 'Hanya draft pembelian yang dapat disetujui.');
        abort_if(! $pembelian->detail()->exists(), 422, 'Pembelian harus memiliki minimal satu produk.');

        $pembelian->update([
            'status' => Pembelian::STATUS_DISETUJUI,
            'disetujui_oleh' => $request->user()->id,
            'disetujui_pada' => now(),
        ]);

        return redirect()->route('purchases.show', $pembelian)->with('success', 'Pembelian berhasil disetujui. Stok belum berubah sampai barang diterima.');
    }

    public function destroy(Pembelian $pembelian)
    {
        abort_unless($pembelian->status === Pembelian::STATUS_DRAFT, 403, 'Hanya draft pembelian yang dapat dihapus.');
        abort_if($pembelian->penerimaan()->exists() || $pembelian->pembayaran()->exists(), 422, 'Pembelian sudah memiliki penerimaan atau pembayaran.');

        $pembelian->delete();

        return redirect()->route('purchases.index')->with('success', 'Draft pembelian berhasil dihapus.');
    }

    private function validatePurchase(Request $request, ?Pembelian $pembelian = null): array
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'ulid', 'exists:supplier,id'],
            'nomor_faktur_supplier' => ['nullable', 'string', 'max:100'],
            'tanggal_pembelian' => ['required', 'date'],
            'tanggal_jatuh_tempo' => ['nullable', 'date', 'after_or_equal:tanggal_pembelian'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'biaya_lain' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.produk_id' => ['required', 'ulid', 'exists:produk,id'],
            'detail.*.satuan_produk_id' => ['required', 'ulid', 'exists:satuan_produk,id'],
            'detail.*.jumlah' => ['required', 'numeric', 'gt:0'],
            'detail.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'detail.*.diskon' => ['nullable', 'numeric', 'min:0'],
            'detail.*.catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'tanggal_pembelian.required' => 'Tanggal pembelian wajib diisi.',
            'detail.required' => 'Tambahkan minimal satu produk pembelian.',
            'detail.min' => 'Tambahkan minimal satu produk pembelian.',
            'detail.*.produk_id.required' => 'Produk pada setiap baris wajib dipilih.',
            'detail.*.satuan_produk_id.required' => 'Satuan pembelian wajib dipilih.',
            'detail.*.jumlah.gt' => 'Jumlah pembelian harus lebih dari nol.',
        ]);

        foreach ($validated['detail'] as $index => $row) {
            $unit = \App\Models\SatuanProduk::query()
                ->whereKey($row['satuan_produk_id'])
                ->where('produk_id', $row['produk_id'])
                ->where('aktif', true)
                ->whereNull('deleted_at')
                ->with('satuan')
                ->first();

            if (! $unit) {
                throw ValidationException::withMessages(["detail.{$index}.satuan_produk_id" => 'Satuan tidak sesuai dengan produk yang dipilih atau sudah tidak aktif.']);
            }

            $gross = (float) $row['jumlah'] * (float) $row['harga_beli'];
            if ((float) ($row['diskon'] ?? 0) > $gross) {
                throw ValidationException::withMessages(["detail.{$index}.diskon" => 'Diskon baris tidak boleh melebihi nilai barang.']);
            }
        }

        return $validated;
    }

    private function syncDetails(Pembelian $pembelian, array $rows): void
    {
        foreach ($rows as $row) {
            $produk = Produk::query()->findOrFail($row['produk_id']);
            $unit = \App\Models\SatuanProduk::query()->with('satuan')->findOrFail($row['satuan_produk_id']);
            $jumlah = (float) $row['jumlah'];
            $konversi = (float) $unit->konversi_ke_satuan_dasar;
            $subtotal = ($jumlah * (float) $row['harga_beli']) - (float) ($row['diskon'] ?? 0);

            $pembelian->detail()->create([
                'produk_id' => $produk->id,
                'satuan_produk_id' => $unit->id,
                'nama_produk' => $produk->nama,
                'kode_sku' => $produk->kode_sku,
                'nama_satuan' => $unit->satuan?->nama ?? '-',
                'konversi_ke_satuan_dasar' => $konversi,
                'jumlah' => $jumlah,
                'jumlah_dalam_satuan_dasar' => $jumlah * $konversi,
                'harga_beli' => $row['harga_beli'],
                'diskon' => $row['diskon'] ?? 0,
                'subtotal' => $subtotal,
                'catatan' => $row['catatan'] ?? null,
            ]);
        }
    }

    public function productSearch(Request $request)
    {
        $search = trim($request->string('q')->value());
        $selectedId = $request->string('id')->value();

        $products = Produk::query()
            ->where('aktif', true)
            ->whereNull('deleted_at')
            ->when($selectedId !== '', fn (Builder $query) => $query->whereKey($selectedId))
            ->when($selectedId === '' && $search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_sku', 'like', "%{$search}%")
                    ->orWhereHas('satuanProduk', fn (Builder $unit) => $unit->where('barcode', 'like', "%{$search}%"));
            }))
            ->with(['satuanProduk' => fn ($query) => $query->where('aktif', true)->whereNull('deleted_at')->with('satuan')])
            ->orderBy('nama')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $products->map(fn ($product) => [
                'id' => $product->id,
                'text' => $product->nama . ($product->kode_sku ? ' · ' . $product->kode_sku : ''),
                'units' => $product->satuanProduk->map(fn ($unit) => [
                    'id' => $unit->id,
                    'nama' => $unit->satuan?->nama ?? '-',
                    'konversi' => (float) $unit->konversi_ke_satuan_dasar,
                ])->values(),
            ])->values(),
        ]);
    }

    private function productOptions(?Pembelian $pembelian = null): array
    {
        $products = Produk::query()
            ->where('aktif', true)
            ->with(['satuanProduk' => fn ($query) => $query->where('aktif', true)->whereNull('deleted_at')->with('satuan')])
            ->orderBy('nama')
            ->get();

        $options = $products->map(fn ($product) => [
            'id' => $product->id,
            'nama' => $product->nama,
            'sku' => $product->kode_sku,
            'units' => $product->satuanProduk->map(fn ($unit) => [
                'id' => $unit->id,
                'nama' => $unit->satuan?->nama ?? '-',
                'konversi' => (float) $unit->konversi_ke_satuan_dasar,
            ])->values(),
        ])->values()->all();

        if ($pembelian) {
            foreach ($pembelian->detail as $detail) {
                if (collect($options)->contains(fn ($item) => $item['id'] === $detail->produk_id)) continue;
                $options[] = [
                    'id' => $detail->produk_id,
                    'nama' => $detail->nama_produk,
                    'sku' => $detail->kode_sku,
                    'units' => [[
                        'id' => $detail->satuan_produk_id,
                        'nama' => $detail->nama_satuan,
                        'konversi' => (float) $detail->konversi_ke_satuan_dasar,
                    ]],
                ];
            }
        }

        return $options;
    }

    private function generatePurchaseNumber(): string
    {
        do {
            $number = 'PB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Pembelian::query()->where('nomor_pembelian', $number)->exists());

        return $number;
    }

    private function navigation(): array
    {
        return [
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => false],
            ['label' => 'Transaksi', 'children' => [
                ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
            ]],
            ['label' => 'Manajemen Toko', 'active' => true, 'children' => [
                ['label' => 'Master', 'href' => route('products.index')],
                ['label' => 'Pembelian', 'href' => route('purchases.index')],
                ['label' => 'Persediaan', 'disabled' => true, 'status' => 'Segera'],
            ]],
            ['label' => 'Laporan', 'children' => [
                ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                ['label' => 'Pembayaran', 'href' => '#ringkasan'],
            ]],
        ];
    }
}
