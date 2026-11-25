<?php

namespace App\Filament\Widgets;

use App\Enums\StatusLaundry;
use App\Enums\StatusPembayaran;
use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikLaundry extends BaseWidget
{
    protected function getStats(): array
    {
        $hariIni = Transaksi::whereDate('tanggal_masuk', today());

        $pendapatan = Transaksi::whereDate('tanggal_masuk', today())
            ->where('status_pembayaran', StatusPembayaran::Lunas)
            ->sum('total_bayar');

        return [
            Stat::make('Transaksi Hari Ini', (clone $hariIni)->count())
                ->description('Total transaksi masuk hari ini')
                ->icon('heroicon-o-receipt-percent')
                ->color('primary'),
            Stat::make('Pendapatan Hari Ini', 'Rp '.number_format((float) $pendapatan, 0, ',', '.'))
                ->description('Hanya transaksi lunas')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Sedang Diproses', Transaksi::where('status_laundry', StatusLaundry::Diproses)->count())
                ->description('Laundry dalam pengerjaan')
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),
            Stat::make('Belum Bayar', Transaksi::where('status_pembayaran', StatusPembayaran::BelumBayar)->count())
                ->description('Transaksi menunggu pembayaran')
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger'),
        ];
    }
}
