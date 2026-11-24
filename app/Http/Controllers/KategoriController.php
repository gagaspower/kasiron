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
    public function index(Request $request): View
    {
        $trash = $request->string('status')->value() === 'trash';
        $search = trim($request->string('search')->value());

        $categories = Kategori::query()
            ->when($trash, fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', fn (Builder $query) => $query->where('nama', 'like', "%{$search}%"))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('products.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'trash' => $trash,
            'navigation' => $this->navigation(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255', Rule::unique('kategori', 'nama')],
        ], ['nama.required' => 'Nama kategori wajib diisi.', 'nama.unique' => 'Nama kategori sudah digunakan.']);

        if ($validator->fails()) {
            return redirect()->route('categories.index')->withErrors($validator)->withInput()
                ->with('category_form_action', route('categories.store'));
        }

        Kategori::create(['nama' => $validator->validated()['nama'], 'aktif' => true]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
        ], ['nama.required' => 'Nama kategori wajib diisi.', 'nama.unique' => 'Nama kategori sudah digunakan.']);

        if ($validator->fails()) {
            return redirect()->route('categories.index')->withErrors($validator)->withInput()
                ->with('category_form_action', route('categories.update', $kategori));
        }

        $kategori->update(['nama' => $validator->validated()['nama']]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggleStatus(Kategori $kategori): RedirectResponse
    {
        $kategori->update(['aktif' => ! $kategori->aktif]);

        return back()->with('success', 'Status kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $kategori->delete();

        return back()->with('success', 'Kategori dipindahkan ke sampah.');
    }

    public function restore(string $id): RedirectResponse
    {
        Kategori::onlyTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'Kategori berhasil dipulihkan.');
    }

    public function forceDelete(string $id): RedirectResponse
    {
        $kategori = Kategori::onlyTrashed()->findOrFail($id);

        if ($kategori->produk()->exists()) {
            return back()->with('success', 'Kategori tidak dapat dihapus permanen karena masih digunakan oleh produk.');
        }

        $kategori->forceDelete();

        return back()->with('success', 'Kategori berhasil dihapus permanen.');
    }

    public function bulkAction(Request $request): RedirectResponse
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
                    if ($category->produk()->exists()) {
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
