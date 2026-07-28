@extends('layouts.app')

@section('title', 'Reporte de promociones | Santo Remedio')
@section('page-title', 'Reporte de promociones')
@section('page-subtitle', 'Promociones vendidas, ingresos y productos descontados')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <h2 style="margin-top: 0; color: #4C1D95;">Reporte de promociones</h2>
    <p style="margin:6px 0 0; color:#4B5563;">
        Sucursal:
        <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
    </p>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a
            href="{{ route('reportes.promociones.exportar-csv', request()->query()) }}"
            class="btn-primary"
        >
            Exportar Excel
        </a>

        <a href="{{ route('reportes.index') }}" class="btn-secondary">
            Volver a reportes
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.promociones') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Filtrar
            </button>

            <a href="{{ route('reportes.promociones') }}" class="btn-secondary">
                Limpiar
            </a>

            @if (auth()->user()->tienePermiso('ver_reportes'))
                <a href="{{ route('reportes.index') }}" class="btn-secondary">
                    Volver a reportes
                </a>
            @endif
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Total generado</span>
        <h3>{{ number_format($totalGenerado, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Promociones vendidas</span>
        <h3>{{ $totalPromocionesVendidas }}</h3>
    </div>

    <div class="stat-card">
        <span>Registros encontrados</span>
        <h3>{{ $promocionesVendidas->count() }}</h3>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Resumen por promoción</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Promoción</th>
                    <th>Cantidad vendida</th>
                    <th>Total generado</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($resumenPromociones as $resumen)
                    <tr>
                        <td>
                            <strong>{{ $resumen->promocion->nombre ?? 'Promoción eliminada' }}</strong>
                        </td>

                        <td>{{ $resumen->cantidad_vendida }}</td>

                        <td>
                            <strong>{{ number_format($resumen->total_generado, 2) }} Bs</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #6B7280;">
                            No hay promociones vendidas en este rango de fechas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos descontados por promociones</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Forma</th>
                    <th>Unidades descontadas</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($productosDescontados as $producto)
                    <tr>
                        <td>
                            <strong>
                                {{ $producto->productoPresentacion->nombre_mostrado ?? $producto->producto->nombre_comercial ?? '-' }}
                            </strong>

                            @if ($producto->producto?->laboratorio || $producto->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
                                    @if ($producto->producto?->laboratorio)
                                        {{ $producto->producto->laboratorio->nombre }}
                                    @endif

                                    @if ($producto->producto?->laboratorio && $producto->producto?->concentracion)
                                        |
                                    @endif

                                    @if ($producto->producto?->concentracion)
                                        {{ $producto->producto->concentracion }}
                                    @endif
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ $producto->productoPresentacion->presentacion->nombre ?? '-' }}
                        </td>

                        <td>
                            <strong>{{ $producto->total_unidades_descontadas }}</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #6B7280;">
                            No hay productos descontados por promociones en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">Detalle de ventas con promociones</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Venta</th>
                    <th>Fecha</th>
                    <th>Promoción</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Vendedor</th>
                    <th>Sucursal</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($promocionesVendidas as $detallePromo)
                    <tr>
                        <td>
                            <strong>{{ $detallePromo->venta->numero_venta ?? '-' }}</strong>
                        </td>

                        <td>
                            {{ $detallePromo->venta?->fecha_hora?->format('d/m/Y H:i') ?? '-' }}
                        </td>

                        <td>
                            {{ $detallePromo->promocion->nombre ?? 'Promoción eliminada' }}
                        </td>

                        <td>{{ $detallePromo->cantidad }}</td>

                        <td>
                            <strong>{{ number_format($detallePromo->subtotal, 2) }} Bs</strong>
                        </td>

                        <td>{{ $detallePromo->venta->usuario->nombre ?? '-' }}</td>

                        <td>{{ $detallePromo->venta->sucursal->nombre ?? '-' }}</td>

                        <td>
                            @if ($detallePromo->venta && auth()->user()->tienePermiso('ver_ventas'))
                                <a href="{{ route('ventas.show', $detallePromo->venta) }}" class="btn-secondary">
                                    Ver venta
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #6B7280;">
                            No hay ventas con promociones en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection