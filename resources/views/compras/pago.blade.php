@extends('layouts.app')

@section('title', 'Registrar pago | Santo Remedio')
@section('page-title', 'Registrar pago')
@section('page-subtitle', 'Pago de saldo pendiente a proveedor')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Pago de compra {{ $compra->numero_compra }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Proveedor: <strong>{{ $compra->proveedor->nombre ?? '-' }}</strong>
            </p>
        </div>

        <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
            Volver
        </a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Total compra</span>
            <h3>{{ number_format($compra->total, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Monto pagado</span>
            <h3>{{ number_format($compra->monto_pagado, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Saldo pendiente</span>
            <h3>{{ number_format($compra->saldo_pendiente, 2) }} Bs</h3>
        </div>
    </div>
</div>

<div class="card">
    @if (!auth()->user()->tienePermiso('pagar_compra'))
        <div class="alert-danger">
            No tiene permiso para registrar pagos de compras.
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

        <form method="POST" action="{{ route('compras.pago.store', $compra) }}" onsubmit="return confirmarFormulario(event, '¿Registrar pago de compra?')">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Monto a pagar *</label>
                    <input
                        type="number"
                        name="monto"
                        step="0.01"
                        min="0.01"
                        max="{{ $compra->saldo_pendiente }}"
                        value="{{ old('monto', $compra->saldo_pendiente) }}"
                        required
                    >
                    @error('monto')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Método de pago *</label>
                    <select name="metodo_pago" required>
                        <option value="efectivo" {{ old('metodo_pago') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="qr" {{ old('metodo_pago') === 'qr' ? 'selected' : '' }}>QR</option>
                        <option value="transferencia" {{ old('metodo_pago') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                        <option value="otro" {{ old('metodo_pago') === 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('metodo_pago')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Referencia</label>
                    <input
                        type="text"
                        name="referencia"
                        value="{{ old('referencia') }}"
                        placeholder="N° comprobante, QR, transferencia, etc."
                    >
                    @error('referencia')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Observación</label>
                    <textarea name="observacion" rows="3" placeholder="Opcional">{{ old('observacion') }}</textarea>
                    @error('observacion')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                @if (auth()->user()->tienePermiso('pagar_compra'))
                    <button type="submit" class="btn-primary">
                        Registrar pago
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