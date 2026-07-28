@extends('layouts.app')

@section('title', 'Utilidad estimada | Santo Remedio')
@section('page-title', 'Utilidad estimada')
@section('page-subtitle', 'Ganancia aproximada según precio de venta y precio de compra')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Utilidad estimada</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Estimación de utilidad usando ventas completadas y precio de compra registrado.
            </p>

            <p style="margin:6px 0 0; color:#92400E;">
                Nota: este reporte es referencial. La precisión depende del precio de compra registrado en cada presentación.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.utilidad-estimada.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.utilidad-estimada') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, genérico, concentración o laboratorio"
                >
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.utilidad-estimada') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Productos vendidos</span>
        <h3>{{ $resumen['productos_distintos'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Total venta</span>
        <h3>{{ number_format($resumen['total_venta'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Costo estimado</span>
        <h3>{{ number_format($resumen['costo_estimado'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Utilidad estimada</span>
        <h3>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(1, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Margen general estimado</span>
        <h3>{{ number_format($resumen['margen_general'], 2) }}%</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de utilidad por producto</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Laboratorio</th>
                    <th>Presentación</th>
                    <th>Venta</th>
                    <th>Costo estimado</th>
                    <th>Utilidad</th>
                    <th>Margen</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($utilidadesPaginadas as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['producto']->nombre_comercial ?? '-' }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                {{ $item['producto']->nombre_generico ?? '' }}
                                {{ $item['producto']->concentracion ?? '' }}
                            </small>
                        </td>

                        <td>{{ $item['producto']->laboratorio->nombre ?? '-' }}</td>
                        <td>{{ $item['presentacion']->presentacion->nombre ?? '-' }}</td>

                        <td>
                            {{ number_format($item['total_venta'], 2) }} Bs
                            <br>
                            <small style="color:#6B7280;">
                                Cant.: {{ $item['cantidad_vendida'] }}
                            </small>
                        </td>

                        <td>{{ number_format($item['costo_estimado'], 2) }} Bs</td>

                        <td>
                            <strong>{{ number_format($item['utilidad_estimada'], 2) }} Bs</strong>
                        </td>

                        <td>
                            @if ($item['margen'] < 0)
                                <span class="badge badge-danger">{{ number_format($item['margen'], 2) }}%</span>
                            @elseif ($item['margen'] < 20)
                                <span class="badge badge-warning">{{ number_format($item['margen'], 2) }}%</span>
                            @else
                                <span class="badge badge-success">{{ number_format($item['margen'], 2) }}%</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay ventas completadas en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $utilidadesPaginadas->links() }}
    </div>
</div>

@endsection