@extends('layouts.app')

@section('title', 'Productos a reponer | Santo Remedio')
@section('page-title', 'Productos a reponer')
@section('page-subtitle', 'Stock bajo combinado con historial de ventas')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Productos a reponer</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Productos agotados o con stock bajo, priorizados según ventas recientes.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.productos-reponer.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.productos-reponer') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Periodo de análisis</label>
                <select name="dias">
                    <option value="7" @selected($dias == 7)>Últimos 7 días</option>
                    <option value="15" @selected($dias == 15)>Últimos 15 días</option>
                    <option value="30" @selected($dias == 30)>Últimos 30 días</option>
                    <option value="60" @selected($dias == 60)>Últimos 60 días</option>
                    <option value="90" @selected($dias == 90)>Últimos 90 días</option>
                </select>
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

            <a href="{{ route('reportes.productos-reponer') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Productos a reponer</span>
        <h3>{{ $resumen['productos_reponer'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Agotados</span>
        <h3>{{ $resumen['agotados'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Stock bajo</span>
        <h3>{{ $resumen['stock_bajo'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad sugerida total</span>
        <h3>{{ $resumen['cantidad_sugerida_total'] }}</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de reposición sugerida</h3>

    <p style="color:#6B7280;">
        La cantidad sugerida es referencial. Se calcula para alcanzar aproximadamente el doble del stock mínimo.
    </p>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Laboratorio</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                    <th>Vendidas</th>
                    <th>Promedio diario</th>
                    <th>Sugerido</th>
                    <th>Prioridad</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($productosPaginados as $item)
                    @php
                        $inventario = $item['inventario'];
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $inventario->producto->nombre_comercial ?? '-' }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                {{ $inventario->producto->nombre_generico ?? '' }}
                                {{ $inventario->producto->concentracion ?? '' }}
                            </small>
                        </td>

                        <td>{{ $inventario->producto->laboratorio->nombre ?? '-' }}</td>
                        <td>{{ $inventario->stock_actual }}</td>
                        <td>{{ $inventario->stock_minimo }}</td>
                        <td>{{ $item['unidades_vendidas'] }}</td>
                        <td>{{ number_format($item['promedio_diario'], 2) }}</td>
                        <td>
                            <strong>{{ $item['cantidad_sugerida'] }}</strong>
                        </td>

                        <td>
                            @if ($item['prioridad'] === 'Alta')
                                <span class="badge badge-danger">Alta</span>
                            @elseif ($item['prioridad'] === 'Media')
                                <span class="badge badge-warning">Media</span>
                            @else
                                <span class="badge badge-soft">Baja</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color:#6B7280;">
                            No hay productos para reponer según los filtros aplicados.
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