<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de baja {{ $bajaInventario->numero_baja ?? $bajaInventario->id }}</title>

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

        .subtitulo {
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
        }

        .dato {
            font-size: 12px;
            margin: 3px 0;
        }

        .separador {
            border-top: 1px dashed #9CA3AF;
            margin: 10px 0;
        }

        .estado-registrado {
            color: #166534;
            font-weight: bold;
        }

        .estado-anulado {
            color: #991B1B;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        .right {
            text-align: right;
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

    <a href="{{ route('bajas-inventario.show', $bajaInventario) }}" class="btn btn-secondary">
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

        <div class="subtitulo">COMPROBANTE DE BAJA DE INVENTARIO</div>
    </div>

    <div class="separador"></div>

    <div class="dato">
        <strong>N° baja:</strong>
        {{ $bajaInventario->numero_baja ?? 'BAJ-' . str_pad($bajaInventario->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="dato">
        <strong>Fecha:</strong>
        {{ $bajaInventario->created_at?->format('d/m/Y H:i') }}
    </div>

    <div class="dato">
        <strong>Sucursal:</strong>
        {{ $bajaInventario->sucursal->nombre ?? '-' }}
    </div>

    <div class="dato">
        <strong>Usuario:</strong>
        {{ $bajaInventario->usuario->nombre ?? '-' }}
    </div>

    <div class="dato">
        <strong>Estado:</strong>

        @if ($bajaInventario->estado === 'registrado')
            <span class="estado-registrado">Registrado</span>
        @else
            <span class="estado-anulado">Anulado</span>
        @endif
    </div>

    <div class="separador"></div>

    <div class="dato"><strong>Producto:</strong></div>

    <div class="dato">
        {{ $bajaInventario->producto->nombre_comercial ?? '-' }}
    </div>

    @if ($bajaInventario->producto?->nombre_generico)
        <div class="dato">
            <strong>Genérico:</strong>
            {{ $bajaInventario->producto->nombre_generico }}
        </div>
    @endif

    @if ($bajaInventario->producto?->concentracion)
        <div class="dato">
            <strong>Concentración:</strong>
            {{ $bajaInventario->producto->concentracion }}
        </div>
    @endif

    @if ($bajaInventario->producto?->laboratorio)
        <div class="dato">
            <strong>Laboratorio:</strong>
            {{ $bajaInventario->producto->laboratorio->nombre }}
        </div>
    @endif

    <div class="separador"></div>

    <table>
        <tr>
            <td><strong>Lote</strong></td>
            <td class="right">{{ $bajaInventario->lote->numero_lote ?? 'Sin lote' }}</td>
        </tr>

        @if ($bajaInventario->lote?->fecha_vencimiento)
            <tr>
                <td><strong>Vencimiento</strong></td>
                <td class="right">{{ $bajaInventario->lote->fecha_vencimiento->format('d/m/Y') }}</td>
            </tr>
        @endif

        <tr>
            <td><strong>Motivo</strong></td>
            <td class="right">
                @if ($bajaInventario->motivo === 'vencimiento')
                    Vencimiento
                @elseif ($bajaInventario->motivo === 'danado')
                    Dañado
                @elseif ($bajaInventario->motivo === 'perdido')
                    Perdido
                @elseif ($bajaInventario->motivo === 'ajuste_autorizado')
                    Ajuste autorizado
                @else
                    Otro
                @endif
            </td>
        </tr>

        <tr>
            <td><strong>Cantidad retirada</strong></td>
            <td class="right">{{ $bajaInventario->cantidad }}</td>
        </tr>

        <tr>
            <td><strong>Stock anterior</strong></td>
            <td class="right">{{ $bajaInventario->stock_anterior }}</td>
        </tr>

        <tr>
            <td><strong>Stock nuevo</strong></td>
            <td class="right">{{ $bajaInventario->stock_nuevo }}</td>
        </tr>
    </table>

    @if ($bajaInventario->observacion)
        <div class="separador"></div>

        <div class="dato">
            <strong>Observación:</strong>
            {{ $bajaInventario->observacion }}
        </div>
    @endif

    @if ($bajaInventario->estado === 'anulado')
        <div class="separador"></div>

        <div class="dato"><strong>Datos de anulación:</strong></div>

        <div class="dato">
            <strong>Anulado por:</strong>
            {{ $bajaInventario->usuarioAnulacion->nombre ?? '-' }}
        </div>

        <div class="dato">
            <strong>Fecha anulación:</strong>
            {{ $bajaInventario->fecha_anulacion?->format('d/m/Y H:i') }}
        </div>

        @if ($bajaInventario->motivo_anulacion)
            <div class="dato">
                <strong>Motivo:</strong>
                {{ $bajaInventario->motivo_anulacion }}
            </div>
        @endif
    @endif

    <div class="separador"></div>

    <div class="center dato">
        Documento interno de control de inventario.
    </div>
</div>

</body>
</html>