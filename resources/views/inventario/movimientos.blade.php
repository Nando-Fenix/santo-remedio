@extends('layouts.app')

@section('title', 'Movimientos de inventario | Santo Remedio')
@section('page-title', 'Movimientos de inventario')
@section('page-subtitle', 'Historial de entradas, salidas y ajustes de stock')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Historial de inventario</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Revisión de movimientos realizados en el stock.
            </p>
        </div>

        <a href="{{ route('inventario.index') }}" class="btn-secondary">
            Volver al inventario
        </a>
    </div>

    <form method="GET" action="{{ route('inventario.movimientos') }}" style="margin-bottom: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre comercial, genérico o concentración"
                >
            </div>

            <div class="form-group">
                <label>Sucursal</label>
                <select name="sucursal_id">
                    <option value="">Todas las sucursales</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" @selected(($sucursalId ?? '') == $sucursal->id)>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de movimiento</label>
                <select name="tipo_movimiento">
                    <option value="">Todos</option>
                    <option value="entrada" @selected(($tipoMovimiento ?? '') === 'entrada')>Entrada</option>
                    <option value="salida" @selected(($tipoMovimiento ?? '') === 'salida')>Salida</option>
                    <option value="ajuste_positivo" @selected(($tipoMovimiento ?? '') === 'ajuste_positivo')>Ajuste positivo</option>
                    <option value="ajuste_negativo" @selected(($tipoMovimiento ?? '') === 'ajuste_negativo')>Ajuste negativo</option>
                    <option value="devolucion" @selected(($tipoMovimiento ?? '') === 'devolucion')>Devolución</option>
                    <option value="vencimiento" @selected(($tipoMovimiento ?? '') === 'vencimiento')>Vencimiento</option>
                    <option value="daño" @selected(($tipoMovimiento ?? '') === 'daño')>Daño</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('inventario.movimientos') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Sucursal</th>
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
                        <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            {{ $movimiento->producto->nombre_comercial ?? '-' }}
                            @if($movimiento->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">{{ $movimiento->producto->concentracion }}</small>
                            @endif
                        </td>
                        <td>{{ $movimiento->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $movimiento->lote->numero_lote ?? 'Sin lote' }}</td>
                        <td>
                            <span class="badge badge-soft">
                                {{ str_replace('_', ' ', ucfirst($movimiento->tipo_movimiento)) }}
                            </span>
                        </td>
                        <td>{{ $movimiento->cantidad }}</td>
                        <td>{{ $movimiento->stock_anterior }}</td>
                        <td>{{ $movimiento->stock_nuevo }}</td>
                        <td>{{ $movimiento->usuario->nombre ?? '-' }}</td>
                        <td>{{ $movimiento->motivo ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: #6B7280;">
                            No hay movimientos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $movimientos->links() }}
    </div>

</div>

@endsection