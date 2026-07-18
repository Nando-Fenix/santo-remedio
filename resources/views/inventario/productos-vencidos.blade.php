@extends('layouts.app')

@section('title', 'Productos vencidos | Santo Remedio')
@section('page-title', 'Productos vencidos')
@section('page-subtitle', 'Control de lotes vencidos con stock pendiente de baja')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="margin: 0; color: #991B1B;">Productos vencidos</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Estos productos ya no deben venderse. Deben retirarse mediante una baja de inventario.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if (auth()->user()->tienePermiso('ver_inventario'))
                <a href="{{ route('inventario.index') }}" class="btn-secondary">
                    Volver a inventario
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_bajas_inventario'))
                <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                    Bajas de inventario
                </a>
            @endif
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <form method="GET" action="{{ route('inventario.productos-vencidos') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre, genérico, concentración o laboratorio"
                >
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('inventario.productos-vencidos') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #991B1B;">
        Lotes vencidos con stock
    </h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Días vencido</th>
                    <th>Stock</th>
                    <th>Sucursal</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($inventarios as $inventario)
                    @php
                        $fechaVencimiento = $inventario->lote?->fecha_vencimiento;
                        $diasVencido = $fechaVencimiento
                            ? $fechaVencimiento->diffInDays($hoy, false)
                            : null;
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $inventario->producto->nombre_comercial ?? '-' }}</strong>

                            @if ($inventario->producto?->laboratorio || $inventario->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
                                    @if ($inventario->producto?->laboratorio)
                                        {{ $inventario->producto->laboratorio->nombre }}
                                    @endif

                                    @if ($inventario->producto?->laboratorio && $inventario->producto?->concentracion)
                                        |
                                    @endif

                                    @if ($inventario->producto?->concentracion)
                                        {{ $inventario->producto->concentracion }}
                                    @endif
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ $inventario->lote->numero_lote ?? 'Sin lote' }}
                        </td>

                        <td>
                            {{ $fechaVencimiento ? $fechaVencimiento->format('d/m/Y') : '-' }}
                        </td>

                        <td>
                            @if ($diasVencido !== null)
                                <span class="badge badge-danger">
                                    {{ $diasVencido }} día(s)
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <strong>{{ $inventario->stock_actual }}</strong>
                        </td>

                        <td>
                            {{ $inventario->sucursal->nombre ?? '-' }}
                        </td>

                        <td>
                            @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
                                <a href="{{ route('bajas-inventario.create', ['inventario_id' => $inventario->id]) }}" class="btn-danger">
                                    Registrar baja
                                </a>
                            @else
                                <span style="color: #6B7280;">Sin permiso</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6B7280;">
                            No hay productos vencidos con stock pendiente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $inventarios->links() }}
    </div>
</div>

@endsection