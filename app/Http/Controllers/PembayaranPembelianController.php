<?php

namespace App\Http\Controllers;

use App\Models\KelebihanPembayaranSupplier;
use App\Models\PembayaranPembelian;
use App\Models\Pembelian;
use App\Models\PenyelesaianKelebihanSupplier;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PembayaranPembelianController extends Controller
{
    public function index(Request $request): View
    {
        $payments = PembayaranPembelian::query()
            ->with(['supplier', 'detail'])
            ->when($request->filled('supplier_id'), fn (Builder $q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('tanggal_mulai'), fn (Builder $q) => $q->whereDate('tanggal_pembayaran', '>=', $request->tanggal_mulai))
            ->when($request->filled('tanggal_selesai'), fn (Builder $q) => $q->whereDate('tanggal_pembayaran', '<=', $request->tanggal_selesai))
            ->latest('tanggal_pembayaran')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.payments.index', [
            'payments' => $payments,
            'suppliers' => Supplier::query()->orderBy('nama')->get(),
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function create(Request $request): View
    {
        $supplierId = $request->string('supplier_id')->value();
        $invoices = collect();

        if ($supplierId !== '') {
            $invoices = Pembelian::query()
                ->where('supplier_id', $supplierId)
                ->whereIn('status', [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA, Pembelian::STATUS_DITERIMA])
                ->withSum('pembayaranDetail as sudah_dibayar', 'jumlah_bayar')
                ->orderBy('tanggal_pembelian')
                ->get()
                ->filter(fn ($invoice) => round((float) $invoice->total - (float) $invoice->sudah_dibayar, 2) > 0);
        }

        return view('purchases.payments.create', [
            'suppliers' => Supplier::query()->where('aktif', true)->orderBy('nama')->get(),
            'selectedSupplier' => $supplierId,
            'invoices' => $invoices,
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'ulid', 'exists:supplier,id'],
            'tanggal_pembayaran' => ['required', 'date'],
            'total_bayar' => ['required', 'numeric', 'gt:0'],
            'metode_pembayaran' => ['required', 'in:tunai,transfer'],
            'referensi_pembayaran' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:3000'],
            'alokasi' => ['required', 'array', 'min:1'],
            'alokasi.*' => ['nullable', 'numeric', 'min:0'],
            'jadikan_titipan' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $supplier = Supplier::query()->whereKey($data['supplier_id'])->lockForUpdate()->firstOrFail();
            $allocations = collect($data['alokasi'])
                ->map(fn ($amount, $id) => ['pembelian_id' => $id, 'jumlah_bayar' => round((float) $amount, 2)])
                ->filter(fn ($row) => $row['jumlah_bayar'] > 0)
                ->values();

            if ($allocations->isEmpty()) {
                throw ValidationException::withMessages(['alokasi' => 'Isi nominal pembayaran minimal pada satu nota.']);
            }

            $invoiceIds = $allocations->pluck('pembelian_id');
            $invoices = Pembelian::query()->whereIn('id', $invoiceIds)->lockForUpdate()->get()->keyBy('id');
            if ($invoices->count() !== $invoiceIds->unique()->count()) {
                throw ValidationException::withMessages(['alokasi' => 'Ada nota pembelian yang tidak ditemukan.']);
            }

            $allocated = 0;
            foreach ($allocations as $row) {
                $invoice = $invoices->get($row['pembelian_id']);
                if ($invoice->supplier_id !== $supplier->id || in_array($invoice->status, [Pembelian::STATUS_DRAFT, Pembelian::STATUS_DIBATALKAN], true)) {
                    throw ValidationException::withMessages(['alokasi' => 'Nota harus berasal dari supplier yang dipilih dan bukan draft atau dibatalkan.']);
                }
                $paid = (float) $invoice->pembayaranDetail()->sum('jumlah_bayar');
                $remaining = round((float) $invoice->total - $paid, 2);
                if ($row['jumlah_bayar'] > $remaining) {
                    throw ValidationException::withMessages(['alokasi' => 'Alokasi pembayaran melebihi sisa tagihan nota ' . $invoice->nomor_pembelian . '.']);
                }
                $allocated += $row['jumlah_bayar'];
            }

            $total = round((float) $data['total_bayar'], 2);
            if ($allocated > $total) {
                throw ValidationException::withMessages(['total_bayar' => 'Total alokasi tidak boleh melebihi uang yang benar-benar dibayarkan.']);
            }

            $payment = PembayaranPembelian::create([
                'nomor_pembayaran' => 'BY-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'supplier_id' => $supplier->id,
                'tanggal_pembayaran' => $data['tanggal_pembayaran'],
                'total_bayar' => $total,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'referensi_pembayaran' => $data['referensi_pembayaran'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'dibuat_oleh' => $request->user()->id,
            ]);

            foreach ($allocations as $row) {
                $payment->detail()->create($row);
            }

            $excess = round($total - $allocated, 2);
            if ($excess > 0) {
                $overpayment = KelebihanPembayaranSupplier::create([
                    'nomor_kelebihan' => 'KL-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                    'pembayaran_pembelian_id' => $payment->id,
                    'supplier_id' => $supplier->id,
                    'jumlah_kelebihan' => $excess,
                    'tanggal' => $data['tanggal_pembayaran'],
                    'catatan' => $data['catatan'] ?? null,
                    'dibuat_oleh' => $request->user()->id,
                ]);

                if ($request->boolean('jadikan_titipan')) {
                    $settlement = $overpayment->penyelesaian()->create([
                        'jenis' => PenyelesaianKelebihanSupplier::JENIS_TITIPAN,
                        'jumlah' => $excess,
                        'tanggal' => $data['tanggal_pembayaran'],
                        'catatan' => 'Dijadikan titipan saat pencatatan pembayaran.',
                        'dibuat_oleh' => $request->user()->id,
                    ]);
                    $overpayment->load('supplier');
                    \App\Models\MutasiSaldoSupplier::create([
                        'supplier_id' => $supplier->id,
                        'penyelesaian_kelebihan_supplier_id' => $settlement->id,
                        'jenis' => 'masuk',
                        'jumlah' => $excess,
                        'tanggal' => $data['tanggal_pembayaran'],
                        'referensi_tipe' => 'kelebihan_pembayaran',
                        'referensi_id' => $overpayment->id,
                        'catatan' => 'Titipan dari pembayaran ' . $payment->nomor_pembayaran,
                        'dibuat_oleh' => $request->user()->id,
                    ]);
                }
            }
        });

        return redirect()->route('purchases.payments.index')->with('success', 'Pembayaran pembelian berhasil dicatat.');
    }

    public function show(PembayaranPembelian $pembayaran): View
    {
        $pembayaran->load(['supplier', 'detail.pembelian', 'kelebihan.penyelesaian']);

        return view('purchases.payments.show', [
            'payment' => $pembayaran,
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }
}
