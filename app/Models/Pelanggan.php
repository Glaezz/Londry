<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('pelanggan')]
#[Fillable(['nama', 'no_hp', 'alamat'])]
class Pelanggan extends Model
{
    /**
     * @return HasMany<Transaksi, $this>
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'pelanggan_id');
    }
}
