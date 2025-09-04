<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ strtoupper(substr(md5($transaksi->id . $transaksi->created_at), 0, 6)) }}-{{ $transaksi->id }}</title>
    <style>
        body {
            font-family: monospace, sans-serif;
            font-size: 10px;
            width: 58mm;
            padding: 5px;
            margin: 0 auto;
            color: #000;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        td {
            padding: 2px 0;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
    </style>
</head>
<body>

    <div class="text-center">
        <strong>{{ config('app.name') }}</strong><br>
        {{ 'Jl. Tropodo' }}<br>
        {{ $telepon_toko ?? '08xxxxxxxxxx' }}
    </div>

    <div class="line"></div>

    <div>
        <strong>No. Transaksi:</strong> {{ $transaksi->id }}<br>
        <strong>Tanggal:</strong> {{ Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}<br>
        <strong>Kasir:</strong> {{ $transaksi->users->name ?? '-' }}
    </div>

    <div class="line"></div>

    <table>
        <tbody>
            @foreach($transaksi->menus as $menu)
            <tr>
                <td colspan="2">{{ $menu->nama_item }}</td>
            </tr>
            <tr>
                <td>{{ $menu->pivot->jumlah_pesanan }} x Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($menu->pivot->jumlah_pesanan * $menu->harga, 0, ',', '.') }}</td>
            </tr>
            @if($menu->pivot->catatan_pesanan)
            <tr>
                <td colspan="2" style="font-size:9px;color:#666;">Note: {{ $menu->pivot->catatan_pesanan }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table>
        <tbody>
            <tr>
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($transaksi->total_pembayaran, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="line"></div>

    <div class="text-center" style="margin-top:10px;">
        *** TERIMA KASIH ***<br>
        {{ config('app.name') }}
    </div>

</body>
</html>
