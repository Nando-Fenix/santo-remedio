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

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">
                {{ $promocion->nombre }}
            </h2>

            <p style="margin: 6px 0 0; color: #6B7280;">
                {{ $promocion->descripcion ?? 'Sin descripción' }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if ($promocion->estado === 'activo' && auth()->user()->tienePermiso('editar_promocion'))
                <a href="{{ route('promociones.edit', $promocion) }}" class="btn-secondary">
                    Editar
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_promociones'))
                <a href="{{ route('promociones.index') }}" class="btn-secondary">
                    Volver
                </a>
            @endif
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <span>Precio promocional</span>
        <h3>{{ number_format($promocion->precio_promocional, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Tipo</span>
        <h3 style="font-size: 18px;">
            @if ($promocion->tipo === 'producto_individual')
                Producto individual
            @elseif ($promocion->tipo === 'combo')
                Combo
            @elseif ($promocion->tipo === 'por_vencimiento')
                Por vencimiento
            @else
                -
            @endif
        </h3>
    </div>

    <div class="stat-card">
        <span>Sucursal</span>
        <h3 style="font-size: 18px;">{{ $promocion->sucursal->nombre ?? 'Todas' }}</h3>
    </div>

    <div class="stat-card">
        <span>Estado</span>
        <h3 style="font-size: 18px;">{{ ucfirst($promocion->estado) }}</h3>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Vigencia y motivo</h3>

    <p>
        <strong>Fecha inicio:</strong>
        {{ $promocion->fecha_inicio ? $promocion->fecha_inicio->format('d/m/Y') : 'Sin inicio definido' }}
        <br>

        <strong>Fecha fin:</strong>
        {{ $promocion->fecha_fin ? $promocion->fecha_fin->format('d/m/Y') : 'Sin fin definido' }}
        <br>

        <strong>Motivo:</strong>
        {{ $promocion->motivo ?? '-' }}
        <br>

        <strong>Creado por:</strong>
        {{ $promocion->creadoPor->nombre ?? '-' }}
    </p>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos incluidos</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto incluido</th>
                    <th>Forma</th>
                    <th>Lote específico</th>
                    <th>Cantidad</th>
                    <th>Unidades necesarias</th>
                    <th>Precio referencia</th>
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
                                <small style="color: #6B7280;">
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
                                <small style="color: #6B7280;">
                                    {{ $item->productoPresentacion->unidades_equivalentes }} unidad(es) por cantidad
                                </small>
                            @endif
                        </td>

                        <td>
                            @if ($item->lote)
                                <strong>{{ $item->lote->numero_lote }}</strong>

                                @if ($item->lote->fecha_vencimiento)
                                    <br>
                                    <small style="color: #6B7280;">
                                        Vence: {{ $item->lote->fecha_vencimiento->format('d/m/Y') }}
                                    </small>
                                @endif
                            @else
                                No específico
                            @endif
                        </td>

                        <td>{{ $item->cantidad }}</td>
                        <td>{{ $item->unidades_necesarias }}</td>
                        <td>{{ number_format($item->precio_referencia, 2) }} Bs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6B7280;">
                            Esta promoción no tiene productos incluidos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection