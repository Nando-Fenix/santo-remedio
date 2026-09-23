@extends('layouts.app')

@section('title', 'Registrar pago | Santo Remedio')
@section('page-title', 'Registrar pago')
@section('page-subtitle', 'Pago de saldo pendiente a proveedor')

@section('content')

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

<form method="POST" action="{{ route('compras.pago.store', $compra) }}" id="form_pago_compra">
    @csrf

    <div class="purchase-payment-layout">

        <section class="purchase-payment-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-cash-coin"></i>
                            Pago de compra {{ $compra->numero_compra }}
                        </h2>

                        <p>
                            Proveedor:
                            <strong>{{ $compra->proveedor->nombre ?? '-' }}</strong>
                        </p>
                    </div>

                    <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="detail-stat-grid compact-detail-stats">
                    <div class="detail-stat-card detail-stat-main">
                        <span>Saldo pendiente</span>
                        <strong>{{ number_format($compra->saldo_pendiente, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Total compra</span>
                        <strong>{{ number_format($compra->total, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Monto pagado</span>
                        <strong>{{ number_format($compra->monto_pagado, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Estado pago</span>
                        <strong>{{ ucfirst($compra->estado_pago) }}</strong>
                    </div>
                </div>
            </div>

            <div class="purchase-payment-info-card">
                <div>
                    <i class="bi bi-info-circle"></i>
                </div>

                <section>
                    <h3>Registro de pago</h3>

                    <ul>
                        <li>El monto no puede superar el saldo pendiente.</li>
                        <li>Use referencia si el pago fue por QR, transferencia o comprobante.</li>
                        <li>El pago reducirá la deuda activa con el proveedor.</li>
                        <li>Revise bien el monto antes de confirmar.</li>
                    </ul>
                </section>
            </div>

        </section>

        <aside class="purchase-payment-side">

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-wallet2"></i>
                        Datos del pago
                    </h3>
                </div>

                <div class="form-group">
                    <label>Monto a pagar *</label>
                    <input
                        type="number"
                        name="monto"
                        id="monto_pago_compra"
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
                        <option value="efectivo" @selected(old('metodo_pago') === 'efectivo')>Efectivo</option>
                        <option value="qr" @selected(old('metodo_pago') === 'qr')>QR</option>
                        <option value="transferencia" @selected(old('metodo_pago') === 'transferencia')>Transferencia</option>
                        <option value="otro" @selected(old('metodo_pago') === 'otro')>Otro</option>
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

                <div class="form-group">
                    <label>Observación</label>
                    <textarea
                        name="observacion"
                        rows="5"
                        placeholder="Opcional"
                    >{{ old('observacion') }}</textarea>
                    @error('observacion')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="purchase-payment-preview">
                    <span>Pago a registrar</span>
                    <strong id="pago_compra_preview">0.00 Bs</strong>
                    <small>
                        Saldo actual: {{ number_format($compra->saldo_pendiente, 2) }} Bs
                    </small>
                </div>

                <div class="purchase-payment-actions">
                    <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Registrar
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const formPagoCompra = document.getElementById('form_pago_compra');
const montoPagoCompra = document.getElementById('monto_pago_compra');
const pagoCompraPreview = document.getElementById('pago_compra_preview');
const saldoPendienteCompra = Number({{ $compra->saldo_pendiente }});

function actualizarPagoCompraPreview() {
    const monto = Number(montoPagoCompra.value || 0);
    pagoCompraPreview.textContent = monto.toFixed(2) + ' Bs';
}

montoPagoCompra.addEventListener('input', actualizarPagoCompraPreview);

formPagoCompra.addEventListener('submit', function (event) {
    event.preventDefault();

    const monto = Number(montoPagoCompra.value || 0);

    if (monto <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El monto del pago debe ser mayor a 0.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (monto > saldoPendienteCompra) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto excedido',
            text: 'El pago no puede ser mayor al saldo pendiente.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'question',
        title: '¿Registrar pago?',
        text: 'Se reducirá el saldo pendiente de esta compra.',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});

actualizarPagoCompraPreview();
</script>

@endif

@endsection