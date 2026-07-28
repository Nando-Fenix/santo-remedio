<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo {{ $venta->numero_venta }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 0;
            background: #F3F4F6;
        }

        .recibo {
            width: 80mm;
            margin: 20px auto;
            background: white;
            padding: 14px;
            border: 1px solid #E5E7EB;
        }

        .center {
            text-align: center;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .dato {
            font-size: 12px;
            margin: 2px 0;
        }

        .separador {
            border-top: 1px dashed #9CA3AF;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #D1D5DB;
            padding-bottom: 4px;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
        }

        .acciones {
            width: 80mm;
            margin: 20px auto;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 9px 14px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary {
            background: #4C1D95;
            color: white;
        }

        .btn-secondary {
            background: #E5E7EB;
            color: #111827;
        }

        @media print {
            body {
                background: white;
            }

            .acciones {
                display: none;
            }

            .recibo {
                margin: 0;
                border: none;
                width: 80mm;
            }
        }
    </style>
</head>
<body>

<div class="acciones">
    <button onclick="window.print()" class="btn btn-primary">
        Imprimir
    </button>

    <a href="{{ route('ventas.show', $venta) }}" class="btn btn-secondary">
        Volver
    </a>
</div>

<div class="recibo">
    <div class="center">
        @if ($configuracion->logo)
            <img
                src="{{ asset('storage/' . $configuracion->logo) }}"
                alt="Logo"
                style="max-width:60px; max-height:60px; margin-bottom:6px;"
            >
        @endif
        <div class="titulo">{{ $configuracion->nombre_farmacia }}</div>

        @if ($configuracion->nit)
            <div class="dato">NIT: {{ $configuracion->nit }}</div>
        @endif

        @if ($configuracion->direccion)
            <div class="dato">{{ $configuracion->direccion }}</div>
        @endif

        @if ($configuracion->telefono)
            <div class="dato">Tel: {{ $configuracion->telefono }}</div>
        @endif

        @if ($configuracion->ciudad)
            <div class="dato">{{ $configuracion->ciudad }}</div>
        @endif
    </div>

    <div class="separador"></div>

    <div class="dato"><strong>Recibo:</strong> {{ $venta->numero_venta }}</div>
    <div class="dato"><strong>Fecha:</strong> {{ $venta->fecha_hora?->format('d/m/Y H:i') }}</div>
    <div class="dato"><strong>Vendedor:</strong> {{ $venta->usuario->nombre ?? '-' }}</div>
    <div class="dato"><strong>Sucursal:</strong> {{ $venta->sucursal->nombre ?? '-' }}</div>
    <div class="dato"><strong>Cliente:</strong> {{ $venta->cliente->nombre ?? 'Consumidor final' }}</div>

    <div class="separador"></div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="right">Cant.</th>
                <th class="right">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($venta->detalles as $detalle)
                <tr>
                    <td>
                        {{ $detalle->productoPresentacion->nombre_mostrado
                            ?? $detalle->producto->nombre_comercial
                            ?? 'Producto' }}
                        <br>
                        <small>
                            {{ $detalle->productoPresentacion->presentacion->nombre ?? '' }}
                            | P/U: {{ number_format($detalle->precio_unitario, 2) }}
                        </small>
                    </td>

                    <td class="right">{{ $detalle->cantidad }}</td>
                    <td class="right">
                        {{ number_format($detalle->subtotal, 2) }} {{ $configuracion->moneda }}
                    </td>
                </tr>
            @endforeach

            @foreach ($venta->promociones as $promo)
                <tr>
                    <td>
                        PROMO: {{ $promo->promocion->nombre ?? 'Promoción' }}
                        <br>
                        <small>P/U: {{ number_format($promo->precio_unitario, 2) }}</small>
                    </td>

                    <td class="right">{{ $promo->cantidad }}</td>
                    <td class="right">
                        {{ number_format($promo->subtotal, 2) }} {{ $configuracion->moneda }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separador"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ number_format($venta->subtotal, 2) }} {{ $configuracion->moneda }}</td>
        </tr>

        <tr>
            <td>Descuento</td>
            <td class="right">{{ number_format($venta->descuento_total, 2) }} {{ $configuracion->moneda }}</td>
        </tr>

        <tr>
            <td class="total">TOTAL</td>
            <td class="right total">{{ number_format($venta->total, 2) }} {{ $configuracion->moneda }}</td>
        </tr>
    </table>

    <div class="separador"></div>

    <div class="dato"><strong>Método de pago:</strong></div>

    @forelse ($venta->pagos as $pago)
        <div class="dato">
            {{ $pago->metodoPago->nombre ?? '-' }}:
            {{ number_format($pago->monto, 2) }} {{ $configuracion->moneda }}
        </div>
    @empty
        <div class="dato">Sin pagos registrados.</div>
    @endforelse

    <div class="separador"></div>

    @if ($configuracion->mensaje_recibo)
        <div class="center dato">
            {{ $configuracion->mensaje_recibo }}
        </div>
    @else
        <div class="center dato">
            Gracias por su compra.
        </div>
    @endif
</div>

</body>
</html>