@extends('layouts.app')

@section('title', 'Movimientos de inventario | Santo Remedio')
@section('page-title', 'Movimientos de inventario')
@section('page-subtitle', 'Historial de entradas, salidas y ajustes de stock')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-arrow-left-right"></i>
                Movimientos de inventario
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
                href="{{ route('reportes.movimientos-inventario.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.movimientos-inventario.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.movimientos-inventario') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="entrada" @selected($tipo === 'entrada')>Entrada</option>
                    <option value="salida" @selected($tipo === 'salida')>Salida</option>
                    <option value="ajuste" @selected($tipo === 'ajuste')>Ajuste</option>
                </select>
            </div>

            <div class="form-group filter-search">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, genérico, laboratorio..."
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.movimientos-inventario') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="critical-summary-strip">
        <div class="critical-mini-stat critical-soft">
            <span>Total movimientos</span>
            <strong>{{ $resumen['total_movimientos'] }}</strong>
        </div>

        <div class="critical-mini-stat">
            <span>Entradas</span>
            <strong>{{ $resumen['entradas'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-danger">
            <span>Salidas</span>
            <strong>{{ $resumen['salidas'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-warning">
            <span>Ajustes</span>
            <strong>{{ $resumen['ajustes'] }}</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-clock-history"></i>
                Detalle de movimientos
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-inventory-movements-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Tipo</th>
                        <th>Cant.</th>
                        <th>Stock</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($movimientos as $movimiento)
                        <tr>
                            <td>
                                {{ $movimiento->created_at?->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $movimiento->created_at?->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $movimiento->producto->nombre_comercial ?? '-' }}</strong>

                                @if (($movimiento->producto->nombre_generico ?? null) || ($movimiento->producto->concentracion ?? null))
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $movimiento->producto->nombre_generico ?? '' }}
                                        {{ $movimiento->producto->concentracion ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $movimiento->lote->numero_lote ?? '-' }}
                            </td>

                            <td>
                                @if ($movimiento->tipo_movimiento === 'entrada')
                                    <span class="badge badge-success">Entrada</span>
                                @elseif ($movimiento->tipo_movimiento === 'salida')
                                    <span class="badge badge-danger">Salida</span>
                                @else
                                    <span class="badge badge-warning">Ajuste</span>
                                @endif
                            </td>

                            <td>
                                @if ($movimiento->tipo_movimiento === 'entrada')
                                    <strong class="movement-positive">
                                        +{{ $movimiento->cantidad }}
                                    </strong>
                                @elseif ($movimiento->tipo_movimiento === 'salida')
                                    <strong class="movement-negative">
                                        -{{ $movimiento->cantidad }}
                                    </strong>
                                @else
                                    <strong>
                                        {{ $movimiento->cantidad }}
                                    </strong>
                                @endif
                            </td>

                            <td>
                                <div class="movement-stock-box">
                                    <span>{{ $movimiento->stock_anterior }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                    <strong>{{ $movimiento->stock_nuevo }}</strong>
                                </div>
                            </td>

                            <td>
                                {{ $movimiento->usuario->nombre ?? '-' }}
                            </td>

                            <td>
                                <span class="movement-reason">
                                    {{ $movimiento->motivo ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay movimientos registrados en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $movimientos->links() }}
        </div>
    </div>

</div>

@endsection