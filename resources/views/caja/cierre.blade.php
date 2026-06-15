@extends('layouts.app')

@section('title', 'Cerrar caja | Santo Remedio')
@section('page-title', 'Cerrar caja')
@section('page-subtitle', 'Arqueo y verificación de caja')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Cierre de caja</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Sucursal: <strong>{{ $sucursal->nombre }}</strong>.
            Turno: <strong>{{ $cajaAbierta->turno->nombre ?? '-' }}</strong>.
            Apertura: <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
        </p>
    </div>

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
    @endphp

    <div class="grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <span>Monto inicial</span>
            <h3>{{ number_format($cajaAbierta->monto_inicial, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Ventas efectivo</span>
            <h3>{{ number_format($cajaAbierta->total_efectivo, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Ventas QR</span>
            <h3>{{ number_format($cajaAbierta->total_qr, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Egresos</span>
            <h3>{{ number_format($cajaAbierta->total_egresos, 2) }} Bs</h3>
        </div>
    </div>

    <div class="card" style="background: #F5F3FF; margin-bottom: 22px;">
        <h3 style="margin-top: 0; color: #4C1D95;">Resumen del sistema</h3>

        <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 0;">
            <div class="stat-card">
                <span>Efectivo esperado</span>
                <h3 id="efectivo_sistema">{{ number_format($efectivoSistema, 2) }} Bs</h3>
            </div>

            <div class="stat-card">
                <span>QR esperado</span>
                <h3 id="qr_sistema">{{ number_format($qrSistema, 2) }} Bs</h3>
            </div>

            <div class="stat-card">
                <span>Total sistema</span>
                <h3 id="total_sistema">{{ number_format($totalSistema, 2) }} Bs</h3>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('caja.cierre.store') }}" onsubmit="return confirmarFormulario(event, '¿Confirmar cierre de caja?')">
        @csrf

        <div class="card" style="margin-top: 20px; margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Conteo de efectivo</h3>

            <p style="color: #6B7280; margin-top: 0;">
                Ingrese la cantidad de billetes y monedas contadas. El sistema calculará el efectivo automáticamente.
            </p>

            @php
                $denominaciones = [200, 100, 50, 20, 10, 5, 2, 1, 0.50, 0.20];
            @endphp

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Denominación</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($denominaciones as $denominacion)
                            <tr>
                                <td>{{ number_format($denominacion, 2) }} Bs</td>
                                <td>
                                    <input
                                        type="number"
                                        min="0"
                                        value="0"
                                        class="denominacion-input"
                                        data-denominacion="{{ $denominacion }}"
                                        name="denominaciones[{{ str_replace('.', '_', $denominacion) }}][cantidad]"
                                        style="width: 110px;"
                                    >

                                    <input
                                        type="hidden"
                                        name="denominaciones[{{ str_replace('.', '_', $denominacion) }}][denominacion]"
                                        value="{{ $denominacion }}"
                                    >
                                </td>
                                <td>
                                    <strong class="denominacion-total">0.00 Bs</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-grid">
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
        </div>

        <div class="card" style="margin-top: 20px; background: #FAFAFA;">
            <h3 style="margin-top: 0; color: #4C1D95;">Diferencias</h3>

            <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 0;">
                <div class="stat-card">
                    <span>Diferencia efectivo</span>
                    <h3 id="diferencia_efectivo">0.00 Bs</h3>
                </div>

                <div class="stat-card">
                    <span>Diferencia QR</span>
                    <h3 id="diferencia_qr">0.00 Bs</h3>
                </div>

                <div class="stat-card">
                    <span>Total verificado</span>
                    <h3 id="total_verificado">{{ number_format($totalSistema, 2) }} Bs</h3>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 18px;">
            <label>Observación</label>
            <textarea name="observacion" rows="4" placeholder="Ej: Caja cerrada sin diferencias">{{ old('observacion') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">
                Cerrar caja
            </button>

            <a href="{{ route('caja.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>

</div>

<script>
    const efectivoSistema = Number({{ $efectivoSistema }});
    const qrSistema = Number({{ $qrSistema }});

    const efectivoContado = document.getElementById('efectivo_contado');
    const qrVerificado = document.getElementById('qr_verificado');

    const diferenciaEfectivoText = document.getElementById('diferencia_efectivo');
    const diferenciaQrText = document.getElementById('diferencia_qr');
    const totalVerificadoText = document.getElementById('total_verificado');

    const denominacionInputs = document.querySelectorAll('.denominacion-input');

    function calcularDenominaciones() {
        let totalEfectivo = 0;

        denominacionInputs.forEach(input => {
            const denominacion = Number(input.dataset.denominacion || 0);
            const cantidad = Number(input.value || 0);
            const total = denominacion * cantidad;

            const fila = input.closest('tr');
            const totalText = fila.querySelector('.denominacion-total');

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
    }

    denominacionInputs.forEach(input => {
        input.addEventListener('input', calcularDenominaciones);
    });

    qrVerificado.addEventListener('input', calcularDiferencias);

    calcularDenominaciones();
</script>

@endsection