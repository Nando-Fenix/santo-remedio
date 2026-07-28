@extends('layouts.app')

@section('title', 'Métodos de pago | Santo Remedio')
@section('page-title', 'Métodos de pago')
@section('page-subtitle', 'Resumen de ingresos por forma de pago')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Métodos de pago</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Resumen de ingresos de ventas y servicios agrupados por método de pago.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.metodos-pago.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.metodos-pago') }}">
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
                <label>Método de pago</label>
                <select name="metodo_pago_id">
                    <option value="">Todos</option>
                    @foreach ($metodosPago as $metodo)
                        <option value="{{ $metodo->id }}" @selected($metodoPagoId == $metodo->id)>
                            {{ $metodo->nombre }} - {{ ucfirst($metodo->tipo) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.metodos-pago') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ventas</span>
        <h3>{{ number_format($resumen['total_ventas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Servicios</span>
        <h3>{{ number_format($resumen['total_servicios'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total general</span>
        <h3>{{ number_format($resumen['total_general'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad ventas</span>
        <h3>{{ $resumen['cantidad_ventas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad servicios</span>
        <h3>{{ $resumen['cantidad_servicios'] }}</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Resumen por método</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Tipo</th>
                    <th>Cant. ventas</th>
                    <th>Total ventas</th>
                    <th>Cant. servicios</th>
                    <th>Total servicios</th>
                    <th>Total general</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($resumenPorMetodo as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['metodo']->nombre ?? '-' }}</strong>
                        </td>

                        <td>{{ ucfirst($item['metodo']->tipo ?? '-') }}</td>
                        <td>{{ $item['cantidad_ventas'] }}</td>
                        <td>{{ number_format($item['total_ventas'], 2) }} Bs</td>
                        <td>{{ $item['cantidad_servicios'] }}</td>
                        <td>{{ number_format($item['total_servicios'], 2) }} Bs</td>
                        <td>
                            <strong>{{ number_format($item['total_general'], 2) }} Bs</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay pagos registrados en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection