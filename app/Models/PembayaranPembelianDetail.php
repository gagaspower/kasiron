<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pembayaran_pembelian_id', 'pembelian_id', 'jumlah_bayar'])]
class PembayaranPembelianDetail extends Model
{
    use HasUlids;

    protected $table = 'pembayaran_pembelian_detail';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(PembayaranPembelian::class, 'pembayaran_pembelian_id');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    protected function casts(): array
    {
        return ['jumlah_bayar' => 'decimal:2'];
    }
}
