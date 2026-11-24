<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'kategori_id',
    'kode_sku',
    'nama',
    'satuan_dasar',
    'jumlah_stok',
    'stok_minimum',
    'aktif',
])]
class Produk extends Model
{
    use HasUlids, SoftDeletes;

    protected $table = 'produk';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    /**
     * Get the category assigned to this product.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
    }

    /**
     * Get the sale units configured for this product.
     *
     * @return HasMany<SatuanProduk, $this>
     */
    public function satuanProduk(): HasMany
    {
        return $this->hasMany(SatuanProduk::class, 'produk_id', 'id');
    }

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_stok' => 'decimal:6',
            'stok_minimum' => 'decimal:6',
            'aktif' => 'boolean',
        ];
    }
}
