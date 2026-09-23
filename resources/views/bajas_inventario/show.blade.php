@extends('layouts.app')

@section('title', 'Detalle de baja de inventario | Santo Remedio')
@section('page-title', 'Detalle de baja de inventario')
@section('page-subtitle', 'Información del producto retirado del inventario')

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
                <i class="bi bi-box-arrow-down"></i>
                Baja #{{ $bajaInventario->numero_baja ?? 'BAJ-' . str_pad($bajaInventario->id, 6, '0', STR_PAD_LEFT) }}
            </h2>

            <p>
                Registrada el {{ $bajaInventario->created_at->format('d/m/Y H:i') }}
                ·
                Producto: <strong>{{ $bajaInventario->producto->nombre_comercial ?? '-' }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            @if ($bajaInventario->estado === 'registrado')
                <span class="badge badge-success">Registrado</span>
            @else
                <span class="badge badge-danger">Anulado</span>
            @endif

            <button
                type="button"
                class="btn-primary"
                onclick="abrirModalImpresion('{{ route('bajas-inventario.recibo', $bajaInventario) }}')"
            >
                <i class="bi bi-printer"></i>
                Imprimir
            </button>

            @if ($bajaInventario->estado === 'registrado' && auth()->user()->tienePermiso('anular_baja_inventario'))
                <a
                    href="{{ route('bajas-inventario.anular.create', $bajaInventario) }}"
                    class="btn-danger"
                >
                    <i class="bi bi-x-octagon"></i>
                    Anular
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_bajas_inventario'))
                <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="detail-stat-grid">
        <div class="detail-stat-card detail-stat-main">
            <span>Cantidad retirada</span>
            <strong>{{ $bajaInventario->cantidad }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Stock anterior</span>
            <strong>{{ $bajaInventario->stock_anterior }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Stock nuevo</span>
            <strong>{{ $bajaInventario->stock_nuevo }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Motivo</span>
            <strong>
                @if ($bajaInventario->motivo === 'vencimiento')
                    Vencimiento
                @elseif ($bajaInventario->motivo === 'danado')
                    Dañado
                @elseif ($bajaInventario->motivo === 'perdido')
                    Perdido
                @elseif ($bajaInventario->motivo === 'ajuste_autorizado')
                    Ajuste autorizado
                @else
                    Otro
                @endif
            </strong>
        </div>
    </div>

    <div class="stock-out-detail-layout">

        <section class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-capsule"></i>
                    Producto retirado
                </h3>
            </div>

            <div class="detail-info-grid">
                <div class="detail-info-item detail-info-full">
                    <span>Producto</span>
                    <strong>{{ $bajaInventario->producto->nombre_comercial ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Nombre genérico</span>
                    <strong>{{ $bajaInventario->producto->nombre_generico ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Concentración</span>
                    <strong>{{ $bajaInventario->producto->concentracion ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Laboratorio</span>
                    <strong>{{ $bajaInventario->producto->laboratorio->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Sucursal</span>
                    <strong>{{ $bajaInventario->sucursal->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Lote</span>
                    <strong>{{ $bajaInventario->lote->numero_lote ?? 'Sin lote' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Vencimiento</span>
                    <strong>
                        {{ $bajaInventario->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-' }}
                    </strong>
                </div>
            </div>
        </section>

        <section class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-card-text"></i>
                    Motivo y observación
                </h3>
            </div>

            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span>Motivo</span>
                    <strong>
                        @if ($bajaInventario->motivo === 'vencimiento')
                            Vencimiento
                        @elseif ($bajaInventario->motivo === 'danado')
                            Dañado
                        @elseif ($bajaInventario->motivo === 'perdido')
                            Perdido
                        @elseif ($bajaInventario->motivo === 'ajuste_autorizado')
                            Ajuste autorizado
                        @else
                            Otro
                        @endif
                    </strong>
                </div>

                <div class="detail-info-item">
                    <span>Registrado por</span>
                    <strong>{{ $bajaInventario->usuario->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item detail-info-full">
                    <span>Observación</span>
                    <strong>{{ $bajaInventario->observacion ?? '-' }}</strong>
                </div>
            </div>

            <div class="stock-out-impact-card">
                <div>
                    <span>Impacto en inventario</span>
                    <strong>
                        {{ $bajaInventario->stock_anterior }}
                        →
                        {{ $bajaInventario->stock_nuevo }}
                    </strong>
                </div>

                <div>
                    <span>Unidades retiradas</span>
                    <strong>{{ $bajaInventario->cantidad }}</strong>
                </div>
            </div>
        </section>

    </div>

    @if ($bajaInventario->estado === 'anulado')
        <div class="detail-cancel-card">
            <div>
                <h3>
                    <i class="bi bi-x-octagon"></i>
                    Datos de anulación
                </h3>

                <p>
                    <strong>Anulado por:</strong>
                    {{ $bajaInventario->usuarioAnulacion->nombre ?? '-' }}
                </p>

                <p>
                    <strong>Fecha:</strong>
                    {{ $bajaInventario->fecha_anulacion?->format('d/m/Y H:i') ?? '-' }}
                </p>

                <p>
                    <strong>Motivo:</strong>
                    {{ $bajaInventario->motivo_anulacion ?? '-' }}
                </p>
            </div>
        </div>
    @endif

</div>

@endsection