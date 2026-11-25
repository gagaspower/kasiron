<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $trash = $request->string('status')->value() === 'trash';
        $search = trim($request->string('search')->value());

        $units = Satuan::query()
            ->when($trash, fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('dimensi', 'like', "%{$search}%");
            }))
            ->orderBy('nama')
            ->paginate(10)
            ->withPath(route('units.index'))
            ->withQueryString();

        $data = compact('units', 'search', 'trash') + ['navigation' => $this->navigation()];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('products.units.partials.results', $data)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('products.units.index', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => ['required', 'string', 'max:20', Rule::unique('satuan', 'kode')],
            'nama' => ['required', 'string', 'max:255', Rule::unique('satuan', 'nama')],
            'dimensi' => ['required', 'string', 'max:30'],
        ], [
            'kode.required' => 'Kode satuan wajib diisi.',
            'kode.unique' => 'Kode satuan sudah digunakan.',
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique' => 'Nama satuan sudah digunakan.',
            'dimensi.required' => 'Dimensi wajib dipilih.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Periksa kembali data yang dimasukkan.', 'errors' => $validator->errors()], 422);
            return back()->withErrors($validator)->withInput()->with('unit_form_action', route('units.store'));
        }

        Satuan::create($validator->validated() + ['aktif' => true]);

        return $this->respondAfterMutation($request, 'Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validator = Validator::make($request->all(), [
            'kode' => ['required', 'string', 'max:20', Rule::unique('satuan', 'kode')->ignore($satuan->id)],
            'nama' => ['required', 'string', 'max:255', Rule::unique('satuan', 'nama')->ignore($satuan->id)],
            'dimensi' => ['required', 'string', 'max:30'],
        ], [
            'kode.required' => 'Kode satuan wajib diisi.',
            'kode.unique' => 'Kode satuan sudah digunakan.',
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique' => 'Nama satuan sudah digunakan.',
            'dimensi.required' => 'Dimensi wajib dipilih.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Periksa kembali data yang dimasukkan.', 'errors' => $validator->errors()], 422);
            return back()->withErrors($validator)->withInput()
                ->with('unit_form_action', route('units.update', $satuan))
                ->with('unit_form_method', 'PUT');
        }

        $satuan->update($validator->validated());

        return $this->respondAfterMutation($request, 'Satuan berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Satuan $satuan)
    {
        $satuan->update(['aktif' => ! $satuan->aktif]);
        return $this->respondAfterMutation($request, 'Status satuan berhasil diperbarui.');
    }

    public function destroy(Request $request, Satuan $satuan)
    {
        $satuan->delete();
        return $this->respondAfterMutation($request, 'Satuan dipindahkan ke sampah.');
    }

    public function restore(Request $request, string $id)
    {
        Satuan::onlyTrashed()->findOrFail($id)->restore();
        return $this->respondAfterMutation($request, 'Satuan berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, string $id)
    {
        $satuan = Satuan::onlyTrashed()->findOrFail($id);

        if ($satuan->produk()->withTrashed()->exists() || $satuan->satuanProduk()->withTrashed()->exists()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Satuan masih digunakan oleh produk dan tidak dapat dihapus permanen.'], 422);
            return back()->with('success', 'Satuan tidak dapat dihapus permanen karena masih digunakan oleh produk.');
        }

        $satuan->forceDelete();
        return $this->respondAfterMutation($request, 'Satuan berhasil dihapus permanen.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['trash', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:satuan,id'],
        ], ['ids.required' => 'Pilih minimal satu satuan.', 'ids.min' => 'Pilih minimal satu satuan.']);

        $units = Satuan::withTrashed()->whereIn('id', $validated['ids'])->get();
        $count = 0;
        $blocked = 0;

        DB::transaction(function () use ($units, $validated, &$count, &$blocked) {
            foreach ($units as $unit) {
                if ($validated['action'] === 'trash' && ! $unit->trashed()) {
                    $unit->delete();
                    $count++;
                } elseif ($validated['action'] === 'restore' && $unit->trashed()) {
                    $unit->restore();
                    $count++;
                } elseif ($validated['action'] === 'force_delete' && $unit->trashed()) {
                    if ($unit->produk()->withTrashed()->exists() || $unit->satuanProduk()->withTrashed()->exists()) {
                        $blocked++;
                        continue;
                    }
                    $unit->forceDelete();
                    $count++;
                }
            }
        });

        $message = match ($validated['action']) {
            'trash' => "{$count} satuan dipindahkan ke sampah.",
            'restore' => "{$count} satuan dipulihkan.",
            default => "{$count} satuan dihapus permanen.",
        };
        if ($blocked > 0) $message .= " {$blocked} satuan tidak dapat dihapus karena masih digunakan oleh produk.";

        return $this->respondAfterMutation($request, $message);
    }

    private function respondAfterMutation(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            $trash = $request->input('status', $request->query('status')) === 'trash';
            $search = trim((string) $request->input('search', $request->query('search', '')));
            $units = Satuan::query()
                ->when($trash, fn (Builder $query) => $query->onlyTrashed())
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $q) use ($search) {
                    $q->where('kode', 'like', "%{$search}%")->orWhere('nama', 'like', "%{$search}%")->orWhere('dimensi', 'like', "%{$search}%");
                }))
                ->orderBy('nama')
                ->paginate(10)
                ->withPath(route('units.index'))
                ->appends(array_filter(['status' => $trash ? 'trash' : null, 'search' => $search !== '' ? $search : null]));

            return response()->json([
                'message' => $message,
                'html' => view('products.units.partials.results', compact('units', 'search', 'trash'))->render(),
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
            ['label' => 'Manajemen Toko', 'active' => true, 'children' => [
                ['label' => 'Master', 'href' => route('products.index')],
                ['label' => 'Pembelian', 'children' => [
                    ['label' => 'Transaksi Pembelian', 'href' => route('purchases.transactions.index')],
                    ['label' => 'Penerimaan Barang', 'href' => route('purchases.receipts.index')],
                    ['label' => 'Pembayaran Pembelian', 'href' => route('purchases.payments.index')],
                ]],
                ['label' => 'Persediaan', 'disabled' => true, 'status' => 'Segera'],
            ]],
            ['label' => 'Laporan', 'children' => [
                ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                ['label' => 'Pembayaran', 'href' => '#ringkasan'],
            ]],
        ];
    }
}
