<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'penerimaan_pembelian_id',
    'pembelian_detail_id',
    'jumlah_diterima',
    'jumlah_diterima_dalam_satuan_dasar',
    'catatan',
])]
class PenerimaanPembelianDetail extends Model
{
    use HasUlids;

    protected $table = 'penerimaan_pembelian_detail';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanPembelian::class, 'penerimaan_pembelian_id');
    }

    public function pembelianDetail(): BelongsTo
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_detail_id');
    }

    protected function casts(): array
    {
        return [
            'jumlah_diterima' => 'decimal:6',
            'jumlah_diterima_dalam_satuan_dasar' => 'decimal:6',
        ];
    }
}
