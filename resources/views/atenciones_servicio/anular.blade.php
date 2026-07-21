@extends('layouts.app')

@section('title', 'Anular atención de servicio | Santo Remedio')
@section('page-title', 'Anular atención de servicio')
@section('page-subtitle', 'Reversión de cobro e insumos descontados')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#991B1B;">
                Anular atención de servicio #{{ $atencionServicio->id }}
            </h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Esta acción devolverá los insumos al inventario y restará el ingreso de caja.
            </p>
        </div>

        <a href="{{ route('atenciones-servicio.show', $atencionServicio) }}" class="btn-secondary">
            Volver
        </a>
    </div>
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

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Servicio</span>
        <h3 style="font-size:18px;">
            {{ $atencionServicio->servicio->nombre ?? '-' }}
        </h3>
    </div>

    <div class="stat-card">
        <span>Total cobrado</span>
        <h3>{{ number_format($atencionServicio->total, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Método</span>
        <h3 style="font-size:18px;">
            {{ $atencionServicio->metodoPago->nombre ?? '-' }}
        </h3>
    </div>

    <div class="stat-card">
        <span>Fecha</span>
        <h3 style="font-size:18px;">
            {{ $atencionServicio->fecha_hora->format('d/m/Y H:i') }}
        </h3>
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Datos de la atención</h3>

    <p>
        <strong>Cliente:</strong>
        {{ $atencionServicio->cliente->nombre ?? 'Consumidor final' }}

        <br>
        <strong>Cantidad:</strong>
        {{ $atencionServicio->cantidad }}

        <br>
        <strong>Precio unitario:</strong>
        {{ number_format($atencionServicio->precio_unitario, 2) }} Bs

        <br>
        <strong>Subtotal:</strong>
        {{ number_format($atencionServicio->subtotal, 2) }} Bs

        <br>
        <strong>Descuento:</strong>
        {{ number_format($atencionServicio->descuento, 2) }} Bs

        <br>
        <strong>Total:</strong>
        {{ number_format($atencionServicio->total, 2) }} Bs

        <br>
        <strong>Registrado por:</strong>
        {{ $atencionServicio->usuario->nombre ?? '-' }}

        <br>
        <strong>Observación:</strong>
        {{ $atencionServicio->observacion ?? '-' }}
    </p>
</div>

<div class="card" style="margin-bottom:22px;">
    <h3 style="margin-top:0; color:#991B1B;">Insumos que serán devueltos</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Presentación</th>
                    <th>Lote</th>
                    <th>Unidades a devolver</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($atencionServicio->insumos as $insumo)
                    <tr>
                        <td>
                            <strong>
                                {{ $insumo->producto->nombre_comercial ?? '-' }}
                            </strong>

                            @if ($insumo->producto?->laboratorio || $insumo->producto?->concentracion)
                                <br>
                                <small style="color:#6B7280;">
                                    @if ($insumo->producto?->laboratorio)
                                        {{ $insumo->producto->laboratorio->nombre }}
                                    @endif

                                    @if ($insumo->producto?->laboratorio && $insumo->producto?->concentracion)
                                        |
                                    @endif

                                    @if ($insumo->producto?->concentracion)
                                        {{ $insumo->producto->concentracion }}
                                    @endif
                                </small>
                            @endif
                        </td>

                        <td>{{ $insumo->productoPresentacion->presentacion->nombre ?? '-' }}</td>

                        <td>
                            {{ $insumo->lote->numero_lote ?? 'Sin lote' }}

                            @if ($insumo->lote?->fecha_vencimiento)
                                <br>
                                <small style="color:#6B7280;">
                                    Vence: {{ $insumo->lote->fecha_vencimiento->format('d/m/Y') }}
                                </small>
                            @endif
                        </td>

                        <td>{{ $insumo->unidades_descontadas }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#6B7280;">
                            Esta atención no descontó insumos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#991B1B;">Confirmar anulación</h3>

    <form method="POST" action="{{ route('atenciones-servicio.anular.store', $atencionServicio) }}" id="form_anular_atencion">
        @csrf

        <div class="form-group">
            <label>Motivo de anulación *</label>
            <textarea
                name="motivo_anulacion"
                rows="4"
                required
                placeholder="Ej: Error en el registro, servicio no realizado, cobro incorrecto..."
            >{{ old('motivo_anulacion') }}</textarea>
        </div>

        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:18px;">
            <button type="submit" class="btn-danger">
                Confirmar anulación
            </button>

            <a href="{{ route('atenciones-servicio.show', $atencionServicio) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('form_anular_atencion').addEventListener('submit', function (event) {
    event.preventDefault();

    const motivo = document.querySelector('textarea[name="motivo_anulacion"]').value.trim();

    if (motivo.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Motivo requerido',
            text: 'Debe ingresar un motivo de al menos 5 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Anular atención?',
        text: 'Se devolverán los insumos al inventario y se restará el ingreso de caja.',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});
</script>

@endsection