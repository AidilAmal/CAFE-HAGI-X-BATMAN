<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { margin-bottom: 4px; }
        p { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d0d0d0; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Laporan Stok Cafe Hagi X Batman Pools</h1>
    <p>Filter: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }} | Tipe: {{ strtoupper($filters['type'] ?? 'semua') }}</p>
    <table>
        <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th>Qty</th><th>Sebelum</th><th>Sesudah</th><th>User</th><th>Note</th></tr></thead>
        <tbody>
            @foreach($movements as $movement)
                <tr>
                    <td>{{ optional($movement->movement_date)->format('d-m-Y') }}</td>
                    <td>{{ $movement->item?->name }}</td>
                    <td>{{ strtoupper($movement->type) }}</td>
                    <td>{{ $movement->qty }}</td>
                    <td>{{ $movement->stock_before }}</td>
                    <td>{{ $movement->stock_after }}</td>
                    <td>{{ $movement->user?->name }}</td>
                    <td>{{ $movement->note }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
