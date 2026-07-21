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

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">
                {{ $servicioFarmacia->nombre }}
            </h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Servicio registrado para cobros y atenciones.
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            @if (auth()->user()->tienePermiso('editar_servicio_farmacia'))
                <a href="{{ route('servicios-farmacia.edit', $servicioFarmacia) }}" class="btn-primary">
                    Editar
                </a>
            @endif

            <a href="{{ route('servicios-farmacia.index') }}" class="btn-secondary">
                Volver
            </a>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Precio</span>
        <h3>{{ number_format($servicioFarmacia->precio, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Tipo</span>
        <h3 style="font-size:18px;">
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
        </h3>
    </div>

    <div class="stat-card">
        <span>Estado</span>
        <h3 style="font-size:18px;">
            @if ($servicioFarmacia->estado === 'activo')
                Activo
            @else
                Inactivo
            @endif
        </h3>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Descripción</h3>

    <p style="color:#374151;">
        {{ $servicioFarmacia->descripcion ?? 'Sin descripción.' }}
    </p>

    <p style="color:#6B7280; margin-bottom:0;">
        <strong>Creado por:</strong>
        {{ $servicioFarmacia->creadoPor->nombre ?? '-' }}
    </p>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Insumos configurados</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Presentación</th>
                    <th>Cantidad</th>
                    <th>Descuenta del inventario</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($servicioFarmacia->insumos as $insumo)
                    <tr>
                        <td>
                            <strong>{{ $insumo->productoPresentacion->nombre_mostrado ?? $insumo->producto->nombre_comercial ?? '-' }}</strong>

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

                        <td>{{ $insumo->cantidad }}</td>

                        <td>
                            {{ $insumo->unidades_necesarias }}
                            unidad(es)
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#6B7280;">
                            Este servicio no tiene insumos configurados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
