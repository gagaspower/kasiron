<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nama',
    'alamat',
    'nomor_telepon',
    'aktif',
])]
class Supplier extends Model
{
    use HasUlids, SoftDeletes;

    protected $table = 'supplier';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }
}
