@extends('layouts.app')

@section('title', 'Reporte de bajas de inventario | Santo Remedio')
@section('page-title', 'Reporte de bajas de inventario')
@section('page-subtitle', 'Productos retirados del inventario por vencimiento, daño, pérdida u otros motivos')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-archive"></i>
                Bajas de inventario
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal->nombre ?? 'Sin sucursal asignada' }}</strong>
                |
                Productos retirados del stock.
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.bajas-inventario.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.bajas-inventario.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.bajas-inventario') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group filter-search">
                <label>Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="N° baja, producto, genérico, concentración o lote"
                >
            </div>

            <div class="form-group">
                <label>Motivo</label>
                <select name="motivo">
                    <option value="">Todos</option>
                    <option value="vencimiento" @selected(($motivo ?? '') === 'vencimiento')>Vencimiento</option>
                    <option value="danado" @selected(($motivo ?? '') === 'danado')>Dañado</option>
                    <option value="perdido" @selected(($motivo ?? '') === 'perdido')>Perdido</option>
                    <option value="ajuste_autorizado" @selected(($motivo ?? '') === 'ajuste_autorizado')>Ajuste autorizado</option>
                    <option value="otro" @selected(($motivo ?? '') === 'otro')>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="registrado" @selected(($estado ?? '') === 'registrado')>Registrado</option>
                    <option value="anulado" @selected(($estado ?? '') === 'anulado')>Anulado</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.bajas-inventario') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="critical-summary-strip report-summary-two">
        <div class="critical-mini-stat critical-soft">
            <span>Total registros</span>
            <strong>{{ $totalBajas }}</strong>
        </div>

        <div class="critical-mini-stat critical-danger">
            <span>Unidades retiradas válidas</span>
            <strong>{{ $totalUnidades }}</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de bajas
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-stock-outs-table">
                <thead>
                    <tr>
                        <th>N° baja</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Motivo</th>
                        <th>Cant.</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bajas as $baja)
                        <tr>
                            <td>
                                <strong>
                                    {{ $baja->numero_baja ?? 'BAJ-' . str_pad($baja->id, 6, '0', STR_PAD_LEFT) }}
                                </strong>
                            </td>

                            <td>
                                {{ $baja->created_at?->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $baja->created_at?->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $baja->producto->nombre_comercial ?? '-' }}</strong>

                                @if ($baja->producto?->laboratorio || $baja->producto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        @if ($baja->producto?->laboratorio)
                                            {{ $baja->producto->laboratorio->nombre }}
                                        @endif

                                        @if ($baja->producto?->laboratorio && $baja->producto?->concentracion)
                                            |
                                        @endif

                                        @if ($baja->producto?->concentracion)
                                            {{ $baja->producto->concentracion }}
                                        @endif
                                    </small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $baja->lote->numero_lote ?? 'Sin lote' }}</strong>

                                @if ($baja->lote?->fecha_vencimiento)
                                    <br>
                                    <small style="color:#6B7280;">
                                        Vence: {{ $baja->lote->fecha_vencimiento->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if ($baja->motivo === 'vencimiento')
                                    <span class="badge badge-danger">Vencimiento</span>
                                @elseif ($baja->motivo === 'danado')
                                    <span class="badge badge-warning">Dañado</span>
                                @elseif ($baja->motivo === 'perdido')
                                    <span class="badge badge-warning">Perdido</span>
                                @elseif ($baja->motivo === 'ajuste_autorizado')
                                    <span class="badge badge-soft">Ajuste autorizado</span>
                                @else
                                    <span class="badge badge-soft">Otro</span>
                                @endif
                            </td>

                            <td>
                                <strong class="stock-out-quantity">
                                    {{ $baja->cantidad }}
                                </strong>
                            </td>

                            <td>
                                {{ $baja->usuario->nombre ?? '-' }}
                            </td>

                            <td>
                                @if ($baja->estado === 'registrado')
                                    <span class="badge badge-success">Registrado</span>
                                @else
                                    <span class="badge badge-danger">Anulado</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route('bajas-inventario.show', $baja) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver detalle"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                   <button
                                        type="button"
                                        class="icon-action icon-action-print"
                                        onclick="abrirModalImpresion('{{ route('bajas-inventario.recibo', $baja) }}')"
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
                                No hay bajas de inventario en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $bajas->links() }}
        </div>
    </div>

</div>

@endsection