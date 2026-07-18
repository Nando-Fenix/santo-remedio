@extends('layouts.app')

@section('title', 'Productos próximos a vencer | Santo Remedio')
@section('page-title', 'Productos próximos a vencer')
@section('page-subtitle', 'Control de lotes con fecha de vencimiento cercana')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Productos próximos a vencer</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Revise productos que deben promocionarse, venderse pronto o retirarse antes de caducar.
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
    <form method="GET" action="{{ route('inventario.proximos-vencer') }}">
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

            <div class="form-group">
                <label>Ver productos que vencen en</label>
                <select name="dias">
                    <option value="7" @selected((int) $dias === 7)>7 días</option>
                    <option value="15" @selected((int) $dias === 15)>15 días</option>
                    <option value="30" @selected((int) $dias === 30)>30 días</option>
                    <option value="60" @selected((int) $dias === 60)>60 días</option>
                    <option value="90" @selected((int) $dias === 90)>90 días</option>
                    <option value="180" @selected((int) $dias === 180)>180 días</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Filtrar
            </button>

            <a href="{{ route('inventario.proximos-vencer') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">
        Lotes próximos a vencer
    </h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Días restantes</th>
                    <th>Stock</th>
                    <th>Sucursal</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($inventarios as $inventario)
                    @php
                        $fechaVencimiento = $inventario->lote?->fecha_vencimiento;
                        $diasRestantes = $fechaVencimiento
                            ? $hoy->diffInDays($fechaVencimiento, false)
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
                            @if ($diasRestantes !== null)
                                @if ($diasRestantes <= 7)
                                    <span class="badge badge-danger">
                                        {{ $diasRestantes }} día(s)
                                    </span>
                                @elseif ($diasRestantes <= 30)
                                    <span class="badge badge-warning">
                                        {{ $diasRestantes }} día(s)
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        {{ $diasRestantes }} día(s)
                                    </span>
                                @endif
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
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
                                    <a href="{{ route('bajas-inventario.create', ['inventario_id' => $inventario->id]) }}" class="btn-danger">
                                        Registrar baja
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('crear_promocion'))
                                    <a href="{{ route('promociones.create', ['inventario_id' => $inventario->id]) }}" class="btn-primary">
                                        Crear promoción
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6B7280;">
                            No hay productos próximos a vencer en el rango seleccionado.
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