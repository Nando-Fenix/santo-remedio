@extends('layouts.app')

@section('title', 'Inventario crítico | Santo Remedio')
@section('page-title', 'Inventario crítico')
@section('page-subtitle', 'Productos agotados, con stock bajo, próximos a vencer y vencidos')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Inventario crítico</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Productos que requieren atención inmediata.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.inventario-critico.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.inventario-critico') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, genérico, concentración o laboratorio"
                >
            </div>

            <div class="form-group">
                <label>Tipo de alerta</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="agotados" @selected($tipo === 'agotados')>Agotados</option>
                    <option value="stock_bajo" @selected($tipo === 'stock_bajo')>Stock bajo</option>
                    <option value="proximos_vencer" @selected($tipo === 'proximos_vencer')>Próximos a vencer</option>
                    <option value="vencidos" @selected($tipo === 'vencidos')>Vencidos</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.inventario-critico') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Agotados</span>
        <h3>{{ $resumen['agotados'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Stock bajo</span>
        <h3>{{ $resumen['stock_bajo'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Próximos a vencer</span>
        <h3>{{ $resumen['proximos_vencer'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Vencidos</span>
        <h3>{{ $resumen['vencidos'] }}</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de productos críticos</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Laboratorio</th>
                    <th>Stock</th>
                    <th>Mínimo</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Alerta</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($inventarios as $inventario)
                    @php
                        $fechaVencimiento = $inventario->lote?->fecha_vencimiento;
                        $alerta = 'Normal';

                        if ($inventario->stock_actual <= 0) {
                            $alerta = 'Agotado';
                        } elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                            $alerta = 'Stock bajo';
                        }

                        if ($fechaVencimiento && $inventario->stock_actual > 0) {
                            if ($fechaVencimiento->lt(now()->startOfDay())) {
                                $alerta = 'Vencido';
                            } elseif ($fechaVencimiento->between(now()->startOfDay(), now()->addDays(30)->endOfDay())) {
                                $alerta = 'Próximo a vencer';
                            }
                        }
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
                        <td>{{ $inventario->lote->numero_lote ?? '-' }}</td>
                        <td>{{ $fechaVencimiento?->format('d/m/Y') ?? '-' }}</td>

                        <td>
                            @if ($alerta === 'Agotado' || $alerta === 'Vencido')
                                <span class="badge badge-danger">{{ $alerta }}</span>
                            @elseif ($alerta === 'Stock bajo')
                                <span class="badge badge-warning">{{ $alerta }}</span>
                            @elseif ($alerta === 'Próximo a vencer')
                                <span class="badge badge-soft">{{ $alerta }}</span>
                            @else
                                <span class="badge badge-success">{{ $alerta }}</span>
                            @endif
                        </td>

                        <td>
                            @if ($inventario->stock_actual > 0 && $fechaVencimiento && $fechaVencimiento->lt(now()->startOfDay()))
                                <a href="{{ route('bajas-inventario.create', ['inventario_id' => $inventario->id]) }}" class="btn-danger">
                                    Dar baja
                                </a>
                            @elseif ($inventario->stock_actual > 0 && $fechaVencimiento && $fechaVencimiento->between(now()->startOfDay(), now()->addDays(30)->endOfDay()))
                                <a href="{{ route('promociones.create', ['inventario_id' => $inventario->id]) }}" class="btn-primary">
                                    Crear promoción
                                </a>
                            @else
                                <a href="{{ route('inventario.index') }}" class="btn-secondary">
                                    Inventario
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color:#6B7280;">
                            No hay productos críticos según los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $inventarios->links() }}
    </div>
</div>

@endsection