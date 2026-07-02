@extends('layouts.app')

@section('title', 'Anular compra | Santo Remedio')
@section('page-title', 'Anular compra')
@section('page-subtitle', 'Anulación controlada con ajuste de inventario')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Anular compra {{ $compra->numero_compra }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Esta acción descontará del inventario el stock que ingresó por esta compra.
            </p>
        </div>

        <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
            Volver
        </a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Proveedor</span>
            <h3>{{ $compra->proveedor->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Total compra</span>
            <h3>{{ number_format($compra->total, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Pagado</span>
            <h3>{{ number_format($compra->monto_pagado, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Saldo pendiente</span>
            <h3>{{ number_format($compra->saldo_pendiente, 2) }} Bs</h3>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos que se descontarán del inventario</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Presentación</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Cantidad compra</th>
                    <th>Unidades a descontar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compra->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>
                            @if ($detalle->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">{{ $detalle->producto->concentracion }}</small>
                            @endif
                        </td>
                        <td>{{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}</td>
                        <td>{{ $detalle->lote->numero_lote ?? 'Sin lote' }}</td>
                        <td>
                            {{ $detalle->lote?->fecha_vencimiento ? $detalle->lote->fecha_vencimiento->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>
                            <span class="badge badge-warning">
                                {{ $detalle->unidades_ingresadas }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    @if (!auth()->user()->tienePermiso('anular_compra'))
        <div class="alert-danger">
            No tiene permiso para anular compras.
        </div>
    @else
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

    <form method="POST" action="{{ route('compras.anular.store', $compra) }}" onsubmit="return confirmarFormulario(event, '¿Confirmar anulación de compra?')">
        @csrf

        <div class="form-group">
            <label>Motivo de anulación *</label>
            <textarea
                name="motivo_anulacion"
                rows="4"
                required
                placeholder="Ej: Compra registrada por error, factura duplicada, proveedor equivocado..."
            >{{ old('motivo_anulacion') }}</textarea>

            @error('motivo_anulacion')
                <small class="error">{{ $message }}</small>
            @enderror
        </div>

        <div class="alert-danger" style="margin-top: 18px;">
            Esta acción no eliminará la compra. Solo la marcará como anulada y dejará trazabilidad en inventario.
            Si parte del stock ya fue vendido o movido, la anulación no será permitida.
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            @if (auth()->user()->tienePermiso('anular_compra'))
                <button type="submit" class="btn-danger">
                    Anular compra
                </button>
            @endif

            <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
    @endif
</div>

@endsection