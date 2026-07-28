@extends('layouts.app')

@section('title', 'Reporte de ventas | Santo Remedio')
@section('page-title', 'Reporte de ventas')
@section('page-subtitle', 'Resumen y detalle de ventas por sucursal')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Reporte de ventas</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Resumen de ventas registradas en el rango seleccionado.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.ventas.exportar-csv', request()->query()) }}"
                class="btn-primary"
            >
                Exportar Excel
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                Volver a reportes
            </a>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Filtros</h3>

    <form method="GET" action="{{ route('reportes.ventas') }}" class="filter-bar">
        <div class="form-group">
            <label>Fecha inicio</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
        </div>

        <div class="form-group">
            <label>Fecha fin</label>
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
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
            </button>

            <a href="{{ route('reportes.ventas') }}" class="btn-secondary" title="Limpiar filtros">
                <i class="bi bi-x-circle"></i>
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ventas completadas</span>
        <h3>{{ $resumen['ventas_completadas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Total completadas</span>
        <h3>{{ number_format($resumen['total_completadas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Ventas anuladas</span>
        <h3>{{ $resumen['ventas_anuladas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Total anulado</span>
        <h3>{{ number_format($resumen['total_anuladas'], 2) }} Bs</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Descuentos aplicados</span>
        <h3>{{ number_format($resumen['descuentos'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total general válido</span>
        <h3>{{ number_format($resumen['total_general'], 2) }} Bs</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de ventas</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Productos / Promociones</th>
                    <th>Vendedor</th>
                    <th>Método</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ventas as $venta)
                    <tr>
                        <td>{{ $venta->numero_venta }}</td>

                        <td>{{ $venta->fecha_hora?->format('d/m/Y H:i') }}</td>

                        <td>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</td>

                        <td>
                            @forelse ($venta->detalles as $detalle)
                                <div style="margin-bottom:4px;">
                                    <strong>
                                        {{ $detalle->productoPresentacion->nombre_mostrado
                                            ?? $detalle->producto->nombre_comercial
                                            ?? 'Producto' }}
                                    </strong>

                                    <br>

                                    <small style="color:#6B7280;">
                                        Cant: {{ $detalle->cantidad }}
                                        |
                                        Total: {{ number_format($detalle->subtotal, 2) }} Bs
                                    </small>
                                </div>
                            @empty
                            @endforelse

                            @foreach ($venta->promociones as $promo)
                                <div style="margin-bottom:4px;">
                                    <strong style="color:#4C1D95;">
                                        PROMO: {{ $promo->promocion->nombre ?? 'Promoción' }}
                                    </strong>

                                    <br>

                                    <small style="color:#6B7280;">
                                        Cant: {{ $promo->cantidad }}
                                        |
                                        Total: {{ number_format($promo->subtotal, 2) }} Bs
                                    </small>
                                </div>
                            @endforeach

                            @if ($venta->detalles->count() === 0 && $venta->promociones->count() === 0)
                                <span style="color:#6B7280;">Sin detalle</span>
                            @endif
                        </td>

                        <td>{{ $venta->usuario->nombre ?? '-' }}</td>

                        <td>
                            @forelse ($venta->pagos as $pago)
                                <span class="badge badge-soft">
                                    {{ $pago->metodoPago->nombre ?? '-' }}
                                </span>
                            @empty
                                -
                            @endforelse
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
                                <a href="{{ route('ventas.show', $venta) }}"
                                class="icon-action icon-action-primary"
                                title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('ventas.recibo', $venta) }}"
                                class="icon-action icon-action-print"
                                target="_blank"
                                title="Imprimir recibo">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6B7280;">
                            No hay ventas registradas en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $ventas->links() }}
    </div>
</div>

@endsection