<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JenisLayanan: string implements HasLabel
{
    case Kiloan = 'kiloan';
    case Satuan = 'satuan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kiloan => 'Kiloan',
            self::Satuan => 'Satuan',
        };
    }
}
