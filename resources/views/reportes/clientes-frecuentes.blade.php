@extends('layouts.app')

@section('title', 'Clientes frecuentes | Santo Remedio')
@section('page-title', 'Clientes frecuentes')
@section('page-subtitle', 'Ranking de clientes por compras realizadas')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-people"></i>
                Clientes frecuentes
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
                href="{{ route('reportes.clientes-frecuentes.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.clientes-frecuentes.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.clientes-frecuentes') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group filter-search">
                <label>Buscar cliente</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, CI/NIT o teléfono"
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.clientes-frecuentes') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Clientes distintos</span>
            <strong>{{ $resumen['clientes_distintos'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Ventas con cliente</span>
            <strong>{{ $resumen['ventas_con_cliente'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Total comprado</span>
            <strong>{{ number_format($resumen['total_comprado'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Ticket promedio</span>
            <strong>{{ number_format($resumen['ticket_promedio_general'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-ol"></i>
                Ranking de clientes
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-frequent-clients-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>CI/NIT</th>
                        <th>Teléfono</th>
                        <th>Compras</th>
                        <th>Total comprado</th>
                        <th>Ticket prom.</th>
                        <th>Última compra</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($clientesPaginados as $item)
                        <tr>
                            <td>
                                <span class="ranking-number">
                                    {{ $clientesPaginados->firstItem() + $loop->index }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $item['cliente']->nombre ?? '-' }}</strong>
                            </td>

                            <td>
                                {{ $item['cliente']->ci_nit ?? '-' }}
                            </td>

                            <td>
                                {{ $item['cliente']->telefono ?? '-' }}
                            </td>

                            <td>
                                <span class="reorder-suggested">
                                    {{ $item['cantidad_compras'] }}
                                </span>
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($item['total_comprado'], 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                {{ number_format($item['ticket_promedio'], 2) }} Bs
                            </td>

                            <td>
                                {{ $item['ultima_compra']?->format('d/m/Y') ?? '-' }}
                                @if ($item['ultima_compra'])
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $item['ultima_compra']->format('H:i') }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay clientes frecuentes en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $clientesPaginados->links() }}
        </div>
    </div>

</div>

@endsection