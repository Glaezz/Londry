<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case BelumBayar = 'belum_bayar';
    case Lunas = 'lunas';
}
