<?php

namespace App\Enums;

enum StatusLaundry: string
{
    case Baru = 'baru';
    case Dihitung = 'dihitung';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Diambil = 'diambil';
}
