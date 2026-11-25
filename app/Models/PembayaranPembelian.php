<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Attributes\\Fillable;
use Illuminate\\Database\\Eloquent\\Concerns\\HasUlids;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

#[Fillable([
    'nomor_pembayaran',
    'pembelian_id',
    'tanggal_pembayaran',
    'jumlah_bayar',
    'metode_pembayaran',
    'referensi_pembayaran',
    'catatan',
    'dibuat_oleh',
])]
class PembayaranPembelian extends Model
{
    use HasUlids;

    protected $table = 'pembayaran_pembelian';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    protected function casts(): array
    {
        return [
            'tanggal_pembayaran' => 'date',
            'jumlah_bayar' => 'decimal:2',
        ];
    }
}
