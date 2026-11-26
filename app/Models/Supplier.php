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

    public function pembelian(): HasMany\n    {\n        return $this->hasMany(Pembelian::class, 'supplier_id');\n    }\n\n    public function pembayaran(): HasMany\n    {\n        return $this->hasMany(PembayaranPembelian::class, 'supplier_id');\n    }\n\n    public function mutasiSaldo(): HasMany\n    {\n        return $this->hasMany(MutasiSaldoSupplier::class, 'supplier_id');\n    }\n\n    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }
}
