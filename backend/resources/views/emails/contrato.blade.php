<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrato</title>
</head>
<body>
    <h1>Contrato de Servicio</h1>
    <p><strong>Cliente:</strong> {{ $cliente->nombres }} {{ $cliente->apellidos }}</p>
    <p><strong>Cédula/RUC:</strong> {{ $cliente->identificacion }}</p>
    <p><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
    <p><strong>Correo:</strong> {{ $cliente->correo }}</p>
    <p><strong>Plan:</strong> {{ $cliente->plan }}</p>
</body>
</html>
