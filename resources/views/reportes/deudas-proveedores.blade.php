@extends('layouts.app')

@section('title', 'Deudas a proveedores | Santo Remedio')
@section('page-title', 'Deudas a proveedores')
@section('page-subtitle', 'Control de compras pendientes de pago')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Deudas a proveedores</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Compras pendientes de pago agrupadas por proveedor.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.deudas-proveedores.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.deudas-proveedores') }}">
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
                <label>Proveedor</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Buscar por nombre, NIT o teléfono"
                >
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.deudas-proveedores') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Compras pendientes</span>
        <h3>{{ $resumen['cantidad_deudas'] }}</h3>
    </div>

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
        <h3>{{ number_format($resumen['total_deuda'], 2) }} Bs</h3>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Resumen por proveedor</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>NIT</th>
                    <th>Teléfono</th>
                    <th>Compras pendientes</th>
                    <th>Total comprado</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($deudasPorProveedor as $deuda)
                    <tr>
                        <td>{{ $deuda['proveedor']->nombre ?? '-' }}</td>
                        <td>{{ $deuda['proveedor']->nit ?? '-' }}</td>
                        <td>{{ $deuda['proveedor']->telefono ?? '-' }}</td>
                        <td>{{ $deuda['cantidad_compras'] }}</td>
                        <td>{{ number_format($deuda['total_comprado'], 2) }} Bs</td>
                        <td>{{ number_format($deuda['total_pagado'], 2) }} Bs</td>
                        <td>
                            <strong>{{ number_format($deuda['saldo_pendiente'], 2) }} Bs</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay deudas pendientes en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de compras pendientes</h3>

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
                    <th>Ver</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td>{{ $compra->numero_compra ?? $compra->id }}</td>
                        <td>{{ $compra->fecha_compra?->format('d/m/Y H:i') }}</td>
                        <td>{{ $compra->proveedor->nombre ?? '-' }}</td>
                        <td>{{ number_format($compra->total, 2) }} Bs</td>
                        <td>{{ number_format($compra->monto_pagado, 2) }} Bs</td>
                        <td>
                            <strong>{{ number_format($compra->saldo_pendiente, 2) }} Bs</strong>
                        </td>

                        <td>
                            <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay compras pendientes en este rango.
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
