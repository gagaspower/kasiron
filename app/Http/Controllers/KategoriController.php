<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $trash = $request->string('status')->value() === 'trash';
        $search = trim($request->string('search')->value());

        $categories = Kategori::query()
            ->when($trash, fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', fn (Builder $query) => $query->where('nama', 'like', "%{$search}%"))
            ->orderBy('nama')
            ->paginate(10)
            ->withPath(route('categories.index'))
            ->withQueryString();

        $data = compact('categories', 'search', 'trash') + ['navigation' => $this->navigation()];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('products.categories.partials.results', $data)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('products.categories.index', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255', Rule::unique('kategori', 'nama')],
        ], ['nama.required' => 'Nama kategori wajib diisi.', 'nama.unique' => 'Nama kategori sudah digunakan.']);

        if ($validator->fails()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Periksa kembali data yang dimasukkan.', 'errors' => $validator->errors()], 422);

            return redirect()->route('categories.index')->withErrors($validator)->withInput()
                ->with('category_form_action', route('categories.store'));
        }

        Kategori::create(['nama' => $validator->validated()['nama'], 'aktif' => true]);

        return $this->respondAfterMutation($request, 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
        ], ['nama.required' => 'Nama kategori wajib diisi.', 'nama.unique' => 'Nama kategori sudah digunakan.']);

        if ($validator->fails()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Periksa kembali data yang dimasukkan.', 'errors' => $validator->errors()], 422);

            return redirect()->route('categories.index')->withErrors($validator)->withInput()
                ->with('category_form_action', route('categories.update', $kategori))
                ->with('category_form_method', 'PUT');
        }

        $kategori->update(['nama' => $validator->validated()['nama']]);

        return $this->respondAfterMutation($request, 'Kategori berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Kategori $kategori)
    {
        $kategori->update(['aktif' => ! $kategori->aktif]);

        return $this->respondAfterMutation($request, 'Status kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, Kategori $kategori)
    {
        $kategori->delete();

        return $this->respondAfterMutation($request, 'Kategori dipindahkan ke sampah.');
    }

    public function restore(Request $request, string $id)
    {
        Kategori::onlyTrashed()->findOrFail($id)->restore();

        return $this->respondAfterMutation($request, 'Kategori berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, string $id)
    {
        $kategori = Kategori::onlyTrashed()->findOrFail($id);

        if ($kategori->produk()->withTrashed()->exists()) {
            if ($request->expectsJson()) return response()->json(['message' => 'Kategori masih digunakan oleh produk.'], 422);
            return back()->with('success', 'Kategori tidak dapat dihapus permanen karena masih digunakan oleh produk.');
        }

        $kategori->forceDelete();

        return $this->respondAfterMutation($request, 'Kategori berhasil dihapus permanen.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['trash', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:kategori,id'],
        ], ['ids.required' => 'Pilih minimal satu kategori.', 'ids.min' => 'Pilih minimal satu kategori.']);

        $categories = Kategori::withTrashed()->whereIn('id', $validated['ids'])->get();
        $count = 0;
        $blocked = 0;

        DB::transaction(function () use ($categories, $validated, &$count, &$blocked) {
            foreach ($categories as $category) {
                if ($validated['action'] === 'trash' && ! $category->trashed()) {
                    $category->delete();
                    $count++;
                } elseif ($validated['action'] === 'restore' && $category->trashed()) {
                    $category->restore();
                    $count++;
                } elseif ($validated['action'] === 'force_delete' && $category->trashed()) {
                    if ($category->produk()->withTrashed()->exists()) {
                        $blocked++;
                        continue;
                    }

                    $category->forceDelete();
                    $count++;
                }
            }
        });

        $message = match ($validated['action']) {
            'trash' => "{$count} kategori dipindahkan ke sampah.",
            'restore' => "{$count} kategori dipulihkan.",
            default => "{$count} kategori dihapus permanen.",
        };

        if ($blocked > 0) {
            $message .= " {$blocked} kategori tidak dapat dihapus karena masih digunakan oleh produk.";
        }

        return $this->respondAfterMutation($request, $message);
    }

    private function respondAfterMutation(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            $trash = $request->input('status', $request->query('status')) === 'trash';
            $search = trim((string) $request->input('search', $request->query('search', '')));
            $categories = Kategori::query()
                ->when($trash, fn (Builder $query) => $query->onlyTrashed())
                ->when($search !== '', fn (Builder $query) => $query->where('nama', 'like', "%{$search}%"))
                ->orderBy('nama')
                ->paginate(10)
                ->withPath(route('categories.index'))
                ->appends(array_filter([
                    'status' => $trash ? 'trash' : null,
                    'search' => $search !== '' ? $search : null,
                ]));

            return response()->json([
                'message' => $message,
                'html' => view('products.categories.partials.results', compact('categories', 'search', 'trash'))->render(),
            ]);
        }

        return back()->with('success', $message);
    }

    private function navigation(): array
    {
        return [
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ['label' => 'Transaksi', 'children' => [
                ['label' => 'Transaksi terbaru', 'href' => '#transaksi'],
                ['label' => 'Metode pembayaran', 'href' => '#ringkasan'],
            ]],
            ['label' => 'Manajemen Toko', 'active' => request()->routeIs('products.*', 'products-master.*', 'categories.*', 'units.*', 'suppliers.*', 'purchases.*', 'inventory.*'), 'children' => [
                ['label' => 'Master', 'href' => route('products.index'), 'active' => request()->routeIs('products.index', 'products-master.*', 'categories.*', 'units.*', 'suppliers.*')],
                ['label' => 'Pembelian', 'href' => route('purchases.index'), 'active' => request()->routeIs('purchases.*')],
                ['label' => 'Persediaan', 'href' => route('inventory.index'), 'active' => request()->routeIs('inventory.*')],
            ]],
            ['label' => 'Laporan', 'children' => [
                ['label' => 'Ringkasan penjualan', 'href' => '#dashboard'],
                ['label' => 'Pembayaran', 'href' => '#ringkasan'],
            ]],
        ];
    }
}
