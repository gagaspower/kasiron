<?php

namespace App\Http\Controllers;

use App\Models\KelebihanPembayaranSupplier;
use App\Models\MutasiSaldoSupplier;
use App\Models\PenyelesaianKelebihanSupplier;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\PembayaranPembelian;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaldoSupplierController extends Controller
{
    public function index(Request $request): View
    {
        $overpayments = KelebihanPembayaranSupplier::query()
            ->with(['supplier', 'pembayaran'])
            ->withSum('penyelesaian as sudah_diselesaikan', 'jumlah')
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->latest('tanggal')
            ->paginate(10, ['*'], 'kelebihan_page')
            ->withQueryString();

        $balances = MutasiSaldoSupplier::query()
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->select('supplier_id')
            ->selectRaw("SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE -jumlah END) as saldo")
            ->with('supplier')
            ->groupBy('supplier_id')
            ->havingRaw('SUM(CASE WHEN jenis = ? THEN jumlah ELSE -jumlah END) > 0', ['masuk'])
            ->get();

        $openInvoices = Pembelian::query()
            ->whereIn('supplier_id', $balances->pluck('supplier_id'))
            ->whereIn('status', [Pembelian::STATUS_DISETUJUI, Pembelian::STATUS_SEBAGIAN_DITERIMA, Pembelian::STATUS_DITERIMA])
            ->withSum('pembayaranDetail as sudah_dibayar', 'jumlah_bayar')
            ->orderBy('tanggal_pembelian')
            ->get()
            ->filter(fn ($invoice) => round((float) $invoice->total - (float) $invoice->sudah_dibayar, 2) > 0)
            ->groupBy('supplier_id');

        $settlements = PenyelesaianKelebihanSupplier::query()
            ->with('kelebihan.supplier')
            ->latest('tanggal')
            ->paginate(10, ['*'], 'penyelesaian_page')
            ->withQueryString();

        $mutations = MutasiSaldoSupplier::query()
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->with('supplier')
            ->latest('tanggal')
            ->paginate(10, ['*'], 'mutasi_page')
            ->withQueryString();

        return view('purchases.balances.index', [
            'overpayments' => $overpayments,
            'balances' => $balances,
            'mutations' => $mutations,
            'openInvoices' => $openInvoices,
            'settlements' => $settlements,
            'suppliers' => Supplier::query()->orderBy('nama')->get(),
            'navigation' => app(PembelianController::class)->navigationForView(),
        ]);
    }

    public function settle(Request $request, KelebihanPembayaranSupplier $kelebihan)
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:titipan,pengembalian'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'tanggal' => ['required', 'date'],
            'referensi' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $request, $kelebihan) {
            $locked = KelebihanPembayaranSupplier::query()->whereKey($kelebihan->id)->lockForUpdate()->firstOrFail();
            Supplier::query()->whereKey($locked->supplier_id)->lockForUpdate()->firstOrFail();
            $settled = (float) $locked->penyelesaian()->sum('jumlah');
            $remaining = round((float) $locked->jumlah_kelebihan - $settled, 2);
            $amount = round((float) $data['jumlah'], 2);
            if ($amount > $remaining) {
                throw ValidationException::withMessages(['jumlah' => 'Nominal melebihi kelebihan yang belum diselesaikan.']);
            }

            $settlement = $locked->penyelesaian()->create([
                'jenis' => $data['jenis'],
                'jumlah' => $amount,
                'tanggal' => $data['tanggal'],
                'referensi' => $data['referensi'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'dibuat_oleh' => $request->user()->id,
            ]);

            if ($data['jenis'] === 'titipan') {
                MutasiSaldoSupplier::create([
                    'supplier_id' => $locked->supplier_id,
                    'penyelesaian_kelebihan_supplier_id' => $settlement->id,
                    'jenis' => 'masuk',
                    'jumlah' => $amount,
                    'tanggal' => $data['tanggal'],
                    'referensi_tipe' => 'kelebihan_pembayaran',
                    'referensi_id' => $locked->id,
                    'catatan' => $data['catatan'] ?? 'Kelebihan pembayaran dijadikan titipan.',
                    'dibuat_oleh' => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('purchases.balances.index')->with('success', 'Penyelesaian kelebihan pembayaran berhasil dicatat.');
    }

    public function useDeposit(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'alokasi' => ['required', 'array', 'min:1'],
            'alokasi.*' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $request, $supplier) {
            $supplier = Supplier::query()->whereKey($supplier->id)->lockForUpdate()->firstOrFail();
            $balance = (float) MutasiSaldoSupplier::query()
                ->where('supplier_id', $supplier->id)
                ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE -jumlah END), 0) as saldo")
                ->value('saldo');

            $allocations = collect($data['alokasi'])
                ->map(fn ($amount, $id) => ['pembelian_id' => $id, 'jumlah_bayar' => round((float) $amount, 2)])
                ->filter(fn ($row) => $row['jumlah_bayar'] > 0)
                ->values();

            if ($allocations->isEmpty()) {
                throw ValidationException::withMessages(['alokasi' => 'Isi nominal titipan yang digunakan pada minimal satu nota.']);
            }

            $invoiceIds = $allocations->pluck('pembelian_id');
            $invoices = Pembelian::query()->whereIn('id', $invoiceIds)->lockForUpdate()->get()->keyBy('id');
            if ($invoices->count() !== $invoiceIds->unique()->count()) {
                throw ValidationException::withMessages(['alokasi' => 'Ada nota pembelian yang tidak ditemukan.']);
            }

            $total = 0;
            foreach ($allocations as $row) {
                $invoice = $invoices->get($row['pembelian_id']);
                if ($invoice->supplier_id !== $supplier->id || in_array($invoice->status, [Pembelian::STATUS_DRAFT, Pembelian::STATUS_DIBATALKAN], true)) {
                    throw ValidationException::withMessages(['alokasi' => 'Nota harus berasal dari supplier yang dipilih dan bukan draft atau dibatalkan.']);
                }
                $paid = (float) $invoice->pembayaranDetail()->sum('jumlah_bayar');
                if ($row['jumlah_bayar'] > round((float) $invoice->total - $paid, 2)) {
                    throw ValidationException::withMessages(['alokasi' => 'Pemakaian titipan melebihi sisa tagihan nota ' . $invoice->nomor_pembelian . '.']);
                }
                $total += $row['jumlah_bayar'];
            }

            if ($total > $balance) {
                throw ValidationException::withMessages(['alokasi' => 'Total pemakaian melebihi saldo titipan supplier.']);
            }

            $payment = PembayaranPembelian::create([
                'nomor_pembayaran' => 'BY-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'supplier_id' => $supplier->id,
                'tanggal_pembayaran' => $data['tanggal'],
                'total_bayar' => $total,
                'metode_pembayaran' => 'titipan',
                'referensi_pembayaran' => null,
                'catatan' => $data['catatan'] ?? 'Pembayaran menggunakan saldo titipan supplier.',
                'dibuat_oleh' => $request->user()->id,
            ]);

            foreach ($allocations as $row) {
                $payment->detail()->create($row);
            }

            MutasiSaldoSupplier::create([
                'supplier_id' => $supplier->id,
                'jenis' => 'pemakaian',
                'jumlah' => $total,
                'tanggal' => $data['tanggal'],
                'referensi_tipe' => 'pembayaran_pembelian',
                'referensi_id' => $payment->id,
                'catatan' => $data['catatan'] ?? 'Pemakaian saldo titipan untuk pembayaran nota.',
                'dibuat_oleh' => $request->user()->id,
            ]);
        });

        return redirect()->route('purchases.balances.index')->with('success', 'Saldo titipan berhasil digunakan untuk membayar nota pembelian.');
    }
}
