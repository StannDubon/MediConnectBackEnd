<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Exámenes</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Reporte de Exámenes</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Título</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($examenes as $examen)
                <tr>
                    <td>{{ $examen->fecha }}</td>
                    <td>{{ $examen->titulo }}</td>
                    <td>{{ $examen->descripcion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
