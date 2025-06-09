<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Actualización de reparación' }}</title>
</head>
<body>
<h2>Hola, {{ $repair->client->name }}!</h2>
<p>{{ $message }}</p>
<p>Detalles de la reparación:</p>
<ul>
    <li>Número de ticket: {{ $repair->ticket_number }}</li>
    <li>Descripción: {{ $repair->description }}</li>
    <li>Estado: {{ $repair->status }}</li>
</ul>
<p>¡Gracias por confiar en nosotros!</p>
</body>
</html>
