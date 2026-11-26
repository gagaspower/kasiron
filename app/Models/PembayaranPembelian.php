<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nomor_pembayaran',
    'supplier_id',
    'tanggal_pembayaran',
    'total_bayar',
    'metode_pembayaran',
    'referensi_pembayaran',
    'catatan',
    'status',
    'alasan_pembatalan',
    'dibatalkan_oleh',
    'dibatalkan_pada',
    'dibuat_oleh',
])]
class PembayaranPembelian extends Model
{
    use HasUlids;

    protected $table = 'pembayaran_pembelian';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public function dibatalkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PembayaranPembelianDetail::class, 'pembayaran_pembelian_id');
    }

    public function kelebihan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(KelebihanPembayaranSupplier::class, 'pembayaran_pembelian_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    protected function casts(): array
    {
        return [
            'tanggal_pembayaran' => 'date',
            'total_bayar' => 'decimal:2',
            'dibatalkan_pada' => 'datetime',
        ];
    }
}
