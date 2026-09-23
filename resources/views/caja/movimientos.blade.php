@extends('layouts.app')

@section('title', 'Movimientos de caja | Santo Remedio')
@section('page-title', 'Movimientos de caja')
@section('page-subtitle', 'Historial de ingresos, egresos y apertura de caja')

@section('content')

@if (!auth()->user()->tienePermiso('ver_caja'))
    <div class="alert-danger">
        No tiene permiso para ver movimientos de caja.
    </div>
@else

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-list-ul"></i>
                Movimientos de caja
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal->nombre }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            <a href="{{ route('caja.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver a caja
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('caja.movimientos') }}" class="filter-bar">
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

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-funnel"></i>
                Filtrar
            </button>

            <a href="{{ route('caja.movimientos') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table cash-movements-table">
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
                        <td>
                            {{ $movimiento->created_at->format('d/m/Y') }}
                            <br>
                            <small style="color:#6B7280;">
                                {{ $movimiento->created_at->format('H:i') }}
                            </small>
                        </td>

                        <td>
                            @if ($movimiento->tipo_movimiento === 'ingreso')
                                <span class="badge badge-success">Ingreso</span>
                            @elseif ($movimiento->tipo_movimiento === 'egreso')
                                <span class="badge badge-danger">Egreso</span>
                            @elseif ($movimiento->tipo_movimiento === 'apertura')
                                <span class="badge badge-soft">Apertura</span>
                            @elseif ($movimiento->tipo_movimiento === 'reembolso')
                                <span class="badge badge-warning">Reembolso</span>
                            @elseif ($movimiento->tipo_movimiento === 'cierre')
                                <span class="badge badge-info">Cierre</span>
                            @else
                                <span class="badge badge-warning">
                                    {{ ucfirst(str_replace('_', ' ', $movimiento->tipo_movimiento)) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            {{ $movimiento->metodoPago->nombre ?? '-' }}
                        </td>

                        <td>
                            <strong
                                class="{{ in_array($movimiento->tipo_movimiento, ['egreso', 'reembolso']) ? 'cash-amount-negative' : 'cash-amount-positive' }}"
                            >
                                {{ number_format($movimiento->monto, 2) }} Bs
                            </strong>
                        </td>

                        <td>
                            {{ $movimiento->usuario->nombre ?? '-' }}
                        </td>

                        <td>
                            @if ($movimiento->venta && auth()->user()->tienePermiso('ver_ventas'))
                                <a
                                    href="{{ route('ventas.show', $movimiento->venta) }}"
                                    class="cash-sale-link"
                                >
                                    {{ $movimiento->venta->numero_venta }}
                                </a>
                            @elseif ($movimiento->venta)
                                {{ $movimiento->venta->numero_venta }}
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <span class="cash-description">
                                {{ $movimiento->descripcion ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-table-message">
                            No hay movimientos de caja registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $movimientos->links() }}
    </div>

</div>

@endif

@endsection