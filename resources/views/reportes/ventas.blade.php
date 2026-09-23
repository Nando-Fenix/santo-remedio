@extends('layouts.app')

@section('title', 'Reporte de ventas | Santo Remedio')
@section('page-title', 'Reporte de ventas')
@section('page-subtitle', 'Resumen y detalle de ventas por sucursal')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-receipt"></i>
                Reporte de ventas
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
                href="{{ route('reportes.ventas.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Exportar
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Reportes
            </a>
        </div>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.ventas') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="completada" @selected($estado === 'completada')>
                        Completadas
                    </option>
                    <option value="anulada" @selected($estado === 'anulada')>
                        Anuladas
                    </option>
                </select>
            </div>

            <div class="form-group filter-search">
                <label>Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="N° venta, cliente, producto o método"
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.ventas') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip">
        <div class="report-mini-stat">
            <span>Completadas</span>
            <strong>{{ $resumen['ventas_completadas'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Total completadas</span>
            <strong>{{ number_format($resumen['total_completadas'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Anuladas</span>
            <strong>{{ $resumen['ventas_anuladas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Total anulado</span>
            <strong>{{ number_format($resumen['total_anuladas'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Descuentos</span>
            <strong>{{ number_format($resumen['descuentos'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Total válido</span>
            <strong>{{ number_format($resumen['total_general'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de ventas
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-sales-table">
                <thead>
                    <tr>
                        <th>N° venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Productos / promociones</th>
                        <th>Vendedor</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($ventas as $venta)
                        <tr>
                            <td>
                                <strong>{{ $venta->numero_venta }}</strong>
                            </td>

                            <td>
                                {{ $venta->fecha_hora?->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $venta->fecha_hora?->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                {{ $venta->cliente->nombre ?? 'Consumidor final' }}
                            </td>

                            <td>
                                <div class="report-products-list">
                                    @foreach ($venta->detalles->take(2) as $detalle)
                                        <div class="report-product-item">
                                            <strong>
                                                {{ $detalle->productoPresentacion->nombre_mostrado
                                                    ?? $detalle->producto->nombre_comercial
                                                    ?? 'Producto' }}
                                            </strong>

                                            <small>
                                                Cant: {{ $detalle->cantidad }}
                                                |
                                                {{ number_format($detalle->subtotal, 2) }} Bs
                                            </small>
                                        </div>
                                    @endforeach

                                    @foreach ($venta->promociones->take(2) as $promo)
                                        <div class="report-product-item report-promo-item">
                                            <strong>
                                                PROMO: {{ $promo->promocion->nombre ?? 'Promoción' }}
                                            </strong>

                                            <small>
                                                Cant: {{ $promo->cantidad }}
                                                |
                                                {{ number_format($promo->subtotal, 2) }} Bs
                                            </small>
                                        </div>
                                    @endforeach

                                    @php
                                        $totalDetalles = $venta->detalles->count();
                                        $totalPromos = $venta->promociones->count();
                                        $totalItems = $totalDetalles + $totalPromos;
                                        $itemsMostrados = min($totalDetalles, 2) + min($totalPromos, 2);
                                        $restantes = $totalItems - $itemsMostrados;
                                    @endphp

                                    @if ($restantes > 0)
                                        <small class="report-more-items">
                                            + {{ $restantes }} item(s) más
                                        </small>
                                    @endif

                                    @if ($totalItems === 0)
                                        <span style="color:#6B7280;">Sin detalle</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                {{ $venta->usuario->nombre ?? '-' }}
                            </td>

                            <td>
                                <div class="payment-badges">
                                    @forelse ($venta->pagos as $pago)
                                        <span class="badge badge-soft">
                                            {{ $pago->metodoPago->nombre ?? '-' }}
                                        </span>
                                    @empty
                                        -
                                    @endforelse
                                </div>
                            </td>

                            <td>
                                <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                            </td>

                            <td>
                                @if ($venta->estado === 'completada')
                                    <span class="badge badge-success">Completada</span>
                                @else
                                    <span class="badge badge-danger">Anulada</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route('ventas.show', $venta) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver detalle"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <button
                                        type="button"
                                        class="icon-action icon-action-print"
                                        onclick="abrirModalImpresion('{{ route('ventas.recibo', $venta) }}')"
                                        title="Imprimir recibo"
                                    >
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-table-message">
                                No hay ventas registradas en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $ventas->links() }}
        </div>
    </div>

</div>

@endsection