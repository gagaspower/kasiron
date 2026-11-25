<?php

namespace App\Http\Controllers;

use App\Models\MutasiStok;
use App\Models\Pembelian;
use App\Models\PenerimaanPembelian;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenerimaanPembelianController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->value());
        $tanggalMulai = $request->string('tanggal_mulai')->value();
        $tanggalSelesai = $request->string('tanggal_selesai')->value();
        $supplierId = $request->string('supplier_id')->value();

        $pembelians = Pembelian::query()
            ->with(['supplier', 'detail.penerimaanDetail' => fn ($query) => $query->whereHas('penerimaan', fn ($receipt) => $receipt->where('status', PenerimaanPembelian::STATUS_DITERIMA))])
            ->whereIn('status', [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('nomor_pembelian', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('nama', 'like', "%{$search}%"));
            }))
            ->latest('tanggal_pembelian')
            ->paginate(10, ['*'], 'menunggu_page')
            ->withQueryString();

        $pembelians->through(function (Pembelian $pembelian) {
            $pembelian->detail->each(function ($detail) {
                $detail->jumlah_sudah_diterima = (float) $detail->penerimaanDetail->sum('jumlah_diterima');
                $detail->jumlah_sisa = max(0, (float) $detail->jumlah - $detail->jumlah_sudah_diterima);
            });
            $pembelian->total_sisa_item = $pembelian->detail->filter(fn ($detail) => $detail->jumlah_sisa > 0)->count();
            $pembelian->total_sisa_qty = $pembelian->detail->sum('jumlah_sisa');

            return $pembelian;
        });

        $riwayat = PenerimaanPembelian::query()
            ->with(['pembelian.supplier', 'dibuatOleh', 'diterimaOleh'])
            ->when($tanggalMulai !== '', fn (Builder $query) => $query->whereDate('tanggal_penerimaan', '>=', $tanggalMulai))
            ->when($tanggalSelesai !== '', fn (Builder $query) => $query->whereDate('tanggal_penerimaan', '<=', $tanggalSelesai))
            ->when($supplierId !== '', fn (Builder $query) => $query->whereHas('pembelian', fn (Builder $purchase) => $purchase->where('supplier_id', $supplierId)))
            ->latest('tanggal_penerimaan')
            ->paginate(10, ['*'], 'riwayat_page')
            ->withQueryString();

        return view('purchases.receipts', [
            'pembelians' => $pembelians,
            'riwayat' => $riwayat,
            'search' => $search,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'supplierId' => $supplierId,
            'suppliers' => Supplier::query()->orderBy('nama')->get(['id', 'nama']),
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function create(Pembelian $pembelian): View
    {
        abort_unless(in_array($pembelian->status, [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA], true), 403, 'Pembelian belum dapat diterima.');

        $pembelian->load(['supplier', 'detail.penerimaanDetail' => fn ($query) => $query->whereHas('penerimaan', fn ($receipt) => $receipt->where('status', PenerimaanPembelian::STATUS_DITERIMA))]);

        $pembelian->detail->each(function ($detail) {
            $detail->jumlah_sudah_diterima = (float) $detail->penerimaanDetail->sum('jumlah_diterima');
            $detail->jumlah_sisa = max(0, (float) $detail->jumlah - $detail->jumlah_sudah_diterima);
        });

        abort_if($pembelian->detail->every(fn ($detail) => $detail->jumlah_sisa <= 0), 422, 'Semua barang pada pembelian ini sudah diterima.');

        return view('purchases.receipts-create', [
            'pembelian' => $pembelian,
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function store(Request $request, Pembelian $pembelian)
    {
        abort_unless(in_array($pembelian->status, [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA], true), 403, 'Pembelian belum dapat diterima.');

        $validated = $request->validate([
            'tanggal_penerimaan' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'proses' => ['required', 'in:draft,terima'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.jumlah_diterima' => ['nullable', 'numeric', 'min:0'],
            'detail.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $receipt = DB::transaction(function () use ($validated, $request, $pembelian) {
            $lockedPurchase = Pembelian::query()->lockForUpdate()->with('detail')->findOrFail($pembelian->id);
            abort_unless(in_array($lockedPurchase->status, [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA], true), 403, 'Status pembelian sudah berubah.');

            $rows = collect($validated['detail'] ?? [])->filter(fn ($row) => (float) ($row['jumlah_diterima'] ?? 0) > 0);
            if ($rows->isEmpty()) {
                throw ValidationException::withMessages(['detail' => 'Isi minimal satu jumlah barang yang diterima.']);
            }

            $receipt = PenerimaanPembelian::create([
                'nomor_penerimaan' => $this->generateReceiptNumber(),
                'pembelian_id' => $lockedPurchase->id,
                'tanggal_penerimaan' => $validated['tanggal_penerimaan'],
                'status' => PenerimaanPembelian::STATUS_DRAFT,
                'catatan' => $validated['catatan'] ?? null,
                'dibuat_oleh' => $request->user()->id,
            ]);

            foreach ($rows as $detailId => $row) {
                $purchaseDetail = $lockedPurchase->detail->firstWhere('id', $detailId);
                if (! $purchaseDetail) {
                    throw ValidationException::withMessages(['detail' => 'Rincian produk tidak sesuai dengan transaksi pembelian.']);
                }

                $alreadyReceived = (float) DB::table('penerimaan_pembelian_detail')
                    ->join('penerimaan_pembelian', 'penerimaan_pembelian.id', '=', 'penerimaan_pembelian_detail.penerimaan_pembelian_id')
                    ->where('penerimaan_pembelian_detail.pembelian_detail_id', $purchaseDetail->id)
                    ->where('penerimaan_pembelian.status', PenerimaanPembelian::STATUS_DITERIMA)
                    ->sum('penerimaan_pembelian_detail.jumlah_diterima');

                $quantity = (float) $row['jumlah_diterima'];
                $remaining = (float) $purchaseDetail->jumlah - $alreadyReceived;
                if ($quantity > $remaining + 0.000001) {
                    throw ValidationException::withMessages(["detail.{$detailId}.jumlah_diterima" => "Jumlah melebihi sisa pesanan ({$remaining})."]);
                }

                $receipt->detail()->create([
                    'pembelian_detail_id' => $purchaseDetail->id,
                    'jumlah_diterima' => $quantity,
                    'jumlah_diterima_dalam_satuan_dasar' => $quantity * (float) $purchaseDetail->konversi_ke_satuan_dasar,
                    'catatan' => $row['catatan'] ?? null,
                ]);
            }

            if ($validated['proses'] === 'terima') {
                $this->finalizeReceipt($receipt->load('detail.pembelianDetail'), $lockedPurchase, $request->user()->id);
            }

            return $receipt;
        });

        $message = $validated['proses'] === 'terima'
            ? 'Penerimaan berhasil disimpan dan stok telah diperbarui.'
            : 'Draft penerimaan berhasil disimpan. Anda dapat mengonfirmasi penerimaan nanti.';

        return redirect()->route('purchases.receipts.show', $receipt)->with('success', $message);
    }

    public function edit(PenerimaanPembelian $penerimaan): View
    {
        abort_unless(in_array($penerimaan->status, [PenerimaanPembelian::STATUS_DRAFT, PenerimaanPembelian::STATUS_DITERIMA], true), 422, 'Penerimaan ini tidak dapat direvisi.');

        $penerimaan->load(['pembelian.supplier', 'detail.pembelianDetail']);
        $purchase = $penerimaan->pembelian;
        $purchase->load(['detail.penerimaanDetail' => fn ($query) => $query->whereHas('penerimaan', fn ($receipt) => $receipt->where('status', PenerimaanPembelian::STATUS_DITERIMA))]);

        $penerimaan->detail->each(function ($row) {
            $row->pembelianDetail->jumlah_sudah_diterima_lain = 0;
        });

        $purchase->detail->each(function ($detail) use ($penerimaan) {
            $detail->jumlah_sudah_diterima_lain = (float) $detail->penerimaanDetail
                ->where('penerimaan_pembelian_id', '!=', $penerimaan->id)
                ->sum('jumlah_diterima');
            $detail->penerimaan_sekarang = (float) $penerimaan->detail
                ->where('pembelian_detail_id', $detail->id)
                ->sum('jumlah_diterima');
            $detail->jumlah_sisa_revisi = max(0, (float) $detail->jumlah - $detail->jumlah_sudah_diterima_lain);
        });

        return view('purchases.receipts-edit', [
            'penerimaan' => $penerimaan,
            'pembelian' => $purchase,
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function update(Request $request, PenerimaanPembelian $penerimaan)
    {
        $validated = $request->validate([
            'tanggal_penerimaan' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'alasan_revisi' => ['required', 'string', 'max:1000'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.jumlah_diterima' => ['nullable', 'numeric', 'min:0'],
            'detail.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $penerimaan, $validated) {
            $receipt = PenerimaanPembelian::query()->lockForUpdate()->with('detail.pembelianDetail')->findOrFail($penerimaan->id);
            abort_unless(in_array($receipt->status, [PenerimaanPembelian::STATUS_DRAFT, PenerimaanPembelian::STATUS_DITERIMA], true), 422, 'Penerimaan ini tidak dapat direvisi.');
            $purchase = Pembelian::query()->lockForUpdate()->with('detail')->findOrFail($receipt->pembelian_id);
            $isConfirmed = $receipt->status === PenerimaanPembelian::STATUS_DITERIMA;

            $rows = collect($validated['detail'])->filter(fn ($row) => (float) ($row['jumlah_diterima'] ?? 0) > 0);
            if ($rows->isEmpty()) {
                throw ValidationException::withMessages(['detail' => 'Isi minimal satu jumlah barang yang diterima.']);
            }

            $newRows = [];
            foreach ($rows as $detailId => $row) {
                $purchaseDetail = $purchase->detail->firstWhere('id', $detailId);
                if (! $purchaseDetail) {
                    throw ValidationException::withMessages(['detail' => 'Rincian produk tidak sesuai dengan pembelian.']);
                }

                $receivedElsewhere = (float) DB::table('penerimaan_pembelian_detail')
                    ->join('penerimaan_pembelian', 'penerimaan_pembelian.id', '=', 'penerimaan_pembelian_detail.penerimaan_pembelian_id')
                    ->where('penerimaan_pembelian_detail.pembelian_detail_id', $detailId)
                    ->where('penerimaan_pembelian.status', PenerimaanPembelian::STATUS_DITERIMA)
                    ->where('penerimaan_pembelian.id', '!=', $receipt->id)
                    ->sum('penerimaan_pembelian_detail.jumlah_diterima');

                $quantity = (float) $row['jumlah_diterima'];
                if ($quantity > (float) $purchaseDetail->jumlah - $receivedElsewhere + 0.000001) {
                    throw ValidationException::withMessages(["detail.{$detailId}.jumlah_diterima" => "Jumlah melebihi sisa pesanan setelah memperhitungkan penerimaan lainnya."]);
                }

                $newRows[$detailId] = [
                    'pembelian_detail' => $purchaseDetail,
                    'jumlah' => $quantity,
                    'jumlah_dasar' => $quantity * (float) $purchaseDetail->konversi_ke_satuan_dasar,
                    'catatan' => $row['catatan'] ?? null,
                ];
            }

            if ($isConfirmed) {
                $oldByProduct = $receipt->detail->groupBy(fn ($row) => $row->pembelianDetail->produk_id)
                    ->map(fn ($items) => $items->sum('jumlah_diterima_dalam_satuan_dasar'));
                $newByProduct = collect($newRows)->groupBy(fn ($row) => $row['pembelian_detail']->produk_id)
                    ->map(fn ($items) => $items->sum('jumlah_dasar'));

                foreach ($oldByProduct->keys()->merge($newByProduct->keys())->unique() as $productId) {
                    $product = Produk::withTrashed()->lockForUpdate()->findOrFail($productId);
                    if (! $product->kelola_stok) {
                        continue;
                    }

                    $oldQty = (float) ($oldByProduct[$productId] ?? 0);
                    $newQty = (float) ($newByProduct[$productId] ?? 0);
                    $delta = $newQty - $oldQty;
                    if (abs($delta) < 0.000001) {
                        continue;
                    }

                    $before = (float) $product->jumlah_stok;
                    $after = $before + $delta;
                    if ($after < -0.000001) {
                        throw ValidationException::withMessages(['stok' => "Revisi tidak dapat dilakukan karena stok {$product->nama} sudah terpakai."]);
                    }
                    $product->update(['jumlah_stok' => max(0, $after)]);
                    MutasiStok::create([
                        'produk_id' => $product->id,
                        'jenis' => $delta > 0 ? MutasiStok::JENIS_MASUK : MutasiStok::JENIS_KELUAR,
                        'jumlah' => abs($delta),
                        'stok_sebelum' => $before,
                        'stok_sesudah' => max(0, $after),
                        'referensi_tipe' => 'revisi_penerimaan_pembelian',
                        'referensi_id' => $receipt->id,
                        'catatan' => 'Revisi ' . $receipt->nomor_penerimaan . ': ' . $validated['alasan_revisi'],
                    ]);
                }
            }

            $receipt->detail()->delete();
            foreach ($newRows as $detailId => $row) {
                $receipt->detail()->create([
                    'pembelian_detail_id' => $detailId,
                    'jumlah_diterima' => $row['jumlah'],
                    'jumlah_diterima_dalam_satuan_dasar' => $row['jumlah_dasar'],
                    'catatan' => $row['catatan'],
                ]);
            }

            $receipt->update([
                'tanggal_penerimaan' => $validated['tanggal_penerimaan'],
                'catatan' => $validated['catatan'] ?? null,
            ]);
            $this->refreshPurchaseStatus($purchase);
        });

        return redirect()->route('purchases.receipts.show', $penerimaan)->with('success', 'Penerimaan berhasil direvisi dan stok telah disesuaikan.');
    }

    public function cancel(Request $request, PenerimaanPembelian $penerimaan)
    {
        $validated = $request->validate([
            'alasan_pembatalan' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $penerimaan, $validated) {
            $receipt = PenerimaanPembelian::query()->lockForUpdate()->with('detail.pembelianDetail')->findOrFail($penerimaan->id);
            abort_unless(in_array($receipt->status, [PenerimaanPembelian::STATUS_DRAFT, PenerimaanPembelian::STATUS_DITERIMA], true), 422, 'Penerimaan ini sudah dibatalkan.');

            $purchase = Pembelian::query()->lockForUpdate()->with('detail')->findOrFail($receipt->pembelian_id);
            if ($receipt->status === PenerimaanPembelian::STATUS_DITERIMA) {
                $byProduct = $receipt->detail->groupBy(fn ($row) => $row->pembelianDetail->produk_id)
                    ->map(fn ($items) => $items->sum('jumlah_diterima_dalam_satuan_dasar'));

                foreach ($byProduct as $productId => $quantity) {
                    $product = Produk::withTrashed()->lockForUpdate()->findOrFail($productId);
                    if (! $product->kelola_stok) {
                        continue;
                    }

                    $before = (float) $product->jumlah_stok;
                    $after = $before - (float) $quantity;
                    if ($after < -0.000001) {
                        throw ValidationException::withMessages(['pembatalan' => "Penerimaan tidak dapat dibatalkan karena stok {$product->nama} sudah terpakai. Lakukan penyesuaian stok terlebih dahulu."]);
                    }
                    $product->update(['jumlah_stok' => max(0, $after)]);
                    MutasiStok::create([
                        'produk_id' => $product->id,
                        'jenis' => MutasiStok::JENIS_KELUAR,
                        'jumlah' => $quantity,
                        'stok_sebelum' => $before,
                        'stok_sesudah' => max(0, $after),
                        'referensi_tipe' => 'pembatalan_penerimaan_pembelian',
                        'referensi_id' => $receipt->id,
                        'catatan' => 'Pembatalan ' . $receipt->nomor_penerimaan . ': ' . $validated['alasan_pembatalan'],
                    ]);
                }
            }

            $receipt->update(['status' => 'dibatalkan']);
            $this->refreshPurchaseStatus($purchase);
        });

        return redirect()->route('purchases.receipts.show', $penerimaan)->with('success', 'Penerimaan berhasil dibatalkan.');
    }

    private function refreshPurchaseStatus(Pembelian $purchase): void
    {
        $allReceived = true;
        $hasReceived = false;
        foreach ($purchase->detail as $purchaseDetail) {
            $received = (float) DB::table('penerimaan_pembelian_detail')
                ->join('penerimaan_pembelian', 'penerimaan_pembelian.id', '=', 'penerimaan_pembelian_detail.penerimaan_pembelian_id')
                ->where('penerimaan_pembelian_detail.pembelian_detail_id', $purchaseDetail->id)
                ->where('penerimaan_pembelian.status', PenerimaanPembelian::STATUS_DITERIMA)
                ->sum('penerimaan_pembelian_detail.jumlah_diterima');
            $hasReceived = $hasReceived || $received > 0;
            if ($received + 0.000001 < (float) $purchaseDetail->jumlah) {
                $allReceived = false;
            }
        }

        $purchase->update([
            'status' => $allReceived ? Pembelian::STATUS_DITERIMA : ($hasReceived ? Pembelian::STATUS_SEBAGIAN_DITERIMA : Pembelian::STATUS_DISETUJUI),
        ]);
    }

    public function show(PenerimaanPembelian $penerimaan): View
    {
        $penerimaan->load(['pembelian.supplier', 'detail.pembelianDetail', 'dibuatOleh', 'diterimaOleh']);

        return view('purchases.receipts-show', [
            'penerimaan' => $penerimaan,
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function confirm(Request $request, PenerimaanPembelian $penerimaan)
    {
        DB::transaction(function () use ($request, $penerimaan) {
            $receipt = PenerimaanPembelian::query()->lockForUpdate()->with('detail.pembelianDetail')->findOrFail($penerimaan->id);
            $purchase = Pembelian::query()->lockForUpdate()->with('detail')->findOrFail($receipt->pembelian_id);
            $this->finalizeReceipt($receipt, $purchase, $request->user()->id);
        });

        return redirect()->route('purchases.receipts.show', $penerimaan)->with('success', 'Penerimaan berhasil dikonfirmasi dan stok telah diperbarui.');
    }

    private function finalizeReceipt(PenerimaanPembelian $receipt, Pembelian $purchase, $userId): void
    {
        abort_unless($receipt->status === PenerimaanPembelian::STATUS_DRAFT, 422, 'Penerimaan ini sudah diproses.');
        abort_unless(in_array($purchase->status, [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA], true), 422, 'Status pembelian tidak memungkinkan penerimaan.');

        foreach ($receipt->detail as $receiptDetail) {
            $purchaseDetail = $purchase->detail->firstWhere('id', $receiptDetail->pembelian_detail_id);
            abort_unless($purchaseDetail, 422, 'Rincian pembelian tidak ditemukan.');

            $alreadyReceived = (float) DB::table('penerimaan_pembelian_detail')
                ->join('penerimaan_pembelian', 'penerimaan_pembelian.id', '=', 'penerimaan_pembelian_detail.penerimaan_pembelian_id')
                ->where('penerimaan_pembelian_detail.pembelian_detail_id', $purchaseDetail->id)
                ->where('penerimaan_pembelian.status', PenerimaanPembelian::STATUS_DITERIMA)
                ->sum('penerimaan_pembelian_detail.jumlah_diterima');

            if ((float) $receiptDetail->jumlah_diterima > (float) $purchaseDetail->jumlah - $alreadyReceived + 0.000001) {
                throw ValidationException::withMessages(['penerimaan' => "Jumlah diterima untuk {$purchaseDetail->nama_produk} melebihi sisa pesanan."]);
            }

            $product = Produk::query()->lockForUpdate()->findOrFail($purchaseDetail->produk_id);
            if ($product->kelola_stok) {
                $before = (float) $product->jumlah_stok;
                $quantity = (float) $receiptDetail->jumlah_diterima_dalam_satuan_dasar;
                $after = $before + $quantity;
                $product->update(['jumlah_stok' => $after]);

                MutasiStok::create([
                    'produk_id' => $product->id,
                    'jenis' => MutasiStok::JENIS_MASUK,
                    'jumlah' => $quantity,
                    'stok_sebelum' => $before,
                    'stok_sesudah' => $after,
                    'referensi_tipe' => 'penerimaan_pembelian',
                    'referensi_id' => $receipt->id,
                    'catatan' => 'Penerimaan ' . $receipt->nomor_penerimaan,
                ]);
            }
        }

        $receipt->update([
            'status' => PenerimaanPembelian::STATUS_DITERIMA,
            'diterima_oleh' => $userId,
            'diterima_pada' => now(),
        ]);

        $allReceived = true;
        foreach ($purchase->detail as $purchaseDetail) {
            $received = (float) DB::table('penerimaan_pembelian_detail')
                ->join('penerimaan_pembelian', 'penerimaan_pembelian.id', '=', 'penerimaan_pembelian_detail.penerimaan_pembelian_id')
                ->where('penerimaan_pembelian_detail.pembelian_detail_id', $purchaseDetail->id)
                ->where('penerimaan_pembelian.status', PenerimaanPembelian::STATUS_DITERIMA)
                ->sum('penerimaan_pembelian_detail.jumlah_diterima');

            if ($received + 0.000001 < (float) $purchaseDetail->jumlah) {
                $allReceived = false;
                break;
            }
        }

        $this->refreshPurchaseStatus($purchase);
    }

    private function generateReceiptNumber(): string
    {
        do {
            $number = 'PBM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (PenerimaanPembelian::query()->where('nomor_penerimaan', $number)->exists());

        return $number;
    }
}
