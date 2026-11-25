<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $trash = $request->string('status')->value() === 'trash';
        $search = trim($request->string('search')->value());

        $suppliers = Supplier::query()
            ->when($trash, fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
            }))
            ->orderBy('nama')
            ->paginate(10)
            ->withPath(route('suppliers.index'))
            ->withQueryString();

        $data = compact('suppliers', 'search', 'trash') + ['navigation' => $this->navigation()];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('products.suppliers.partials.results', $data)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('products.suppliers.index', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'nomor_telepon' => ['nullable', 'string', 'max:30'],
        ], [
            'nama.required' => 'Nama supplier wajib diisi.',
            'nama.max' => 'Nama supplier maksimal 255 karakter.',
            'nomor_telepon.max' => 'Nomor telepon maksimal 30 karakter.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Periksa kembali data yang dimasukkan.', 'errors' => $validator->errors()], 422);
            }

            return redirect()->route('suppliers.index')->withErrors($validator)->withInput()
                ->with('supplier_form_action', route('suppliers.store'));
        }

        Supplier::create($validator->validated() + ['aktif' => true]);

        return $this->respondAfterMutation($request, 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'nomor_telepon' => ['nullable', 'string', 'max:30'],
        ], [
            'nama.required' => 'Nama supplier wajib diisi.',
            'nama.max' => 'Nama supplier maksimal 255 karakter.',
            'nomor_telepon.max' => 'Nomor telepon maksimal 30 karakter.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Periksa kembali data yang dimasukkan.', 'errors' => $validator->errors()], 422);
            }

            return redirect()->route('suppliers.index')->withErrors($validator)->withInput()
                ->with('supplier_form_action', route('suppliers.update', $supplier))
                ->with('supplier_form_method', 'PUT');
        }

        $supplier->update($validator->validated());

        return $this->respondAfterMutation($request, 'Supplier berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Supplier $supplier)
    {
        $supplier->update(['aktif' => ! $supplier->aktif]);

        return $this->respondAfterMutation($request, 'Status supplier berhasil diperbarui.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $supplier->delete();

        return $this->respondAfterMutation($request, 'Supplier dipindahkan ke sampah.');
    }

    public function restore(Request $request, string $id)
    {
        Supplier::onlyTrashed()->findOrFail($id)->restore();

        return $this->respondAfterMutation($request, 'Supplier berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, string $id)
    {
        Supplier::onlyTrashed()->findOrFail($id)->forceDelete();

        return $this->respondAfterMutation($request, 'Supplier berhasil dihapus permanen.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['trash', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:supplier,id'],
        ], [
            'ids.required' => 'Pilih minimal satu supplier.',
            'ids.min' => 'Pilih minimal satu supplier.',
        ]);

        $suppliers = Supplier::withTrashed()->whereIn('id', $validated['ids'])->get();
        $count = 0;

        DB::transaction(function () use ($suppliers, $validated, &$count) {
            foreach ($suppliers as $supplier) {
                if ($validated['action'] === 'trash' && ! $supplier->trashed()) {
                    $supplier->delete();
                    $count++;
                } elseif ($validated['action'] === 'restore' && $supplier->trashed()) {
                    $supplier->restore();
                    $count++;
                } elseif ($validated['action'] === 'force_delete' && $supplier->trashed()) {
                    $supplier->forceDelete();
                    $count++;
                }
            }
        });

        $message = match ($validated['action']) {
            'trash' => "{$count} supplier dipindahkan ke sampah.",
            'restore' => "{$count} supplier dipulihkan.",
            default => "{$count} supplier dihapus permanen.",
        };

        return $this->respondAfterMutation($request, $message);
    }

    private function respondAfterMutation(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            $trash = $request->input('status', $request->query('status')) === 'trash';
            $search = trim((string) $request->input('search', $request->query('search', '')));

            $suppliers = Supplier::query()
                ->when($trash, fn (Builder $query) => $query->onlyTrashed())
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%")
                        ->orWhere('nomor_telepon', 'like', "%{$search}%");
                }))
                ->orderBy('nama')
                ->paginate(10)
                ->withPath(route('suppliers.index'))
                ->appends(array_filter([
                    'status' => $trash ? 'trash' : null,
                    'search' => $search !== '' ? $search : null,
                ]));

            return response()->json([
                'message' => $message,
                'html' => view('products.suppliers.partials.results', compact('suppliers', 'search', 'trash'))->render(),
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
