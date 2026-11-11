<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusLaundry: string implements HasColor, HasLabel
{
    case Baru = 'baru';
    case Dihitung = 'dihitung';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Diambil = 'diambil';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dihitung => 'Dihitung',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Diambil => 'Diambil',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baru => 'gray',
            self::Dihitung => 'info',
            self::Diproses => 'warning',
            self::Selesai => 'success',
            self::Diambil => 'primary',
        };
    }

    /**
     * The next step in the workflow, or null when the order is picked up.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Baru => self::Dihitung,
            self::Dihitung => self::Diproses,
            self::Diproses => self::Selesai,
            self::Selesai => self::Diambil,
            self::Diambil => null,
        };
    }
}
