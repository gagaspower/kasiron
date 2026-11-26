<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nomor_kelebihan', 'pembayaran_pembelian_id', 'supplier_id', 'jumlah_kelebihan', 'tanggal', 'catatan', 'dibuat_oleh'])]
class KelebihanPembayaranSupplier extends Model
{
    use HasUlids;

    protected $table = 'kelebihan_pembayaran_supplier';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(PembayaranPembelian::class, 'pembayaran_pembelian_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function penyelesaian(): HasMany
    {
        return $this->hasMany(PenyelesaianKelebihanSupplier::class, 'kelebihan_pembayaran_supplier_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    protected function casts(): array
    {
        return ['jumlah_kelebihan' => 'decimal:2', 'tanggal' => 'date'];
    }
}
