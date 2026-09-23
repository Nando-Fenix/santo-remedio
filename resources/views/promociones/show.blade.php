@extends('layouts.app')

@section('title', 'Detalle de promoción | Santo Remedio')
@section('page-title', 'Detalle de promoción')
@section('page-subtitle', 'Productos incluidos, vigencia y precio promocional')

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
                <i class="bi bi-tags"></i>
                {{ $promocion->nombre }}
            </h2>

            <p>
                {{ $promocion->descripcion ?? 'Sin descripción' }}
            </p>
        </div>

        <div class="detail-actions">
            <span class="badge {{ $promocion->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                {{ ucfirst($promocion->estado) }}
            </span>

            @if ($promocion->estado === 'activo' && auth()->user()->tienePermiso('editar_promocion'))
                <a href="{{ route('promociones.edit', $promocion) }}" class="btn-secondary">
                    <i class="bi bi-pencil"></i>
                    Editar
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_promociones'))
                <a href="{{ route('promociones.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Precio promocional</span>
            <strong>{{ number_format($promocion->precio_promocional, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Tipo</span>
            <strong>
                @if ($promocion->tipo === 'producto_individual')
                    Producto individual
                @elseif ($promocion->tipo === 'combo')
                    Combo
                @elseif ($promocion->tipo === 'por_vencimiento')
                    Por vencimiento
                @else
                    -
                @endif
            </strong>
        </div>

        <div class="detail-stat-card">
            <span>Sucursal</span>
            <strong>{{ $promocion->sucursal->nombre ?? 'Todas' }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Productos</span>
            <strong>{{ $promocion->items->count() }}</strong>
        </div>
    </div>

    <div class="promo-mini-info">
        <div>
            <span>Inicio</span>
            <strong>{{ $promocion->fecha_inicio ? $promocion->fecha_inicio->format('d/m/Y') : 'Sin inicio' }}</strong>
        </div>

        <div>
            <span>Fin</span>
            <strong>{{ $promocion->fecha_fin ? $promocion->fecha_fin->format('d/m/Y') : 'Sin fin' }}</strong>
        </div>

        <div>
            <span>Motivo</span>
            <strong>{{ $promocion->motivo ?? '-' }}</strong>
        </div>

        <div>
            <span>Creado por</span>
            <strong>{{ $promocion->creadoPor->nombre ?? '-' }}</strong>
        </div>
    </div>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Productos incluidos
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table promo-table">
                <thead>
                    <tr>
                        <th>Producto incluido</th>
                        <th>Forma</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Unidades</th>
                        <th>Precio ref.</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($promocion->items as $item)
                        <tr>
                            <td>
                                <strong>
                                    {{ $item->productoPresentacion->nombre_mostrado ?? $item->producto->nombre_comercial ?? '-' }}
                                </strong>

                                @if ($item->producto?->laboratorio || $item->producto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        @if ($item->producto?->laboratorio)
                                            {{ $item->producto->laboratorio->nombre }}
                                        @endif

                                        @if ($item->producto?->laboratorio && $item->producto?->concentracion)
                                            |
                                        @endif

                                        @if ($item->producto?->concentracion)
                                            {{ $item->producto->concentracion }}
                                        @endif
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $item->productoPresentacion->presentacion->nombre ?? '-' }}

                                @if ($item->productoPresentacion?->unidades_equivalentes)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $item->productoPresentacion->unidades_equivalentes }} unidad(es)
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if ($item->lote)
                                    <strong>{{ $item->lote->numero_lote }}</strong>

                                    @if ($item->lote->fecha_vencimiento)
                                        <br>
                                        <small style="color:#6B7280;">
                                            Vence: {{ $item->lote->fecha_vencimiento->format('d/m/Y') }}
                                        </small>
                                    @endif
                                @else
                                    <small style="color:#6B7280;">No específico</small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $item->cantidad }}</strong>
                            </td>

                            <td>
                                {{ $item->unidades_necesarias }}

                                @if ($item->productoPresentacion?->unidades_equivalentes)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $item->cantidad }} x {{ $item->productoPresentacion->unidades_equivalentes }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ number_format($item->precio_referencia, 2) }} Bs</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table-message">
                                Esta promoción no tiene productos incluidos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection