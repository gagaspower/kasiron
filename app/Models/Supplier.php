<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function pembelian(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'supplier_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranPembelian::class, 'supplier_id');
    }

    public function mutasiSaldo(): HasMany
    {
        return $this->hasMany(MutasiSaldoSupplier::class, 'supplier_id');
    }

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }
}
