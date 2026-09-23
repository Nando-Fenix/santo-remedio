@extends('layouts.app')

@section('title', 'Cerrar caja | Santo Remedio')
@section('page-title', 'Cerrar caja')
@section('page-subtitle', 'Arqueo y verificación de caja')

@section('content')

@if (!auth()->user()->tienePermiso('cerrar_caja'))
    <div class="alert-danger">
        No tiene permiso para cerrar caja.
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

@php
    $efectivoSistema = $cajaAbierta->monto_inicial
        + $cajaAbierta->total_efectivo
        - $cajaAbierta->total_egresos
        - $cajaAbierta->total_reembolsos;

    $qrSistema = $cajaAbierta->total_qr;
    $totalSistema = $efectivoSistema + $qrSistema;

    $denominaciones = [200, 100, 50, 20, 10, 5, 2, 1, 0.50, 0.20];
@endphp

<form method="POST" action="{{ route('caja.cierre.store') }}" id="form_cierre_caja">
    @csrf

    <div class="cash-close-layout">

        <section class="cash-close-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-lock"></i>
                            Cierre de caja
                        </h2>

                        <p>
                            Sucursal:
                            <strong>{{ $sucursal->nombre }}</strong>
                            · Turno:
                            <strong>{{ $cajaAbierta->turno->nombre ?? '-' }}</strong>
                            · Apertura:
                            <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
                        </p>
                    </div>

                    <a href="{{ route('caja.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="detail-stat-grid compact-detail-stats">
                    <div class="detail-stat-card detail-stat-main">
                        <span>Efectivo esperado</span>
                        <strong id="efectivo_sistema">{{ number_format($efectivoSistema, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>QR esperado</span>
                        <strong id="qr_sistema">{{ number_format($qrSistema, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Total sistema</span>
                        <strong id="total_sistema">{{ number_format($totalSistema, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Monto inicial</span>
                        <strong>{{ number_format($cajaAbierta->monto_inicial, 2) }} Bs</strong>
                    </div>
                </div>
            </div>

            <div class="cash-close-income-grid">
                <div>
                    <span>Ventas efectivo</span>
                    <strong>{{ number_format($ventasEfectivo ?? 0, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Ventas QR</span>
                    <strong>{{ number_format($ventasQr ?? 0, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Servicios efectivo</span>
                    <strong>{{ number_format($serviciosEfectivo ?? 0, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Servicios QR</span>
                    <strong>{{ number_format($serviciosQr ?? 0, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Egresos</span>
                    <strong class="cash-close-danger">{{ number_format($cajaAbierta->total_egresos, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Reembolsos</span>
                    <strong class="cash-close-danger">{{ number_format($cajaAbierta->total_reembolsos, 2) }} Bs</strong>
                </div>
            </div>

            <div class="cash-close-total-strip">
                <div>
                    <span>Total ventas</span>
                    <strong>{{ number_format($ingresosVentas ?? 0, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Total servicios</span>
                    <strong>{{ number_format($ingresosServicios ?? 0, 2) }} Bs</strong>
                </div>

                <div>
                    <span>Total ingresos válidos</span>
                    <strong>{{ number_format(($ingresosVentas ?? 0) + ($ingresosServicios ?? 0), 2) }} Bs</strong>
                </div>
            </div>

            <div class="detail-section-card compact-section">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-calculator"></i>
                        Conteo de efectivo
                    </h3>
                </div>

                <p class="cash-close-help-text">
                    Ingrese la cantidad de billetes y monedas contadas. El sistema calculará el efectivo automáticamente.
                </p>

                <div class="cash-denomination-grid">
                    @foreach ($denominaciones as $denominacion)
                        <div class="cash-denomination-item">
                            <label>{{ number_format($denominacion, 2) }} Bs</label>

                            <input
                                type="number"
                                min="0"
                                value="0"
                                class="denominacion-input"
                                data-denominacion="{{ $denominacion }}"
                                name="denominaciones[{{ str_replace('.', '_', $denominacion) }}][cantidad]"
                            >

                            <input
                                type="hidden"
                                name="denominaciones[{{ str_replace('.', '_', $denominacion) }}][denominacion]"
                                value="{{ $denominacion }}"
                            >

                            <strong class="denominacion-total">0.00 Bs</strong>
                        </div>
                    @endforeach
                </div>
            </div>

        </section>

        <aside class="cash-close-side">

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-clipboard-check"></i>
                        Verificación final
                    </h3>
                </div>

                <div class="form-group">
                    <label>Efectivo contado *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="efectivo_contado"
                        id="efectivo_contado"
                        value="{{ old('efectivo_contado', 0) }}"
                        readonly
                        required
                    >
                </div>

                <div class="form-group">
                    <label>QR / Transferencia verificado *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="qr_verificado"
                        id="qr_verificado"
                        value="{{ old('qr_verificado', $qrSistema) }}"
                        required
                    >
                </div>

                <div class="cash-close-difference-grid">
                    <div>
                        <span>Diferencia efectivo</span>
                        <strong id="diferencia_efectivo">0.00 Bs</strong>
                    </div>

                    <div>
                        <span>Diferencia QR</span>
                        <strong id="diferencia_qr">0.00 Bs</strong>
                    </div>

                    <div class="cash-close-total-verified">
                        <span>Total verificado</span>
                        <strong id="total_verificado">{{ number_format($totalSistema, 2) }} Bs</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea
                        name="observacion"
                        rows="5"
                        placeholder="Ej: Caja cerrada sin diferencias"
                    >{{ old('observacion') }}</textarea>
                </div>

                <div class="cash-close-export-actions">
                    <a href="{{ route('caja.cierre.exportar-csv') }}" class="btn-secondary">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        Excel
                    </a>

                    <a href="{{ route('caja.cierre.exportar-excel') }}" class="btn-secondary">
                        <i class="bi bi-file-earmark-richtext"></i>
                        Visual
                    </a>
                </div>

                <div class="cash-close-actions">
                    <a href="{{ route('caja.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-lock"></i>
                        Cerrar caja
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const efectivoSistema = Number({{ $efectivoSistema }});
const qrSistema = Number({{ $qrSistema }});

const formCierreCaja = document.getElementById('form_cierre_caja');
const efectivoContado = document.getElementById('efectivo_contado');
const qrVerificado = document.getElementById('qr_verificado');

const diferenciaEfectivoText = document.getElementById('diferencia_efectivo');
const diferenciaQrText = document.getElementById('diferencia_qr');
const totalVerificadoText = document.getElementById('total_verificado');

const denominacionInputs = document.querySelectorAll('.denominacion-input');

function pintarDiferencia(elemento, valor) {
    elemento.classList.remove('cash-close-ok', 'cash-close-alert');

    if (Math.abs(valor) < 0.01) {
        elemento.classList.add('cash-close-ok');
    } else {
        elemento.classList.add('cash-close-alert');
    }
}

function calcularDenominaciones() {
    let totalEfectivo = 0;

    denominacionInputs.forEach(input => {
        const denominacion = Number(input.dataset.denominacion || 0);
        const cantidad = Number(input.value || 0);
        const total = denominacion * cantidad;

        const contenedor = input.closest('.cash-denomination-item');
        const totalText = contenedor.querySelector('.denominacion-total');

        totalText.textContent = total.toFixed(2) + ' Bs';

        totalEfectivo += total;
    });

    efectivoContado.value = totalEfectivo.toFixed(2);

    calcularDiferencias();
}

function calcularDiferencias() {
    const efectivo = Number(efectivoContado.value || 0);
    const qr = Number(qrVerificado.value || 0);

    const diferenciaEfectivo = efectivo - efectivoSistema;
    const diferenciaQr = qr - qrSistema;
    const totalVerificado = efectivo + qr;

    diferenciaEfectivoText.textContent = diferenciaEfectivo.toFixed(2) + ' Bs';
    diferenciaQrText.textContent = diferenciaQr.toFixed(2) + ' Bs';
    totalVerificadoText.textContent = totalVerificado.toFixed(2) + ' Bs';

    pintarDiferencia(diferenciaEfectivoText, diferenciaEfectivo);
    pintarDiferencia(diferenciaQrText, diferenciaQr);
}

denominacionInputs.forEach(input => {
    input.addEventListener('input', calcularDenominaciones);
});

qrVerificado.addEventListener('input', calcularDiferencias);

formCierreCaja.addEventListener('submit', function (event) {
    event.preventDefault();

    const efectivo = Number(efectivoContado.value || 0);
    const qr = Number(qrVerificado.value || 0);
    const diferenciaEfectivo = efectivo - efectivoSistema;
    const diferenciaQr = qr - qrSistema;

    Swal.fire({
        icon: Math.abs(diferenciaEfectivo) < 0.01 && Math.abs(diferenciaQr) < 0.01 ? 'question' : 'warning',
        title: '¿Confirmar cierre de caja?',
        text: 'Se registrará el arqueo final y la caja quedará cerrada.',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar caja',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});

calcularDenominaciones();
</script>

@endif

@endsection