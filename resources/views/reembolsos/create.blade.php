@extends('layouts.app')

@section('title', 'Registrar reembolso | Santo Remedio')
@section('page-title', 'Registrar reembolso')
@section('page-subtitle', 'Devolución parcial de productos vendidos')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Reembolso de venta {{ $venta->numero_venta }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Seleccione los productos y cantidades que serán devueltos.
            </p>
        </div>

        <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
            Volver
        </a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Total venta</span>
            <h3>{{ number_format($venta->total, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Cliente</span>
            <h3>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</h3>
        </div>

        <div class="stat-card">
            <span>Sucursal</span>
            <h3>{{ $venta->sucursal->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Vendedor</span>
            <h3>{{ $venta->usuario->nombre ?? '-' }}</h3>
        </div>
    </div>
</div>

<div class="card">
    @if ($errors->any())
        <div class="alert-danger">
            <strong>Revise los siguientes errores:</strong>
            <ul style="margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('reembolsos.store', $venta) }}" onsubmit="return confirmarFormulario(event, '¿Confirmar reembolso?')">
        @csrf

        <h3 style="margin-top: 0; color: #4C1D95;">Productos vendidos</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Presentación</th>
                        <th>Cantidad vendida</th>
                        <th>Cantidad ya devuelta</th>
                        <th>Disponible para devolver</th>
                        <th>Precio unitario</th>
                        <th>Cantidad a devolver</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->detalles as $index => $detalle)
                        @php
                            $cantidadYaDevuelta = $detalle->reembolsos()
                                ->whereHas('reembolso', function ($query) {
                                    $query->where('estado', 'registrado');
                                })
                                ->sum('cantidad_devuelta');

                            $cantidadDisponible = $detalle->cantidad - $cantidadYaDevuelta;
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>

                                @if ($detalle->producto?->concentracion)
                                    <br>
                                    <small style="color: #6B7280;">
                                        {{ $detalle->producto->concentracion }}
                                    </small>
                                @endif

                                <br>
                                <small style="color: #6B7280;">
                                    Lotes:
                                    @forelse ($detalle->lotesDescontados as $loteDescontado)
                                        {{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}
                                        ({{ $loteDescontado->unidades_descontadas }} unidades)
                                    @empty
                                        Sin lote
                                    @endforelse
                                </small>
                            </td>

                            <td>{{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>{{ $cantidadYaDevuelta }}</td>

                            <td>
                                @if ($cantidadDisponible > 0)
                                    <span class="badge badge-success">{{ $cantidadDisponible }}</span>
                                @else
                                    <span class="badge badge-danger">0</span>
                                @endif
                            </td>

                            <td>{{ number_format($detalle->precio_unitario, 2) }} Bs</td>

                            <td>
                                <input type="hidden" name="items[{{ $index }}][detalle_venta_id]" value="{{ $detalle->id }}">

                                <input
                                    type="number"
                                    name="items[{{ $index }}][cantidad_devuelta]"
                                    min="0"
                                    max="{{ $cantidadDisponible }}"
                                    value="0"
                                    style="width: 110px;"
                                    {{ $cantidadDisponible <= 0 ? 'disabled' : '' }}
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="form-group" style="margin-top: 22px;">
            <label>Motivo del reembolso *</label>
            <textarea
                name="motivo"
                rows="4"
                required
                placeholder="Ej: Cliente devolvió producto por error de compra, medicamento equivocado, devolución autorizada..."
            >{{ old('motivo') }}</textarea>

            @error('motivo')
                <small class="error">{{ $message }}</small>
            @enderror
        </div>

        <div class="alert-danger" style="margin-top: 18px;">
            El reembolso devolverá stock al inventario y descontará el monto correspondiente de la caja actual.
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-danger">
                Registrar reembolso
            </button>

            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection