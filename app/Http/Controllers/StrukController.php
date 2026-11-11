<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class StrukController extends Controller
{
    public function __invoke(Transaksi $transaksi): Response
    {
        $transaksi->loadMissing(['pelanggan', 'user', 'detail.layanan']);

        return Pdf::loadView('pdf.struk', ['transaksi' => $transaksi])
            ->setPaper([0, 0, 226.77, 600])
            ->stream('struk-'.$transaksi->kode_transaksi.'.pdf');
    }
}
