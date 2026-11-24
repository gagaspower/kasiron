<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama', 'aktif'])]
class Kategori extends Model
{
    use HasUlids, SoftDeletes;

    protected $table = 'kategori';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    /**
     * Get the products assigned to this category.
     *
     * @return HasMany<Produk, $this>
     */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori_id', 'id');
    }

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }
}
