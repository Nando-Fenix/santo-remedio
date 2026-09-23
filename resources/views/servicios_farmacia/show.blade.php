@extends('layouts.app')

@section('title', 'Detalle de servicio | Santo Remedio')
@section('page-title', 'Detalle de servicio')
@section('page-subtitle', 'Información del servicio de farmacia')

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
                <i class="bi bi-clipboard2-pulse"></i>
                {{ $servicioFarmacia->nombre }}
            </h2>

            <p>
                Servicio registrado para cobros y atenciones.
            </p>
        </div>

        <div class="detail-actions">
            @if ($servicioFarmacia->estado === 'activo')
                <span class="badge badge-success">Activo</span>
            @else
                <span class="badge badge-danger">Inactivo</span>
            @endif

            @if (auth()->user()->tienePermiso('editar_servicio_farmacia'))
                <a href="{{ route('servicios-farmacia.edit', $servicioFarmacia) }}" class="btn-primary">
                    <i class="bi bi-pencil"></i>
                    Editar
                </a>
            @endif

            <a href="{{ route('servicios-farmacia.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="detail-stat-grid">
        <div class="detail-stat-card detail-stat-main">
            <span>Precio</span>
            <strong>{{ number_format($servicioFarmacia->precio, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Tipo</span>
            <strong>
                @if ($servicioFarmacia->tipo === 'inyectable')
                    Inyectable
                @elseif ($servicioFarmacia->tipo === 'control')
                    Control
                @elseif ($servicioFarmacia->tipo === 'curacion')
                    Curación
                @elseif ($servicioFarmacia->tipo === 'nebulizacion')
                    Nebulización
                @elseif ($servicioFarmacia->tipo === 'orientacion')
                    Orientación
                @else
                    Otro
                @endif
            </strong>
        </div>

        <div class="detail-stat-card">
            <span>Insumos</span>
            <strong>{{ $servicioFarmacia->insumos->count() }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Creado por</span>
            <strong>{{ $servicioFarmacia->creadoPor->nombre ?? '-' }}</strong>
        </div>
    </div>

    <div class="service-show-layout">

        <section class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-card-text"></i>
                    Descripción
                </h3>
            </div>

            <div class="service-description-box">
                {{ $servicioFarmacia->descripcion ?? 'Sin descripción.' }}
            </div>
        </section>

        <section class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-box-seam"></i>
                    Insumos configurados
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Cant.</th>
                            <th>Desc.</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($servicioFarmacia->insumos as $insumo)
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
                                    <strong>{{ $insumo->cantidad }}</strong>
                                </td>

                                <td>
                                    <strong>{{ $insumo->unidades_necesarias }}</strong>
                                    <br>
                                    <small style="color:#6B7280;">unidad(es)</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-table-message">
                                    Este servicio no tiene insumos configurados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

</div>

@endsection