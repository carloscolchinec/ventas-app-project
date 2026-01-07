<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Nueva venta registrada</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    /* Soporte básico móvil */
    @media (max-width:600px) {
      .wrap {
        padding: 16px !important
      }

      .pill {
        display: inline-block !important;
        margin-top: 8px !important
      }

      .h2 {
        font-size: 18px !important
      }

      .p {
        font-size: 14px !important
      }
    }
  </style>
</head>

<body style="margin:0;padding:24px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111;">

  <!-- Contenedor con margen y borde suave -->
  <div class="wrap"
    style="max-width:680px;margin:0 auto;background:#fff;border:1px solid #eceff3;border-radius:14px;overflow:hidden;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.03)">

    <!-- Header -->
    <table role="presentation" width="100%" style="border-collapse:collapse;margin-bottom:14px">
      <tr>
        <td style="text-align:left;vertical-align:middle">
          <img src="{{ $message->embed(public_path('images/logo_socnet.png')) }}" alt="SOCNET"
            style="height:40px;display:block">
        </td>
        <td style="text-align:right;vertical-align:middle">
          <span class="pill"
            style="background:#B00020;color:#fff;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700;display:inline-block">
            Nueva venta
          </span>
        </td>
      </tr>
    </table>

    <!-- Separador fino -->
    <div style="height:1px;background:#f0f2f5;margin:6px 0 18px 0"></div>

    <!-- Saludo y lead -->
    <h2 class="h2" style="margin:0 0 8px 0;color:#0f172a;font-size:20px;line-height:1.35">
      Hola {{ $vendedorNombre }},
    </h2>
    <p class="p" style="margin:0 0 14px 0;font-size:15px;line-height:1.6;color:#334155">
      Has registrado una nueva venta en <strong>SOCNET</strong>. Aquí tienes el resumen:
    </p>

    <!-- Tarjeta de datos -->
    <div style="border:1px solid #e8ecf1;border-radius:12px;padding:16px;background:#fafbfd;margin:12px 0">
      <table role="presentation" width="100%"
        style="border-collapse:separate;border-spacing:0 6px;font-size:14px;color:#0f172a">
        <tr>
          <td style="width:42%;color:#6b7280">Cliente</td>
          <td style="font-weight:600">{{ $cliente->nombres }} {{ $cliente->apellidos }}</td>
        </tr>
        <tr>
          <td style="color:#6b7280">Identificación</td>
          <td>{{ $cliente->identificacion }}</td>
        </tr>
        <tr>
          <td style="color:#6b7280">Serie/Contrato</td>
          <td>{{ $cliente->serie_contrato ?? '-' }}</td>
        </tr>
        <tr>
          <td style="color:#6b7280">Plan</td>
          <td>
            {{ $cliente->plan }}
            @if($plan && $plan->velocidad) — {{ $plan->velocidad }} @endif
            @if(isset($cliente->servicios_adicionales['zappingTV']) && $cliente->servicios_adicionales['zappingTV']) +
            ZappingTV @endif
          </td>
        </tr>
        <tr>
          <td style="color:#6b7280">Establecimiento</td>
          <td>{{ $cliente->establecimiento ?? '-' }}</td>
        </tr>
        <tr>
          <td style="color:#6b7280">Fecha de registro</td>
          <td>{{ optional($cliente->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
          <td style="color:#6b7280">Estado</td>
          <td>
            @php $estados = ['VI' => 'Venta Ingresada', 'VR' => 'Venta Rechazada', 'IEP' => 'Instalación en Proceso']; @endphp
            {{ $estados[$cliente->estado] ?? $cliente->estado }}
          </td>
        </tr>
      </table>
    </div>

    <!-- Notas -->
    <!-- <p class="p" style="margin:12px 0 0 0;font-size:14px;line-height:1.6;color:#334155">
      @if($plan)
        Recuerda programar la instalación correspondiente al plan <strong>{{ $cliente->plan }}</strong>.
      @else
        Recuerda verificar los detalles del plan asignado.
      @endif
    </p> -->
    <p class="p" style="margin:8px 0 16px 0;font-size:14px;line-height:1.6;color:#334155">
      Si lo necesitas, el contrato en PDF se adjunta a este correo.
    </p>

    <!-- Separador -->
    <div style="height:1px;background:#f0f2f5;margin:18px 0"></div>

    <!-- Footer -->
    <div style="text-align:center;color:#94a3b8;font-size:12px;line-height:1.6">
      © SOCNET 2025 · Gestión de Ventas
    </div>
  </div>
</body>

</html>