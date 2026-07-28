@extends('layouts.app')

@section('title', 'Clientes frecuentes | Santo Remedio')
@section('page-title', 'Clientes frecuentes')
@section('page-subtitle', 'Ranking de clientes por compras realizadas')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Clientes frecuentes</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Clientes con mayor cantidad de compras e ingresos generados.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.clientes-frecuentes.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.clientes-frecuentes') }}">
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
                <label>Buscar cliente</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, CI/NIT o teléfono"
                >
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.clientes-frecuentes') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Clientes distintos</span>
        <h3>{{ $resumen['clientes_distintos'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Ventas con cliente</span>
        <h3>{{ $resumen['ventas_con_cliente'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Total comprado</span>
        <h3>{{ number_format($resumen['total_comprado'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Ticket promedio</span>
        <h3>{{ number_format($resumen['ticket_promedio_general'], 2) }} Bs</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Ranking de clientes</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>CI/NIT</th>
                    <th>Teléfono</th>
                    <th>Compras</th>
                    <th>Total comprado</th>
                    <th>Ticket promedio</th>
                    <th>Última compra</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($clientesPaginados as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['cliente']->nombre ?? '-' }}</strong>
                        </td>
                        <td>{{ $item['cliente']->ci_nit ?? '-' }}</td>
                        <td>{{ $item['cliente']->telefono ?? '-' }}</td>
                        <td>{{ $item['cantidad_compras'] }}</td>
                        <td>
                            <strong>{{ number_format($item['total_comprado'], 2) }} Bs</strong>
                        </td>
                        <td>{{ number_format($item['ticket_promedio'], 2) }} Bs</td>
                        <td>{{ $item['ultima_compra']?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay clientes frecuentes en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $clientesPaginados->links() }}
    </div>
</div>

@endsection