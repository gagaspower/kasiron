<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Attributes\\Fillable;
use Illuminate\\Database\\Eloquent\\Concerns\\HasUlids;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;\nuse Illuminate\\Database\\Eloquent\\Relations\\HasMany;

#[Fillable([
    'pembelian_id',
    'produk_id',
    'satuan_produk_id',
    'nama_produk',
    'kode_sku',
    'nama_satuan',
    'konversi_ke_satuan_dasar',
    'jumlah',
    'jumlah_dalam_satuan_dasar',
    'harga_beli',
    'diskon',
    'subtotal',
    'catatan',
])]
class PembelianDetail extends Model
{
    use HasUlids;

    protected $table = 'pembelian_detail';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function satuanProduk(): BelongsTo
    {
        return $this->belongsTo(SatuanProduk::class, 'satuan_produk_id');
    }

    public function penerimaanDetail(): HasMany
    {
        return $this->hasMany(PenerimaanPembelianDetail::class, 'pembelian_detail_id');
    }

    protected function casts(): array
    {
        return [
            'konversi_ke_satuan_dasar' => 'decimal:6',
            'jumlah' => 'decimal:6',
            'jumlah_dalam_satuan_dasar' => 'decimal:6',
            'harga_beli' => 'decimal:2',
            'diskon' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }
}
