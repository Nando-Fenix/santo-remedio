@extends('layouts.app')

@section('title', 'Detalle de atención | Santo Remedio')
@section('page-title', 'Detalle de atención de servicio')
@section('page-subtitle', 'Información del servicio registrado y cobrado')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card" style="margin-bottom:22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">
                Atención de servicio #{{ $atencionServicio->numero_atencion ?? 'SER-' . str_pad($atencionServicio->id, 6, '0', STR_PAD_LEFT) }}
            </h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Registrada el {{ $atencionServicio->fecha_hora->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            @if ($atencionServicio->estado === 'completada' && auth()->user()->tienePermiso('anular_atencion_servicio'))
                <a href="{{ route('atenciones-servicio.anular.create', $atencionServicio) }}" class="btn-danger">
                    Anular atención
                </a>
            @endif

            <a href="{{ route('atenciones-servicio.recibo', $atencionServicio) }}" class="btn-primary" target="_blank">
                Imprimir comprobante
            </a>

            <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                Volver
            </a>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom:22px;">
    <div class="stat-card">
        <span>Subtotal</span>
        <h3>{{ number_format($atencionServicio->subtotal, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Descuento</span>
        <h3>{{ number_format($atencionServicio->descuento, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total</span>
        <h3>{{ number_format($atencionServicio->total, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Estado</span>
        <h3 style="font-size:18px;">
            {{ ucfirst($atencionServicio->estado) }}
        </h3>
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Datos de la atención</h3>

    <p>
        <strong>Servicio:</strong>
        {{ $atencionServicio->servicio->nombre ?? '-' }}

        <br>
        <strong>Cantidad:</strong>
        {{ $atencionServicio->cantidad }}

        <br>
        <strong>Precio unitario:</strong>
        {{ number_format($atencionServicio->precio_unitario, 2) }} Bs

        <br>
        <strong>Cliente:</strong>
        {{ $atencionServicio->cliente->nombre ?? 'Consumidor final' }}

        <br>
        <strong>Método de pago:</strong>
        {{ $atencionServicio->metodoPago->nombre ?? '-' }}

        <br>
        <strong>Sucursal:</strong>
        {{ $atencionServicio->sucursal->nombre ?? '-' }}

        <br>
        <strong>Registrado por:</strong>
        {{ $atencionServicio->usuario->nombre ?? '-' }}

        <br>
        <strong>Observación:</strong>
        {{ $atencionServicio->observacion ?? '-' }}
    </p>
</div>

<div class="card" style="margin-bottom:22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Insumos descontados</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Presentación</th>
                    <th>Lote</th>
                    <th>Unidades descontadas</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($atencionServicio->insumos as $insumo)
                    <tr>
                        <td>
                            <strong>
                                {{ $insumo->productoPresentacion->nombre_mostrado ?? $insumo->producto->nombre_comercial ?? '-' }}
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
                            Este servicio no descontó insumos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($atencionServicio->estado === 'anulada')
    <div class="card">
        <h3 style="margin-top:0; color:#991B1B;">Datos de anulación</h3>

        <p>
            <strong>Fecha de anulación:</strong>
            {{ $atencionServicio->fecha_anulacion?->format('d/m/Y H:i') ?? '-' }}

            <br>
            <strong>Motivo:</strong>
            {{ $atencionServicio->motivo_anulacion ?? '-' }}
        </p>
    </div>
@endif

@endsection
