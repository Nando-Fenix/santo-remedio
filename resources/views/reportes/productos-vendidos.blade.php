@extends('layouts.app')

@section('title', 'Productos vendidos | Santo Remedio')
@section('page-title', 'Productos vendidos')
@section('page-subtitle', 'Ranking de productos con mayor venta e ingreso')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Productos vendidos</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Ranking de productos vendidos por cantidad e ingresos generados.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.productos-vendidos.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.productos-vendidos') }}">
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

            <a href="{{ route('reportes.productos-vendidos') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Productos distintos</span>
        <h3>{{ $resumen['productos_distintos'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad vendida</span>
        <h3>{{ $resumen['cantidad_vendida'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Unidades descontadas</span>
        <h3>{{ $resumen['unidades_vendidas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Total generado</span>
        <h3>{{ number_format($resumen['total_generado'], 2) }} Bs</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Ranking de productos</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Laboratorio</th>
                    <th>Presentación</th>
                    <th>Cantidad vendida</th>
                    <th>Unidades descontadas</th>
                    <th>Descuento</th>
                    <th>Total generado</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($productosPaginados as $item)
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
                        <td>{{ $item['cantidad_vendida'] }}</td>
                        <td>{{ $item['unidades_vendidas'] }}</td>
                        <td>{{ number_format($item['descuento'], 2) }} Bs</td>
                        <td>
                            <strong>{{ number_format($item['total'], 2) }} Bs</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay productos vendidos en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $productosPaginados->links() }}
    </div>
</div>

@endsection