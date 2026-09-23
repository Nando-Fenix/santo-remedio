@extends('layouts.app')

@section('title', 'Reporte de promociones | Santo Remedio')
@section('page-title', 'Reporte de promociones')
@section('page-subtitle', 'Promociones vendidas, ingresos y productos descontados')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-tags"></i>
                Reporte de promociones
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Periodo:
                <strong>{{ $fechaInicio }}</strong>
                al
                <strong>{{ $fechaFin }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.promociones.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.promociones.exportar-csv', request()->query()) }}"
                class="btn-secondary"
            >
                <i class="bi bi-file-earmark-excel"></i>
                CSV
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Reportes
            </a>
        </div>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.promociones') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Filtrar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.promociones') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip report-summary-three">
        <div class="report-mini-stat report-mini-total">
            <span>Total generado</span>
            <strong>{{ number_format($totalGenerado, 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Promociones vendidas</span>
            <strong>{{ $totalPromocionesVendidas }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Registros encontrados</span>
            <strong>{{ $promocionesVendidas->count() }}</strong>
        </div>
    </div>

    <div class="report-two-columns">
        <div class="compact-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-graph-up"></i>
                    Resumen por promoción
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table report-promotions-summary-table">
                    <thead>
                        <tr>
                            <th>Promoción</th>
                            <th>Cant.</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($resumenPromociones as $resumen)
                            <tr>
                                <td>
                                    <strong>{{ $resumen->promocion->nombre ?? 'Promoción eliminada' }}</strong>
                                </td>

                                <td>
                                    <span class="reorder-suggested">
                                        {{ $resumen->cantidad_vendida }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="report-money">
                                        {{ number_format($resumen->total_generado, 2) }} Bs
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-table-message">
                                    No hay promociones vendidas en este rango de fechas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="compact-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-box-arrow-down"></i>
                    Productos descontados
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table report-promotions-products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Forma</th>
                            <th>Unid.</th>
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
                                        <small style="color:#6B7280;">
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
                                    <span class="badge badge-soft">
                                        {{ $producto->productoPresentacion->presentacion->nombre ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="stock-out-quantity">
                                        {{ $producto->total_unidades_descontadas }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-table-message">
                                    No hay productos descontados por promociones en este rango.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de ventas con promociones
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-promotions-detail-table">
                <thead>
                    <tr>
                        <th>Venta</th>
                        <th>Fecha</th>
                        <th>Promoción</th>
                        <th>Cant.</th>
                        <th>Subtotal</th>
                        <th>Vendedor</th>
                        <th>Sucursal</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($promocionesVendidas as $detallePromo)
                        <tr>
                            <td>
                                <strong>{{ $detallePromo->venta->numero_venta ?? '-' }}</strong>
                            </td>

                            <td>
                                {{ $detallePromo->venta?->fecha_hora?->format('d/m/Y') ?? '-' }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $detallePromo->venta?->fecha_hora?->format('H:i') ?? '' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $detallePromo->promocion->nombre ?? 'Promoción eliminada' }}</strong>
                            </td>

                            <td>
                                {{ $detallePromo->cantidad }}
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($detallePromo->subtotal, 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                {{ $detallePromo->venta->usuario->nombre ?? '-' }}
                            </td>

                            <td>
                                {{ $detallePromo->venta->sucursal->nombre ?? '-' }}
                            </td>

                            <td>
                                <div class="action-group">
                                    @if ($detallePromo->venta && auth()->user()->tienePermiso('ver_ventas'))
                                        <a
                                            href="{{ route('ventas.show', $detallePromo->venta) }}"
                                            class="icon-action icon-action-primary"
                                            title="Ver venta"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <span style="color:#6B7280;">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay ventas con promociones en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection