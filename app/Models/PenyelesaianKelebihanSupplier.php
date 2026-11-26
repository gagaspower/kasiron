<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kelebihan_pembayaran_supplier_id', 'jenis', 'jumlah', 'tanggal', 'referensi', 'catatan', 'dibuat_oleh'])]
class PenyelesaianKelebihanSupplier extends Model
{
    use HasUlids;

    protected $table = 'penyelesaian_kelebihan_supplier';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const JENIS_TITIPAN = 'titipan';

    public const JENIS_PENGEMBALIAN = 'pengembalian';

    public function kelebihan(): BelongsTo
    {
        return $this->belongsTo(KelebihanPembayaranSupplier::class, 'kelebihan_pembayaran_supplier_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2', 'tanggal' => 'date'];
    }
}
