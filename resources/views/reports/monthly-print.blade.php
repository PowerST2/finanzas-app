<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte {{ $month }}</title>
    <style>
        body { font-family: sans-serif; color: #111827; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()">Imprimir / guardar PDF</button>
    <h1>Reporte mensual {{ $month }}</h1>
    <table>
        <thead><tr><th>Fecha</th><th>Tipo</th><th>Billetera</th><th>Categoria</th><th>Monto</th><th>Descripcion</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->date->format('d/m/y - H:i') }}</td>
                    <td>{{ $row->type }}</td>
                    <td>{{ $row->wallet?->name }}</td>
                    <td>{{ $row->category?->name }}</td>
                    <td>{{ number_format((float) $row->amount, 2) }}</td>
                    <td>{{ $row->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
