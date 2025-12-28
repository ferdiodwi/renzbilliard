<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; color: #1a56db; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        .summary { margin-bottom: 30px; width: 100%; border-collapse: collapse; }
        .summary td { padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center; }
        .summary .label { font-size: 12px; color: #64748b; display: block; margin-bottom: 5px; }
        .summary .value { font-size: 18px; font-weight: bold; color: #0f172a; }
        
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; }
        
        table.data { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.data th, table.data td { padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        table.data th { background-color: #f1f5f9; font-weight: bold; color: #475569; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-right { text-align: right !important; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 10px; border-radius: 4px; background: #e2e8f0; }
        
        .breakdown { margin-bottom: 30px; display: table; width: 100%; }
        .breakdown-item { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RenzBilliard</h1>
        <p>Laporan Pendapatan & Statistik</p>
        <p>Periode: <strong>{{ \Carbon\Carbon::parse($data['period']['start'])->format('d M Y') }}</strong> - <strong>{{ \Carbon\Carbon::parse($data['period']['end'])->format('d M Y') }}</strong></p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Total Pendapatan</span>
                <span class="value" style="color: #16a34a">Rp {{ number_format($data['summary']['revenue'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Total Sesi</span>
                <span class="value">{{ $data['summary']['sessions'] }}</span>
            </td>
            <td>
                <span class="label">Total Transaksi</span>
                <span class="value">{{ $data['summary']['transactions'] }}</span>
            </td>
        </tr>
    </table>
    
    <div class="breakdown">
        <div class="breakdown-item">
            <div class="section-title">Sumber Pendapatan</div>
            <table class="data">
                <tr>
                    <td>Sewa Meja</td>
                    <td class="text-right">Rp {{ number_format($data['breakdown']['billiard'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>F&B / Produk</td>
                    <td class="text-right">Rp {{ number_format($data['breakdown']['fnb'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <div class="breakdown-item">
             <div class="section-title">Metode Pembayaran</div>
             <table class="data">
                 @foreach($data['payment_methods'] as $pm)
                 <tr>
                     <td style="text-transform: capitalize">{{ $pm->payment_method }}</td>
                     <td class="text-right">{{ $pm->count }}x</td>
                     <td class="text-right">Rp {{ number_format($pm->total, 0, ',', '.') }}</td>
                 </tr>
                 @endforeach
             </table>
        </div>
    </div>

    <div class="section-title">Rincian Transaksi</div>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['transactions'] as $tx)
            <tr>
                <td>{{ $tx->paid_at->format('d/m/Y H:i') }}</td>
                <td>{{ $tx->invoice_number }}</td>
                <td>{{ $tx->cashier->name ?? '-' }}</td>
                <td style="text-transform: capitalize">{{ $tx->payment_method }}</td>
                <td class="text-right">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
             @if(count($data['transactions']) == 0)
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: #888;">Tidak ada data transaksi</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
