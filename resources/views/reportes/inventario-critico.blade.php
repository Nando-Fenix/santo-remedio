@extends('layouts.app')

@section('title', 'Inventario crítico | Santo Remedio')
@section('page-title', 'Inventario crítico')
@section('page-subtitle', 'Productos agotados, con stock bajo, próximos a vencer y vencidos')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-exclamation-triangle"></i>
                Inventario crítico
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Productos que requieren atención inmediata.
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.inventario-critico.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.inventario-critico.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.inventario-critico') }}" class="filter-bar report-filter-bar">
            <div class="form-group filter-search">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, genérico, concentración o laboratorio"
                >
            </div>

            <div class="form-group">
                <label>Tipo de alerta</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="agotados" @selected($tipo === 'agotados')>Agotados</option>
                    <option value="stock_bajo" @selected($tipo === 'stock_bajo')>Stock bajo</option>
                    <option value="proximos_vencer" @selected($tipo === 'proximos_vencer')>Próximos a vencer</option>
                    <option value="vencidos" @selected($tipo === 'vencidos')>Vencidos</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.inventario-critico') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="critical-summary-strip">
        <div class="critical-mini-stat critical-danger">
            <span>Agotados</span>
            <strong>{{ $resumen['agotados'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-warning">
            <span>Stock bajo</span>
            <strong>{{ $resumen['stock_bajo'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-soft">
            <span>Próximos a vencer</span>
            <strong>{{ $resumen['proximos_vencer'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-danger">
            <span>Vencidos</span>
            <strong>{{ $resumen['vencidos'] }}</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de productos críticos
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-critical-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Stock</th>
                        <th>Mín.</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Alerta</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($inventarios as $inventario)
                        @php
                            $fechaVencimiento = $inventario->lote?->fecha_vencimiento;
                            $alerta = 'Normal';

                            if ($inventario->stock_actual <= 0) {
                                $alerta = 'Agotado';
                            } elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                                $alerta = 'Stock bajo';
                            }

                            if ($fechaVencimiento && $inventario->stock_actual > 0) {
                                if ($fechaVencimiento->lt(now()->startOfDay())) {
                                    $alerta = 'Vencido';
                                } elseif ($fechaVencimiento->between(now()->startOfDay(), now()->addDays(30)->endOfDay())) {
                                    $alerta = 'Próximo a vencer';
                                }
                            }
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $inventario->producto->nombre_comercial ?? '-' }}</strong>

                                @if (($inventario->producto->nombre_generico ?? null) || ($inventario->producto->concentracion ?? null))
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $inventario->producto->nombre_generico ?? '' }}
                                        {{ $inventario->producto->concentracion ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $inventario->producto->laboratorio->nombre ?? '-' }}
                            </td>

                            <td>
                                @if ($inventario->stock_actual <= 0)
                                    <strong class="critical-stock-danger">
                                        {{ $inventario->stock_actual }}
                                    </strong>
                                @elseif ($inventario->stock_actual <= $inventario->stock_minimo)
                                    <strong class="critical-stock-warning">
                                        {{ $inventario->stock_actual }}
                                    </strong>
                                @else
                                    <strong>
                                        {{ $inventario->stock_actual }}
                                    </strong>
                                @endif
                            </td>

                            <td>
                                {{ $inventario->stock_minimo }}
                            </td>

                            <td>
                                {{ $inventario->lote->numero_lote ?? '-' }}
                            </td>

                            <td>
                                {{ $fechaVencimiento?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                @if ($alerta === 'Agotado' || $alerta === 'Vencido')
                                    <span class="badge badge-danger">{{ $alerta }}</span>
                                @elseif ($alerta === 'Stock bajo')
                                    <span class="badge badge-warning">{{ $alerta }}</span>
                                @elseif ($alerta === 'Próximo a vencer')
                                    <span class="badge badge-soft">{{ $alerta }}</span>
                                @else
                                    <span class="badge badge-success">{{ $alerta }}</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    @if ($inventario->stock_actual > 0 && $fechaVencimiento && $fechaVencimiento->lt(now()->startOfDay()))
                                        <a
                                            href="{{ route('bajas-inventario.create', ['inventario_id' => $inventario->id]) }}"
                                            class="icon-action icon-action-danger"
                                            title="Dar baja"
                                        >
                                            <i class="bi bi-dash-circle"></i>
                                        </a>
                                    @elseif ($inventario->stock_actual > 0 && $fechaVencimiento && $fechaVencimiento->between(now()->startOfDay(), now()->addDays(30)->endOfDay()))
                                        <a
                                            href="{{ route('promociones.create', ['inventario_id' => $inventario->id]) }}"
                                            class="icon-action icon-action-primary"
                                            title="Crear promoción"
                                        >
                                            <i class="bi bi-tags"></i>
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('inventario.index') }}"
                                            class="icon-action icon-action-edit"
                                            title="Ver inventario"
                                        >
                                            <i class="bi bi-box-seam"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay productos críticos según los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $inventarios->links() }}
        </div>
    </div>

</div>

@endsection