<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .info {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
        }
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #999;
            font-size: 10px;
            color: #666;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RENZ BILLIARD</h1>
        <p>Jl. Contoh Alamat No. 123</p>
        <p>Telp: 0812-3456-7890</p>
    </div>

    <div class="info">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; padding: 2px;"><strong>No. Invoice:</strong></td>
                <td style="border: none; padding: 2px;">{{ $transaction->invoice_number }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px;"><strong>Tanggal:</strong></td>
                <td style="border: none; padding: 2px;">{{ $transaction->paid_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px;"><strong>Kasir:</strong></td>
                <td style="border: none; padding: 2px;">{{ $transaction->cashier->name }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px;"><strong>Pembayaran:</strong></td>
                <td style="border: none; padding: 2px;">{{ strtoupper($transaction->payment_method) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Meja</th>
                <th>Tarif</th>
                <th>Durasi</th>
                <th class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>{{ $item->session->table->table_number }}</td>
                <td>{{ $item->session->rate->name }}</td>
                <td>{{ $item->session->duration_minutes }} menit</td>
                <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Terima kasih atas kunjungan Anda!</p>
        <p>Selamat bermain dan sampai jumpa kembali</p>
    </div>
</body>
</html>
