@extends('layouts.app')

@section('title', 'Movimientos de inventario | Santo Remedio')
@section('page-title', 'Movimientos de inventario')
@section('page-subtitle', 'Historial de entradas, salidas y ajustes de stock')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Movimientos de inventario</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Historial de entradas, salidas, bajas y ajustes registrados.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.movimientos-inventario.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.movimientos-inventario') }}">
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
                <label>Tipo movimiento</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="entrada" @selected($tipo === 'entrada')>Entrada</option>
                    <option value="salida" @selected($tipo === 'salida')>Salida</option>
                    <option value="ajuste" @selected($tipo === 'ajuste')>Ajuste</option>
                </select>
            </div>

            <div class="form-group">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, genérico, laboratorio..."
                >
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.movimientos-inventario') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Total movimientos</span>
        <h3>{{ $resumen['total_movimientos'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Entradas</span>
        <h3>{{ $resumen['entradas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Salidas</span>
        <h3>{{ $resumen['salidas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Ajustes</span>
        <h3>{{ $resumen['ajustes'] }}</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de movimientos</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Stock anterior</th>
                    <th>Stock nuevo</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>

                        <td>
                            <strong>{{ $movimiento->producto->nombre_comercial ?? '-' }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                {{ $movimiento->producto->nombre_generico ?? '' }}
                                {{ $movimiento->producto->concentracion ?? '' }}
                            </small>
                        </td>

                        <td>{{ $movimiento->lote->numero_lote ?? '-' }}</td>

                        <td>
                            @if ($movimiento->tipo_movimiento === 'entrada')
                                <span class="badge badge-success">Entrada</span>
                            @elseif ($movimiento->tipo_movimiento === 'salida')
                                <span class="badge badge-danger">Salida</span>
                            @else
                                <span class="badge badge-warning">Ajuste</span>
                            @endif
                        </td>

                        <td>{{ $movimiento->cantidad }}</td>
                        <td>{{ $movimiento->stock_anterior }}</td>
                        <td>{{ $movimiento->stock_nuevo }}</td>
                        <td>{{ $movimiento->usuario->nombre ?? '-' }}</td>
                        <td>{{ $movimiento->motivo ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6B7280;">
                            No hay movimientos registrados en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $movimientos->links() }}
    </div>
</div>

@endsection