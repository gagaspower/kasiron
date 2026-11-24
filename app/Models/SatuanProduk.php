<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'produk_id',
    'satuan_id',
    'konversi_ke_satuan_dasar',
    'presisi_kuantitas',
    'harga_jual',
    'barcode',
    'satuan_utama',
    'aktif',
])]
class SatuanProduk extends Model
{
    use HasUlids, SoftDeletes;

    protected $table = 'satuan_produk';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    /**
     * Get the product that owns this sale unit.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id');
    }

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'konversi_ke_satuan_dasar' => 'decimal:6',
            'presisi_kuantitas' => 'integer',
            'harga_jual' => 'integer',
            'satuan_utama' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
