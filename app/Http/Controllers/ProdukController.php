<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\MutasiStok;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\SatuanProduk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $trash = $request->string('status')->value() === 'trash';
        $search = trim($request->string('search')->value());

        $products = Produk::query()
            ->with(['kategori', 'satuanDasar', 'satuanProduk.satuan'])
            ->when($trash, fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_sku', 'like', "%{$search}%")
                    ->orWhereHas('satuanProduk', fn (Builder $unitQuery) => $unitQuery->where('barcode', 'like', "%{$search}%"));
            }))
            ->orderBy('nama')
            ->paginate(10)
            ->withPath(route('products-master.index'))
            ->withQueryString();

        $data = compact('products', 'search', 'trash') + ['navigation' => $this->navigation()];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('products.master.partials.results', $data)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('products.master.index', $data);
    }

    public function create(): View
    {
        return view('products.master.form', $this->formData() + [
            'product' => new Produk(['kelola_stok' => true, 'aktif' => true]),
            'saleUnits' => collect(),
            'formTitle' => 'Tambah Produk',
            'formAction' => route('products-master.store'),
            'formMethod' => 'POST',
            'navigation' => $this->navigation(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        DB::transaction(function () use ($validated, $request) {
            $product = Produk::create([
                'kategori_id' => $validated['kategori_id'] ?? null,
                'kode_sku' => $validated['kode_sku'] ?? null,
                'nama' => $validated['nama'],
                'satuan_dasar_id' => $validated['satuan_dasar_id'],
                'jumlah_stok' => $validated['jumlah_stok'] ?? 0,
                'stok_minimum' => $validated['stok_minimum'] ?? 0,
                'kelola_stok' => $request->boolean('kelola_stok'),
                'aktif' => $request->boolean('aktif'),
            ]);

            $this->syncSaleUnits($product, $validated['satuan_penjualan']);

            $initialStock = (float) ($validated['jumlah_stok'] ?? 0);
            if ($product->kelola_stok && $initialStock > 0) {
                $product->mutasiStok()->create([
                    'jenis' => MutasiStok::JENIS_STOK_AWAL,
                    'jumlah' => $initialStock,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $initialStock,
                    'catatan' => 'Stok awal saat produk dibuat.',
                ]);
            }
        });

        return redirect()->route('products-master.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk): View
    {
        $produk->load(['satuanProduk' => fn ($query) => $query->withTrashed(), 'satuanProduk.satuan']);

        return view('products.master.form', $this->formData($produk) + [
            'product' => $produk,
            'saleUnits' => $produk->satuanProduk->whereNull('deleted_at')->values(),
            'formTitle' => 'Edit Produk',
            'formAction' => route('products-master.update', $produk),
            'formMethod' => 'PUT',
            'navigation' => $this->navigation(),
        ]);
    }

    public function update(Request $request, Produk $produk)
    {
        $validated = $this->validateProduct($request, $produk);

        DB::transaction(function () use ($validated, $request, $produk) {
            $oldStock = (float) $produk->jumlah_stok;
            $newStock = (float) ($validated['jumlah_stok'] ?? 0);

            $produk->update([
                'kategori_id' => $validated['kategori_id'] ?? null,
                'kode_sku' => $validated['kode_sku'] ?? null,
                'nama' => $validated['nama'],
                'satuan_dasar_id' => $validated['satuan_dasar_id'],
                'jumlah_stok' => $newStock,
                'stok_minimum' => $validated['stok_minimum'] ?? 0,
                'kelola_stok' => $request->boolean('kelola_stok'),
                'aktif' => $request->boolean('aktif'),
            ]);

            $this->syncSaleUnits($produk, $validated['satuan_penjualan']);

            if ($produk->kelola_stok && $oldStock !== $newStock) {
                $produk->mutasiStok()->create([
                    'jenis' => MutasiStok::JENIS_PENYESUAIAN,
                    'jumlah' => $newStock - $oldStock,
                    'stok_sebelum' => $oldStock,
                    'stok_sesudah' => $newStock,
                    'catatan' => 'Penyesuaian stok melalui form produk.',
                ]);
            }
        });

        return redirect()->route('products-master.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Produk $produk)
    {
        $produk->update(['aktif' => ! $produk->aktif]);
        return $this->respondAfterMutation($request, 'Status produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Produk $produk)
    {
        $produk->delete();
        return $this->respondAfterMutation($request, 'Produk dipindahkan ke sampah.');
    }

    public function restore(Request $request, string $id)
    {
        Produk::onlyTrashed()->findOrFail($id)->restore();
        return $this->respondAfterMutation($request, 'Produk berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, string $id)
    {
        $produk = Produk::onlyTrashed()->findOrFail($id);

        if ($produk->mutasiStok()->exists()) {
            return response()->json(['message' => 'Produk memiliki riwayat mutasi stok sehingga tidak dapat dihapus permanen.'], 422);
        }

        $produk->forceDelete();
        return $this->respondAfterMutation($request, 'Produk berhasil dihapus permanen.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['trash', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:produk,id'],
        ]);

        $products = Produk::withTrashed()->whereIn('id', $validated['ids'])->get();
        $count = 0;
        $blocked = 0;

        DB::transaction(function () use ($products, $validated, &$count, &$blocked) {
            foreach ($products as $product) {
                if ($validated['action'] === 'trash' && ! $product->trashed()) {
                    $product->delete();
                    $count++;
                } elseif ($validated['action'] === 'restore' && $product->trashed()) {
                    $product->restore();
                    $count++;
                } elseif ($validated['action'] === 'force_delete' && $product->trashed()) {
                    if ($product->mutasiStok()->exists()) {
                        $blocked++;
                        continue;
                    }
                    $product->forceDelete();
                    $count++;
                }
            }
        });

        $message = match ($validated['action']) {
            'trash' => "{$count} produk dipindahkan ke sampah.",
            'restore' => "{$count} produk dipulihkan.",
            default => "{$count} produk dihapus permanen.",
        };
        if ($blocked > 0) $message .= " {$blocked} produk tidak dapat dihapus karena memiliki riwayat mutasi stok.";

        return $this->respondAfterMutation($request, $message);
    }

    private function validateProduct(Request $request, ?Produk $produk = null): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode_sku' => ['nullable', 'string', 'max:255', Rule::unique('produk', 'kode_sku')->ignore($produk?->id)],
            'kategori_id' => ['nullable', Rule::exists('kategori', 'id')->whereNull('deleted_at')],
            'satuan_dasar_id' => ['required', Rule::exists('satuan', 'id')->whereNull('deleted_at')->where('aktif', true)],
            'jumlah_stok' => ['nullable', 'numeric', 'min:0'],
            'stok_minimum' => ['nullable', 'numeric', 'min:0'],
            'satuan_penjualan' => ['required', 'array', 'min:1'],
            'satuan_penjualan.*.id' => ['nullable', 'string'],
            'satuan_penjualan.*.satuan_id' => ['required', 'distinct', Rule::exists('satuan', 'id')->whereNull('deleted_at')->where('aktif', true)],
            'satuan_penjualan.*.konversi' => ['required', 'numeric', 'gt:0'],
            'satuan_penjualan.*.presisi' => ['required', 'integer', 'between:0,6'],
            'satuan_penjualan.*.harga_jual' => ['required', 'integer', 'min:0'],
            'satuan_penjualan.*.barcode' => ['nullable', 'string', 'max:255'],
            'satuan_penjualan.*.utama' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'satuan_dasar_id.required' => 'Satuan dasar wajib dipilih.',
            'satuan_penjualan.required' => 'Tambahkan minimal satu satuan penjualan.',
            'satuan_penjualan.*.satuan_id.distinct' => 'Satuan penjualan tidak boleh dipilih lebih dari sekali.',
        ]);

        $barcodes = collect($validated['satuan_penjualan'])
            ->pluck('barcode')
            ->filter()
            ->values();

        if ($barcodes->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'satuan_penjualan' => 'Barcode tidak boleh digunakan lebih dari satu kali dalam produk yang sama.',
            ]);
        }

        foreach ($validated['satuan_penjualan'] as $index => $row) {
            if (! empty($row['barcode'])) {
                $barcodeRule = Rule::unique('satuan_produk', 'barcode');
                $existingUnitId = $row['id'] ?? null;

                if (! $existingUnitId && $produk) {
                    $existingUnitId = $produk->satuanProduk()
                        ->withTrashed()
                        ->where('satuan_id', $row['satuan_id'])
                        ->value('id');
                }

                if ($existingUnitId) {
                    $barcodeRule->ignore($existingUnitId);
                }

                $barcodeValidator = validator(
                    ['barcode' => $row['barcode']],
                    ['barcode' => ['nullable', 'string', 'max:255', $barcodeRule]],
                    ['barcode.unique' => 'Barcode sudah digunakan oleh satuan produk lain.']
                );

                if ($barcodeValidator->fails()) {
                    throw ValidationException::withMessages([
                        "satuan_penjualan.{$index}.barcode" => $barcodeValidator->errors()->first('barcode'),
                    ]);
                }
            }
        }

        $rows = collect($validated['satuan_penjualan']);
        $baseRow = $rows->firstWhere('satuan_id', $validated['satuan_dasar_id']);

        if (! $baseRow) {
            throw ValidationException::withMessages(['satuan_penjualan' => 'Satuan dasar harus dimasukkan sebagai salah satu satuan penjualan.']);
        }

        if (abs((float) $baseRow['konversi'] - 1) > 0.000001) {
            throw ValidationException::withMessages(['satuan_penjualan' => 'Konversi satuan dasar harus bernilai 1.']);
        }

        if ($rows->filter(fn ($row) => ! empty($row['utama']))->count() !== 1) {
            throw ValidationException::withMessages(['satuan_penjualan' => 'Pilih tepat satu satuan utama untuk produk.']);
        }

        if ($produk) {
            foreach ($rows as $row) {
                if (! empty($row['id']) && ! $produk->satuanProduk()->withTrashed()->whereKey($row['id'])->exists()) {
                    throw ValidationException::withMessages(['satuan_penjualan' => 'Data satuan produk tidak valid.']);
                }
            }
        }

        return $validated;
    }

    private function syncSaleUnits(Produk $produk, array $rows): void
    {
        $existing = $produk->satuanProduk()->withTrashed()->get()->keyBy('id');
        $keptIds = [];

        foreach ($rows as $row) {
            $attributes = [
                'satuan_id' => $row['satuan_id'],
                'konversi_ke_satuan_dasar' => $row['konversi'],
                'presisi_kuantitas' => $row['presisi'],
                'harga_jual' => $row['harga_jual'],
                'barcode' => $row['barcode'] ?? null,
                'satuan_utama' => ! empty($row['utama']),
                'aktif' => true,
            ];

            $saleUnit = null;
            if (! empty($row['id'])) {
                $saleUnit = $existing->get($row['id']);
            }
            if (! $saleUnit) {
                $saleUnit = $existing->firstWhere('satuan_id', $row['satuan_id']);
            }

            if ($saleUnit) {
                if ($saleUnit->trashed()) $saleUnit->restore();
                $saleUnit->update($attributes);
            } else {
                $saleUnit = $produk->satuanProduk()->create($attributes);
            }

            $keptIds[] = $saleUnit->id;
        }

        $produk->satuanProduk()->whereNotIn('id', $keptIds)->get()->each(fn ($unit) => $unit->delete());
    }

    public function categoryOptions(Request $request)
    {
        $search = trim($request->string('q')->value());

        $categories = Kategori::query()
            ->where('aktif', true)
            ->when($search !== '', fn (Builder $query) => $query->where('nama', 'like', "%{$search}%"))
            ->orderBy('nama')
            ->limit(20)
            ->get(['id', 'nama']);

        return response()->json([
            'results' => $categories->map(fn (Kategori $category) => [
                'id' => $category->id,
                'text' => $category->nama,
            ])->values(),
        ]);
    }

    private function formData(?Produk $produk = null): array
    {
        $categoryId = old('kategori_id', $produk?->kategori_id);

        return [
            'selectedCategory' => $categoryId ? Kategori::query()->whereKey($categoryId)->first() : null,
            'units' => Satuan::query()->where('aktif', true)->orderBy('nama')->get(),
        ];
    }

    private function respondAfterMutation(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            $trash = $request->input('status', $request->query('status')) === 'trash';
            $search = trim((string) $request->input('search', $request->query('search', '')));
            $products = Produk::query()
                ->with(['kategori', 'satuanDasar', 'satuanProduk.satuan'])
                ->when($trash, fn (Builder $query) => $query->onlyTrashed())
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_sku', 'like', "%{$search}%")
                        ->orWhereHas('satuanProduk', fn (Builder $unitQuery) => $unitQuery->where('barcode', 'like', "%{$search}%"));
                }))
                ->orderBy('nama')
                ->paginate(10)
                ->withPath(route('products-master.index'))
                ->appends(array_filter(['status' => $trash ? 'trash' : null, 'search' => $search ?: null]));

            return response()->json([
                'message' => $message,
                'html' => view('products.master.partials.results', compact('products', 'search', 'trash'))->render(),
            ]);
        }

        return back()->with('success', $message);
    }

    private function navigation(): array
    {
        return [
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => false],
            ['label' => 'Transaksi', 'children' => [
                ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
            ]],
            ['label' => 'Manajemen Produk', 'href' => route('products.index'), 'active' => true],
            ['label' => 'Laporan', 'children' => [
                ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                ['label' => 'Pembayaran', 'href' => '#ringkasan'],
            ]],
        ];
    }
}
