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

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-heart-pulse"></i>
                Atención #{{ $atencionServicio->numero_atencion ?? 'SER-' . str_pad($atencionServicio->id, 6, '0', STR_PAD_LEFT) }}
            </h2>

            <p>
                Registrada el {{ $atencionServicio->fecha_hora->format('d/m/Y H:i') }}
                ·
                Servicio: <strong>{{ $atencionServicio->servicio->nombre ?? '-' }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            @if ($atencionServicio->estado === 'completada')
                <span class="badge badge-success">
                    Completada
                </span>
            @else
                <span class="badge badge-danger">
                    Anulada
                </span>
            @endif

            <button
                type="button"
                class="btn-primary"
                onclick="abrirModalImpresion('{{ route('atenciones-servicio.recibo', $atencionServicio) }}')"
            >
                <i class="bi bi-printer"></i>
                Imprimir
            </button>

            @if ($atencionServicio->estado === 'completada' && auth()->user()->tienePermiso('anular_atencion_servicio'))
                <a
                    href="{{ route('atenciones-servicio.anular.create', $atencionServicio) }}"
                    class="btn-danger"
                >
                    <i class="bi bi-x-octagon"></i>
                    Anular
                </a>
            @endif

            <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="detail-stat-grid">
        <div class="detail-stat-card">
            <span>Subtotal</span>
            <strong>{{ number_format($atencionServicio->subtotal, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Descuento</span>
            <strong>{{ number_format($atencionServicio->descuento, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card detail-stat-main">
            <span>Total cobrado</span>
            <strong>{{ number_format($atencionServicio->total, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Método</span>
            <strong>{{ $atencionServicio->metodoPago->nombre ?? '-' }}</strong>
        </div>
    </div>

    <div class="detail-layout">

        <section class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-clipboard2-pulse"></i>
                    Datos de la atención
                </h3>
            </div>

            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span>Servicio</span>
                    <strong>{{ $atencionServicio->servicio->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Cantidad</span>
                    <strong>{{ $atencionServicio->cantidad }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Precio unitario</span>
                    <strong>{{ number_format($atencionServicio->precio_unitario, 2) }} Bs</strong>
                </div>

                <div class="detail-info-item">
                    <span>Cliente</span>
                    <strong>{{ $atencionServicio->cliente->nombre ?? 'Consumidor final' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Sucursal</span>
                    <strong>{{ $atencionServicio->sucursal->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Registrado por</span>
                    <strong>{{ $atencionServicio->usuario->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item detail-info-full">
                    <span>Observación</span>
                    <strong>{{ $atencionServicio->observacion ?? '-' }}</strong>
                </div>
            </div>
        </section>

        <section class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-box-seam"></i>
                    Insumos descontados
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Lote</th>
                            <th>Desc.</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($atencionServicio->insumos as $insumo)
                            <tr>
                                <td>
                                    <strong>
                                        {{ $insumo->productoPresentacion->nombre_mostrado ?? $insumo->producto->nombre_comercial ?? '-' }}
                                    </strong>

                                    <br>

                                    <small style="color:#6B7280;">
                                        {{ $insumo->productoPresentacion->presentacion->nombre ?? '-' }}

                                        @if ($insumo->producto?->laboratorio)
                                            · {{ $insumo->producto->laboratorio->nombre }}
                                        @endif

                                        @if ($insumo->producto?->concentracion)
                                            · {{ $insumo->producto->concentracion }}
                                        @endif
                                    </small>
                                </td>

                                <td>
                                    {{ $insumo->lote->numero_lote ?? 'Sin lote' }}

                                    @if ($insumo->lote?->fecha_vencimiento)
                                        <br>
                                        <small style="color:#6B7280;">
                                            Vence: {{ $insumo->lote->fecha_vencimiento->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $insumo->unidades_descontadas }}</strong>
                                    <br>
                                    <small style="color:#6B7280;">unidad(es)</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-table-message">
                                    Este servicio no descontó insumos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    @if ($atencionServicio->estado === 'anulada')
        <div class="detail-cancel-card">
            <div>
                <h3>
                    <i class="bi bi-x-octagon"></i>
                    Datos de anulación
                </h3>

                <p>
                    <strong>Fecha:</strong>
                    {{ $atencionServicio->fecha_anulacion?->format('d/m/Y H:i') ?? '-' }}
                </p>

                <p>
                    <strong>Motivo:</strong>
                    {{ $atencionServicio->motivo_anulacion ?? '-' }}
                </p>
            </div>
        </div>
    @endif

</div>

@endsection