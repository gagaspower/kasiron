<?php

namespace App\Http\Controllers;

use App\Models\MutasiStok;
use App\Models\Pembelian;
use App\Models\PenerimaanPembelian;
use App\Models\Produk;
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

        $pembelians = Pembelian::query()
            ->with(['supplier', 'detail.penerimaanDetail' => fn ($query) => $query->whereHas('penerimaan', fn ($receipt) => $receipt->where('status', PenerimaanPembelian::STATUS_DITERIMA))])
            ->whereIn('status', [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('nomor_pembelian', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('nama', 'like', "%{$search}%"));
            }))
            ->latest('tanggal_pembelian')
            ->get()
            ->map(function (Pembelian $pembelian) {
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
            ->latest('tanggal_penerimaan')
            ->paginate(10, ['*'], 'riwayat_page')
            ->withQueryString();

        return view('purchases.receipts', [
            'pembelians' => $pembelians,
            'riwayat' => $riwayat,
            'search' => $search,
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

            return $receipt;
        });

        return redirect()->route('purchases.receipts.show', $receipt)->with('success', 'Draft penerimaan berhasil disimpan. Periksa kembali sebelum mengonfirmasi.');
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
            abort_unless($receipt->status === PenerimaanPembelian::STATUS_DRAFT, 422, 'Penerimaan ini sudah diproses.');

            $purchase = Pembelian::query()->lockForUpdate()->with('detail')->findOrFail($receipt->pembelian_id);
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
                'diterima_oleh' => $request->user()->id,
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

            $purchase->update([
                'status' => $allReceived ? Pembelian::STATUS_DITERIMA : Pembelian::STATUS_SEBAGIAN_DITERIMA,
            ]);
        });

        return redirect()->route('purchases.receipts.show', $penerimaan)->with('success', 'Penerimaan berhasil dikonfirmasi dan stok telah diperbarui.');
    }

    private function generateReceiptNumber(): string
    {
        do {
            $number = 'PBM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (PenerimaanPembelian::query()->where('nomor_penerimaan', $number)->exists());

        return $number;
    }
}
