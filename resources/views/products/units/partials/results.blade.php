<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div><h2 class="font-semibold text-slate-900">{{ $trash ? 'Sampah satuan' : 'Daftar satuan' }}</h2><p class="mt-1 text-xs text-slate-500">{{ $units->total() }} data ditemukan</p></div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <form method="GET" action="{{ route('units.index') }}" class="relative" data-unit-search-form>
                <input type="hidden" name="status" value="{{ $trash ? 'trash' : 'active' }}">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama satuan..." autocomplete="off" data-unit-search class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-9 text-sm outline-none placeholder:text-slate-400 focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 sm:w-64">
                <button type="button" data-unit-search-clear aria-label="Hapus pencarian" class="{{ $search !== '' ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
            </form>
            <label class="sr-only" for="unit-status-filter">Tampilkan satuan</label>
            <select id="unit-status-filter" data-unit-status-filter class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 sm:w-44">
                <option value="active" @selected(!$trash)>Satuan aktif</option><option value="trash" @selected($trash)>Sampah</option>
            </select>
        </div>
    </div>
    <form id="unit-bulk-form" method="POST" action="{{ route('units.bulk-action') }}" data-unit-bulk-form data-unit-ajax-form>
        @csrf
        <input type="hidden" name="action" value="{{ $trash ? 'restore' : 'trash' }}" data-unit-bulk-action>
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <label class="inline-flex items-center gap-2 text-sm text-slate-500"><input type="checkbox" data-unit-select-all class="size-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">Pilih semua di halaman ini</label>
            <div class="flex flex-wrap items-center gap-2">
                @if($trash)
                    <button type="submit" data-unit-bulk-submit="restore" class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Pulihkan terpilih</button>
                    <button type="submit" data-unit-bulk-submit="force_delete" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus permanen</button>
                @else
                    <button type="submit" data-unit-bulk-submit="trash" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Pindahkan ke sampah</button>
                @endif
            </div>
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-white text-xs font-semibold uppercase tracking-wider text-slate-400"><tr><th class="w-12 px-5 py-3.5"></th><th class="px-3 py-3.5">Kode</th><th class="px-3 py-3.5">Nama satuan</th><th class="px-3 py-3.5">Dimensi</th><th class="px-3 py-3.5">Status</th>@if($trash)<th class="px-3 py-3.5">Dihapus pada</th>@endif<th class="px-5 py-3.5 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($units as $unit)
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-5 py-4"><input type="checkbox" form="unit-bulk-form" name="ids[]" value="{{ $unit->id }}" data-unit-row-checkbox class="size-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"></td>
                        <td class="px-3 py-4"><span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-600">{{ $unit->kode }}</span></td>
                        <td class="px-3 py-4"><span class="font-semibold text-slate-800">{{ $unit->nama }}</span></td>
                        <td class="px-3 py-4"><span class="text-slate-500">{{ ucfirst($unit->dimensi) }}</span></td>
                        <td class="px-3 py-4">
                            @if($trash)<span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Di sampah</span>
                            @elseif($unit->aktif)<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Aktif</span>
                            @else<span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700"><span class="size-1.5 rounded-full bg-amber-500"></span>Nonaktif</span>@endif
                        </td>
                        @if($trash)<td class="px-3 py-4 text-slate-500">{{ $unit->deleted_at?->format('d M Y, H:i') ?? '—' }}</td>@endif
                        <td class="px-5 py-4"><div class="flex items-center justify-end gap-1">
                            @if($trash)
                                <form method="POST" action="{{ route('units.restore', $unit->id) }}" data-unit-ajax-form>@csrf @method('PATCH')<button type="submit" title="Pulihkan satuan" aria-label="Pulihkan satuan {{ $unit->nama }}" class="rounded-lg p-2 text-blue-700 hover:bg-blue-50"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M3 12a9 9 0 1 0 2.6-6.4L3 8m0-5v5h5m4-1v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form>
                                <form method="POST" action="{{ route('units.force-delete', $unit->id) }}" data-unit-ajax-form data-confirm="Hapus permanen satuan ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" title="Hapus permanen" aria-label="Hapus permanen satuan {{ $unit->nama }}" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M5.5 7l1 13h11l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form>
                            @else
                                <button type="button" data-unit-edit data-url="{{ route('units.update', $unit) }}" data-kode="{{ $unit->kode }}" data-name="{{ $unit->nama }}" data-dimensi="{{ $unit->dimensi }}" title="Edit satuan" aria-label="Edit satuan {{ $unit->nama }}" class="rounded-lg p-2 text-violet-700 hover:bg-violet-50"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="m14.5 6.5 3 3M4 20l4.2-.8L19 8.4a2.12 2.12 0 0 0-3-3L5.2 16.2 4 20Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                <form method="POST" action="{{ route('units.toggle-status', $unit) }}" data-unit-ajax-form>@csrf @method('PATCH')<button type="submit" title="{{ $unit->aktif ? 'Nonaktifkan satuan' : 'Aktifkan satuan' }}" aria-label="{{ $unit->aktif ? 'Nonaktifkan' : 'Aktifkan' }} satuan {{ $unit->nama }}" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100">@if($unit->aktif)<svg class="size-4" viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="12" rx="6" stroke="currentColor" stroke-width="1.7"/><circle cx="15" cy="12" r="3" fill="currentColor"/></svg>@else<svg class="size-4" viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="12" rx="6" stroke="currentColor" stroke-width="1.7"/><circle cx="9" cy="12" r="3" fill="currentColor"/></svg>@endif</button></form>
                                <form method="POST" action="{{ route('units.destroy', $unit) }}" data-unit-ajax-form data-confirm="Pindahkan satuan ini ke sampah?">@csrf @method('DELETE')<button type="submit" title="Pindahkan ke sampah" aria-label="Pindahkan satuan {{ $unit->nama }} ke sampah" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50"><svg class="size-4" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M5.5 7l1 13h11l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $trash ? 7 : 6 }}" class="px-5 py-14 text-center"><p class="font-semibold text-slate-700">Belum ada satuan</p><p class="mt-1 text-sm text-slate-500">{{ $search ? 'Coba gunakan kata kunci lain.' : 'Data satuan akan muncul di sini.' }}</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><p class="text-xs text-slate-500">Menampilkan {{ $units->firstItem() ?? 0 }}–{{ $units->lastItem() ?? 0 }} dari {{ $units->total() }} satuan</p>{{ $units->onEachSide(1)->links('vendor.pagination.kasiron') }}</div>
</section>