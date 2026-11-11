<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $transaksi->kode_transaksi }}</title>
    <style>
        @page { margin: 8px 10px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .center { text-align: center; }
        .brand { font-size: 15px; font-weight: bold; letter-spacing: 1px; }
        .muted { color: #555; }
        hr { border: 0; border-top: 1px dashed #888; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .items th { border-bottom: 1px solid #bbb; text-align: left; }
        .items .num { text-align: right; }
        .total { font-size: 12px; font-weight: bold; }
        .footer { margin-top: 8px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="center">
        <div class="brand">{{ config('app.name') }}</div>
        <div class="muted">Sistem Laundry Kiloan &amp; Satuan</div>
    </div>

    <hr>

    <table>
        <tr><td>Kode</td><td class="right">{{ $transaksi->kode_transaksi }}</td></tr>
        <tr><td>Tanggal Masuk</td><td class="right">{{ $transaksi->tanggal_masuk?->format('d/m/Y H:i') }}</td></tr>
        @if ($transaksi->tanggal_selesai)
            <tr><td>Selesai</td><td class="right">{{ $transaksi->tanggal_selesai->format('d/m/Y H:i') }}</td></tr>
        @endif
        <tr><td>Kasir</td><td class="right">{{ $transaksi->user?->name }}</td></tr>
    </table>

    <hr>

    <table>
        <tr><td>Pelanggan</td><td class="right">{{ $transaksi->pelanggan?->nama }}</td></tr>
        <tr><td>No. HP</td><td class="right">{{ $transaksi->pelanggan?->no_hp }}</td></tr>
    </table>

    <hr>

    <table class="items">
        <thead>
            <tr>
                <th>Layanan</th>
                <th class="num">Jml</th>
                <th class="num">Harga</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi->detail as $item)
                <tr>
                    <td>{{ $item->layanan?->nama_layanan }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $item->jumlah_unit, 2, ',', '.'), '0'), ',') }}</td>
                    <td class="num">{{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <table>
        <tr class="total">
            <td>Total Bayar</td>
            <td class="right">Rp {{ number_format((float) $transaksi->total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pembayaran</td>
            <td class="right">{{ $transaksi->status_pembayaran->getLabel() }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td class="right">{{ $transaksi->status_laundry->getLabel() }}</td>
        </tr>
    </table>

    <div class="footer center muted">
        Terima kasih. Simpan struk ini sebagai bukti pengambilan.
    </div>
</body>
</html>
