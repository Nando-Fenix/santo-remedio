@extends('layouts.app')

@section('title', 'Movimientos de caja | Santo Remedio')
@section('page-title', 'Movimientos de caja')
@section('page-subtitle', 'Historial de ingresos, egresos y apertura de caja')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Historial de movimientos</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Sucursal: <strong>{{ $sucursal->nombre }}</strong>
            </p>
        </div>

        <a href="{{ route('caja.index') }}" class="btn-secondary">
            Volver a caja
        </a>
    </div>

    @if (!auth()->user()->tienePermiso('ver_caja'))
        <div class="alert-danger">
            No tiene permiso para ver movimientos de caja.
        </div>
    @else

    <form method="GET" action="{{ route('caja.movimientos') }}" style="margin-bottom: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Tipo de movimiento</label>
                <select name="tipo_movimiento">
                    <option value="">Todos</option>
                    <option value="apertura" @selected(($tipoMovimiento ?? '') === 'apertura')>Apertura</option>
                    <option value="ingreso" @selected(($tipoMovimiento ?? '') === 'ingreso')>Ingreso</option>
                    <option value="egreso" @selected(($tipoMovimiento ?? '') === 'egreso')>Egreso</option>
                    <option value="reembolso" @selected(($tipoMovimiento ?? '') === 'reembolso')>Reembolso</option>
                    <option value="ajuste" @selected(($tipoMovimiento ?? '') === 'ajuste')>Ajuste</option>
                    <option value="cierre" @selected(($tipoMovimiento ?? '') === 'cierre')>Cierre</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Filtrar
            </button>

            <a href="{{ route('caja.movimientos') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Método</th>
                    <th>Monto</th>
                    <th>Usuario</th>
                    <th>Venta</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($movimiento->tipo_movimiento === 'ingreso')
                                <span class="badge badge-success">Ingreso</span>
                            @elseif ($movimiento->tipo_movimiento === 'egreso')
                                <span class="badge badge-danger">Egreso</span>
                            @elseif ($movimiento->tipo_movimiento === 'apertura')
                                <span class="badge badge-soft">Apertura</span>
                            @else
                                <span class="badge badge-warning">
                                    {{ ucfirst(str_replace('_', ' ', $movimiento->tipo_movimiento)) }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $movimiento->metodoPago->nombre ?? '-' }}</td>
                        <td>
                            <strong>{{ number_format($movimiento->monto, 2) }} Bs</strong>
                        </td>
                        <td>{{ $movimiento->usuario->nombre ?? '-' }}</td>
                        <td>
                            @if ($movimiento->venta && auth()->user()->tienePermiso('ver_ventas'))
                                <a href="{{ route('ventas.show', $movimiento->venta) }}" class="btn-secondary">
                                    {{ $movimiento->venta->numero_venta }}
                                </a>
                            @elseif ($movimiento->venta)
                                {{ $movimiento->venta->numero_venta }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $movimiento->descripcion ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6B7280;">
                            No hay movimientos de caja registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $movimientos->links() }}
    </div>
    @endif
</div>

@endsection