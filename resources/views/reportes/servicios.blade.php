@extends('layouts.app')

@section('title', 'Reporte de servicios | Santo Remedio')
@section('page-title', 'Reporte de servicios')
@section('page-subtitle', 'Ingresos, atenciones e insumos consumidos por servicios de farmacia')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-heart-pulse"></i>
                Reporte de servicios
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
                href="{{ route('reportes.servicios.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel atenciones
            </a>

            <a
                href="{{ route('reportes.servicios.exportar-insumos-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-box-seam"></i>
                Excel insumos
            </a>

            <a
                href="{{ route('reportes.servicios.exportar-csv', request()->query()) }}"
                class="btn-secondary"
            >
                <i class="bi bi-file-earmark-excel"></i>
                CSV atenciones
            </a>

            <a
                href="{{ route('reportes.servicios.exportar-insumos-csv', request()->query()) }}"
                class="btn-secondary"
            >
                <i class="bi bi-box-arrow-down"></i>
                CSV insumos
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Reportes
            </a>
        </div>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.servicios') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Servicio</label>
                <select name="servicio_farmacia_id">
                    <option value="">Todos</option>
                    @foreach ($servicios as $servicio)
                        <option value="{{ $servicio->id }}" @selected((string) $servicioId === (string) $servicio->id)>
                            {{ $servicio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="completada" @selected($estado === 'completada')>Completada</option>
                    <option value="anulada" @selected($estado === 'anulada')>Anulada</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.servicios') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip report-summary-five">
        <div class="report-mini-stat">
            <span>Total atenciones</span>
            <strong>{{ $resumen['total_atenciones'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Completadas</span>
            <strong>{{ $resumen['atenciones_completadas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Anuladas</span>
            <strong>{{ $resumen['atenciones_anuladas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Ingresos válidos</span>
            <strong>{{ number_format($resumen['ingresos_completados'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Monto anulado</span>
            <strong>{{ number_format($resumen['ingresos_anulados'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="report-two-columns">
        <div class="compact-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-graph-up"></i>
                    Servicios más vendidos
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table report-services-ranking-table">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Cant.</th>
                            <th>Atenc.</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($serviciosMasVendidos as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->servicio->nombre ?? '-' }}</strong>
                                </td>

                                <td>
                                    <span class="reorder-suggested">
                                        {{ $item->cantidad_total }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->atenciones_total }}
                                </td>

                                <td>
                                    <strong class="report-money">
                                        {{ number_format($item->ingresos_total, 2) }} Bs
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-table-message">
                                    No hay servicios completados en el rango seleccionado.
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
                    <i class="bi bi-box-seam"></i>
                    Insumos consumidos
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table report-service-supplies-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Presentación</th>
                            <th>Unid.</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($insumosConsumidos as $item)
                            <tr>
                                <td>
                                    <strong>
                                        {{ $item->productoPresentacion->nombre_mostrado ?? $item->producto->nombre_comercial ?? '-' }}
                                    </strong>

                                    @if ($item->producto?->laboratorio || $item->producto?->concentracion)
                                        <br>
                                        <small style="color:#6B7280;">
                                            @if ($item->producto?->laboratorio)
                                                {{ $item->producto->laboratorio->nombre }}
                                            @endif

                                            @if ($item->producto?->laboratorio && $item->producto?->concentracion)
                                                |
                                            @endif

                                            @if ($item->producto?->concentracion)
                                                {{ $item->producto->concentracion }}
                                            @endif
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-soft">
                                        {{ $item->productoPresentacion->presentacion->nombre ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="stock-out-quantity">
                                        {{ $item->unidades_total }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-table-message">
                                    No hay insumos consumidos en el rango seleccionado.
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
                Detalle de atenciones
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-services-detail-table">
                <thead>
                    <tr>
                        <th>N° atención</th>
                        <th>Fecha</th>
                        <th>Servicio</th>
                        <th>Cliente</th>
                        <th>Cant.</th>
                        <th>Total</th>
                        <th>Método</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($atenciones as $atencion)
                        <tr>
                            <td>
                                <strong>
                                    {{ $atencion->numero_atencion ?? 'SER-' . str_pad($atencion->id, 6, '0', STR_PAD_LEFT) }}
                                </strong>
                            </td>

                            <td>
                                {{ $atencion->fecha_hora->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $atencion->fecha_hora->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                {{ $atencion->servicio->nombre ?? '-' }}
                            </td>

                            <td>
                                {{ $atencion->cliente->nombre ?? 'Consumidor final' }}
                            </td>

                            <td>
                                {{ $atencion->cantidad }}
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($atencion->total, 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                <span class="badge badge-soft">
                                    {{ $atencion->metodoPago->nombre ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $atencion->usuario->nombre ?? '-' }}
                            </td>

                            <td>
                                @if ($atencion->estado === 'completada')
                                    <span class="badge badge-success">Completada</span>
                                @else
                                    <span class="badge badge-danger">Anulada</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route('atenciones-servicio.show', $atencion) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver atención"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <button
                                        type="button"
                                        class="icon-action icon-action-print"
                                        onclick="abrirModalImpresion('{{ route('atenciones-servicio.recibo', $atencion) }}')"
                                        title="Imprimir recibo"
                                    >
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty-table-message">
                                No hay atenciones en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $atenciones->links() }}
        </div>
    </div>

</div>

@endsection