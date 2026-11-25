<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nomor_pembelian',
    'supplier_id',
    'nomor_faktur_supplier',
    'tanggal_pembelian',
    'tanggal_jatuh_tempo',
    'jenis_pembayaran',
    'status',
    'subtotal',
    'diskon',
    'biaya_lain',
    'total',
    'catatan',
    'dibuat_oleh',
    'disetujui_oleh',
    'disetujui_pada',
])]
class Pembelian extends Model
{
    use HasUlids;

    protected $table = 'pembelian';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_SEBAGIAN_DITERIMA = 'sebagian_diterima';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const JENIS_PEMBAYARAN_TUNAI = 'tunai';

    public const JENIS_PEMBAYARAN_KREDIT = 'kredit';

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id');
    }

    public function penerimaan(): HasMany
    {
        return $this->hasMany(PenerimaanPembelian::class, 'pembelian_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranPembelian::class, 'pembelian_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    protected function casts(): array
    {
        return [
            'tanggal_pembelian' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'subtotal' => 'decimal:2',
            'diskon' => 'decimal:2',
            'biaya_lain' => 'decimal:2',
            'total' => 'decimal:2',
            'disetujui_pada' => 'datetime',
        ];
    }
}
