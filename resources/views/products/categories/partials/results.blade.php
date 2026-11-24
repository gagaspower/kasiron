<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="font-semibold text-slate-900">{{ $trash ? 'Sampah kategori' : 'Daftar kategori' }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $categories->total() }} data ditemukan</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="GET" action="{{ route('categories.index') }}" class="relative" data-category-search-form>
                    <input type="hidden" name="status" value="{{ $trash ? 'trash' : 'active' }}">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kategori..." autocomplete="off" data-category-search class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-9 text-sm outline-none placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 sm:w-64"><button type="button" data-category-search-clear aria-label="Hapus pencarian" class="{{ $search !== '' ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
                </form>
                <a href="{{ route('categories.index', ['status' => $trash ? 'active' : 'trash']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    {{ $trash ? 'Kategori aktif' : 'Sampah' }}
                </a>
            </div>
        </div>

        <form id="category-bulk-form" method="POST" action="{{ route('categories.bulk-action') }}" data-bulk-form data-category-ajax-form>
            @csrf
            <input type="hidden" name="action" value="{{ $trash ? 'restore' : 'trash' }}" data-bulk-action>
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <label class="inline-flex items-center gap-2 text-sm text-slate-500">
                    <input type="checkbox" data-select-all class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Pilih semua di halaman ini
                </label>
                <div class="flex flex-wrap items-center gap-2">
                    @if($trash)
                        <button type="submit" data-bulk-submit="restore" class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Pulihkan terpilih</button>
                        <button type="submit" data-bulk-submit="force_delete" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus permanen</button>
                    @else
                        <button type="submit" data-bulk-submit="trash" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Pindahkan ke sampah</button>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
                <table class="w-full min-w-[650px] text-left text-sm">
                    <thead class="bg-white text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="w-12 px-5 py-3.5"></th>
                            <th class="px-3 py-3.5">Nama kategori</th>
                            <th class="px-3 py-3.5">Status</th>
                            @if($trash)<th class="px-3 py-3.5">Dihapus pada</th>@endif
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $category)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-5 py-4"><input type="checkbox" form="category-bulk-form" name="ids[]" value="{{ $category->id }}" data-row-checkbox class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"></td>
                                <td class="px-3 py-4"><span class="font-semibold text-slate-800">{{ $category->nama }}</span></td>
                                <td class="px-3 py-4">
                                    @if($trash)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Di sampah</span>
                                    @elseif($category->aktif)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Aktif</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700"><span class="size-1.5 rounded-full bg-amber-500"></span>Nonaktif</span>
                                    @endif
                                </td>
                                @if($trash)<td class="px-3 py-4 text-slate-500">{{ $category->deleted_at?->format('d M Y, H:i') ?? '—' }}</td>@endif
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($trash)
                                            <form method="POST" action="{{ route('categories.restore', $category->id) }}" data-category-ajax-form>@csrf @method('PATCH')
                                                <button type="submit" class="rounded-lg px-2.5 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Pulihkan</button>
                                            </form>
                                            <form method="POST" action="{{ route('categories.force-delete', $category->id) }}" data-category-ajax-form data-confirm="Hapus permanen kategori ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')
                                                <button type="submit" class="rounded-lg px-2.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus permanen</button>
                                            </form>
                                        @else
                                            <button type="button" data-category-edit data-id="{{ $category->id }}" data-url="{{ route('categories.update', $category) }}" data-name="{{ $category->nama }}" class="rounded-lg px-2.5 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Edit</button>
                                            <form method="POST" action="{{ route('categories.toggle-status', $category) }}" data-category-ajax-form>@csrf @method('PATCH')
                                                <button type="submit" class="rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">{{ $category->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}" data-category-ajax-form data-confirm="Pindahkan kategori ini ke sampah?">@csrf @method('DELETE')
                                                <button type="submit" class="rounded-lg px-2.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Ke sampah</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $trash ? 5 : 4 }}" class="px-5 py-14 text-center">
                                <p class="font-semibold text-slate-700">Belum ada kategori</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $search ? 'Coba gunakan kata kunci lain.' : 'Data kategori akan muncul di sini.' }}</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">Menampilkan {{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</p>
                {{ $categories->onEachSide(1)->links('vendor.pagination.kasiron') }}
            </div>
    </section>