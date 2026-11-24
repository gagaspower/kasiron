<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'kode',
    'nama',
    'dimensi',
    'aktif',
])]
class Satuan extends Model
{
    use HasUlids, SoftDeletes;

    protected $table = 'satuan';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'satuan_dasar_id');
    }

    public function satuanProduk(): HasMany
    {
        return $this->hasMany(SatuanProduk::class, 'satuan_id');
    }

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }
}
