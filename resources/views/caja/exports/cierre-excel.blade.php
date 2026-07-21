<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
        }

        .titulo {
            background: #4C1D95;
            color: #FFFFFF;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            padding: 14px;
        }

        .subtitulo {
            background: #EDE9FE;
            color: #4C1D95;
            font-size: 14px;
            font-weight: bold;
            padding: 8px;
        }

        .seccion {
            background: #F5F3FF;
            color: #4C1D95;
            font-weight: bold;
            font-size: 15px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #D1D5DB;
            padding: 7px;
            font-size: 12px;
        }

        th {
            background: #4C1D95;
            color: #FFFFFF;
            font-weight: bold;
            text-align: center;
        }

        .label {
            background: #F3F4F6;
            font-weight: bold;
        }

        .money {
            text-align: right;
            mso-number-format: "#,##0.00";
        }

        .success {
            background: #DCFCE7;
            color: #166534;
            font-weight: bold;
        }

        .warning {
            background: #FEF3C7;
            color: #92400E;
            font-weight: bold;
        }

        .danger {
            background: #FEE2E2;
            color: #991B1B;
            font-weight: bold;
        }

        .total {
            background: #DDD6FE;
            color: #4C1D95;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>

<body>

<table>
    <tr>
        <td colspan="6" class="titulo">
            SANTO REMEDIO - CIERRE DE CAJA
        </td>
    </tr>

    <tr>
        <td colspan="6" class="subtitulo">
            Reporte generado el {{ now()->format('d/m/Y H:i') }}
        </td>
    </tr>

    <tr><td colspan="6"></td></tr>

    <tr>
        <td colspan="6" class="seccion">DATOS DE CAJA</td>
    </tr>

    <tr>
        <td class="label">Caja ID</td>
        <td>{{ $cajaAbierta->id }}</td>

        <td class="label">Sucursal</td>
        <td>{{ $sucursal->nombre }}</td>

        <td class="label">Turno</td>
        <td>{{ $cajaAbierta->turno->nombre ?? 'Sin turno' }}</td>
    </tr>

    <tr>
        <td class="label">Fecha apertura</td>
        <td>{{ $cajaAbierta->fecha_apertura?->format('d/m/Y H:i') }}</td>

        <td class="label">Estado</td>
        <td>{{ ucfirst($cajaAbierta->estado) }}</td>

        <td class="label">Monto inicial</td>
        <td class="money">{{ number_format($cajaAbierta->monto_inicial, 2, '.', '') }}</td>
    </tr>

    <tr><td colspan="6"></td></tr>

    <tr>
        <td colspan="6" class="seccion">RESUMEN DE INGRESOS</td>
    </tr>

    <tr>
        <th>Concepto</th>
        <th>Efectivo</th>
        <th>QR / Digital</th>
        <th>Total</th>
        <th colspan="2">Observación</th>
    </tr>

    <tr>
        <td class="label">Ventas</td>
        <td class="money">{{ number_format($ventasEfectivo, 2, '.', '') }}</td>
        <td class="money">{{ number_format($ventasQr, 2, '.', '') }}</td>
        <td class="money success">{{ number_format($ingresosVentas, 2, '.', '') }}</td>
        <td colspan="2">Ingresos por ventas completadas</td>
    </tr>

    <tr>
        <td class="label">Servicios</td>
        <td class="money">{{ number_format($serviciosEfectivo, 2, '.', '') }}</td>
        <td class="money">{{ number_format($serviciosQr, 2, '.', '') }}</td>
        <td class="money success">{{ number_format($ingresosServicios, 2, '.', '') }}</td>
        <td colspan="2">Ingresos por servicios completados</td>
    </tr>

    <tr>
        <td class="total">TOTAL INGRESOS VÁLIDOS</td>
        <td class="money total">{{ number_format($ventasEfectivo + $serviciosEfectivo, 2, '.', '') }}</td>
        <td class="money total">{{ number_format($ventasQr + $serviciosQr, 2, '.', '') }}</td>
        <td class="money total">{{ number_format($ingresosValidos, 2, '.', '') }}</td>
        <td colspan="2" class="total">Ventas + servicios</td>
    </tr>

    <tr><td colspan="6"></td></tr>

    <tr>
        <td colspan="6" class="seccion">TOTALES REGISTRADOS EN CAJA</td>
    </tr>

    <tr>
        <td class="label">Total efectivo caja</td>
        <td class="money">{{ number_format($cajaAbierta->total_efectivo, 2, '.', '') }}</td>

        <td class="label">Total QR caja</td>
        <td class="money">{{ number_format($cajaAbierta->total_qr, 2, '.', '') }}</td>

        <td class="label">Total egresos</td>
        <td class="money danger">{{ number_format($cajaAbierta->total_egresos, 2, '.', '') }}</td>
    </tr>

    <tr>
        <td class="label">Total reembolsos</td>
        <td class="money danger">{{ number_format($cajaAbierta->total_reembolsos, 2, '.', '') }}</td>

        <td class="label">Total final caja</td>
        <td class="money total">{{ number_format($cajaAbierta->total_final, 2, '.', '') }}</td>

        <td class="label">Estado caja</td>
        <td>{{ ucfirst($cajaAbierta->estado) }}</td>
    </tr>

    <tr><td colspan="6"></td></tr>

    <tr>
        <td colspan="6" class="seccion">MOVIMIENTOS DE CAJA</td>
    </tr>

    <tr>
        <th>Fecha</th>
        <th>Tipo</th>
        <th colspan="2">Descripción</th>
        <th>Monto</th>
        <th>Usuario ID</th>
    </tr>

    @forelse ($cajaAbierta->movimientos as $movimiento)
        <tr>
            <td>{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>

            <td>
                {{ ucfirst($movimiento->tipo_movimiento) }}
            </td>

            <td colspan="2">
                {{ $movimiento->descripcion }}
            </td>

            <td class="money">
                {{ number_format($movimiento->monto, 2, '.', '') }}
            </td>

            <td>
                {{ $movimiento->usuario_id }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No hay movimientos registrados.</td>
        </tr>
    @endforelse

    <tr><td colspan="6"></td></tr>

    <tr>
        <td colspan="3" class="label">Firma responsable</td>
        <td colspan="3" class="label">Firma administración</td>
    </tr>

    <tr>
        <td colspan="3" style="height: 50px;"></td>
        <td colspan="3" style="height: 50px;"></td>
    </tr>
</table>

</body>
</html>