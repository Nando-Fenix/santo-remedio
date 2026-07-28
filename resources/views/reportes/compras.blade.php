@extends('layouts.app')

@section('title', 'Reporte de compras | Santo Remedio')
@section('page-title', 'Reporte de compras')
@section('page-subtitle', 'Resumen y detalle de compras por sucursal')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Reporte de compras</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Control de compras, pagos pendientes y compras anuladas.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.compras.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.compras') }}">
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
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente" @selected($estado === 'pendiente')>Pendientes</option>
                    <option value="pagada" @selected($estado === 'pagada')>Pagadas</option>
                    <option value="anulada" @selected($estado === 'anulada')>Anuladas</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.compras') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Compras registradas</span>
        <h3>{{ $resumen['cantidad_compras'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Compras pagadas</span>
        <h3>{{ $resumen['compras_pagadas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Compras pendientes</span>
        <h3>{{ $resumen['compras_pendientes'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Compras anuladas</span>
        <h3>{{ $resumen['compras_anuladas'] }}</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Total comprado</span>
        <h3>{{ number_format($resumen['total_comprado'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total pagado</span>
        <h3>{{ number_format($resumen['total_pagado'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Saldo pendiente</span>
        <h3>{{ number_format($resumen['saldo_pendiente'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total anulado</span>
        <h3>{{ number_format($resumen['total_anulado'], 2) }} Bs</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de compras</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° compra</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th>Ver</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td>{{ $compra->numero_compra ?? $compra->id }}</td>
                        <td>{{ $compra->fecha_compra?->format('d/m/Y H:i') }}</td>
                        <td>{{ $compra->proveedor->nombre ?? '-' }}</td>
                        <td><strong>{{ number_format($compra->total, 2) }} Bs</strong></td>
                        <td>{{ number_format($compra->monto_pagado, 2) }} Bs</td>
                        <td>{{ number_format($compra->saldo_pendiente, 2) }} Bs</td>

                        <td>
                            @if ($compra->estado === 'pagada')
                                <span class="badge badge-success">Pagada</span>
                            @elseif ($compra->estado === 'pendiente')
                                <span class="badge badge-warning">Pendiente</span>
                            @else
                                <span class="badge badge-danger">Anulada</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color:#6B7280;">
                            No hay compras registradas en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $compras->links() }}
    </div>
</div>

@endsection