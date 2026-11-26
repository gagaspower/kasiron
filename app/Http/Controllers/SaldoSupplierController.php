<?php

namespace App\Http\Controllers;

use App\Models\KelebihanPembayaranSupplier;
use App\Models\MutasiSaldoSupplier;
use App\Models\PenyelesaianKelebihanSupplier;
use App\Models\Supplier;
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
            ->select('supplier_id')
            ->selectRaw("SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE -jumlah END) as saldo")
            ->with('supplier')
            ->groupBy('supplier_id')
            ->havingRaw('SUM(CASE WHEN jenis = ? THEN jumlah ELSE -jumlah END) > 0', ['masuk'])
            ->get();

        $mutations = MutasiSaldoSupplier::query()
            ->with('supplier')
            ->latest('tanggal')
            ->paginate(10, ['*'], 'mutasi_page')
            ->withQueryString();

        return view('purchases.balances.index', [
            'overpayments' => $overpayments,
            'balances' => $balances,
            'mutations' => $mutations,
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
}
