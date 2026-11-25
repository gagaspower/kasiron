<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Attributes\\Fillable;
use Illuminate\\Database\\Eloquent\\Concerns\\HasUlids;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

#[Fillable([
    'nomor_penerimaan',
    'pembelian_id',
    'tanggal_penerimaan',
    'status',
    'catatan',
    'dibuat_oleh',
    'diterima_oleh',
    'diterima_pada',
])]
class PenerimaanPembelian extends Model
{
    use HasUlids;

    protected $table = 'penerimaan_pembelian';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_DITERIMA = 'diterima';

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PenerimaanPembelianDetail::class, 'penerimaan_pembelian_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function diterimaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    protected function casts(): array
    {
        return [
            'tanggal_penerimaan' => 'date',
            'diterima_pada' => 'datetime',
        ];
    }
}
