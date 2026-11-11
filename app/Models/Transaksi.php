<?php

namespace App\Models;

use App\Enums\StatusLaundry;
use App\Enums\StatusPembayaran;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('transaksi')]
#[Fillable([
    'kode_transaksi',
    'pelanggan_id',
    'user_id',
    'tanggal_masuk',
    'tanggal_selesai',
    'status_laundry',
    'status_pembayaran',
    'total_bayar',
])]
class Transaksi extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'status_laundry' => StatusLaundry::class,
            'status_pembayaran' => StatusPembayaran::class,
            'total_bayar' => 'decimal:2',
        ];
    }

    /**
     * Recalculate and persist the header total from the stored items.
     */
    public function syncTotal(): void
    {
        $this->forceFill([
            'total_bayar' => round((float) $this->detail()->sum('subtotal'), 2),
        ])->save();
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<DetailTransaksi, $this>
     */
    public function detail(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
