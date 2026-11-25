<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'produk_id',
    'jenis',
    'jumlah',
    'stok_sebelum',
    'stok_sesudah',
    'referensi_tipe',
    'referensi_id',
    'catatan',
])]
class MutasiStok extends Model
{
    use HasUlids;

    protected $table = 'mutasi_stok';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = null;

    public const JENIS_STOK_AWAL = 'stok_awal';

    public const JENIS_MASUK = 'masuk';

    public const JENIS_KELUAR = 'keluar';

    public const JENIS_PENYESUAIAN = 'penyesuaian';

    public const JENIS_PENJUALAN = 'penjualan';

    public const JENIS_PEMBATALAN_PENJUALAN = 'pembatalan_penjualan';

    public const JENIS_RETUR = 'retur';

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:6',
            'stok_sebelum' => 'decimal:6',
            'stok_sesudah' => 'decimal:6',
            'dibuat_pada' => 'datetime',
        ];
    }
}
