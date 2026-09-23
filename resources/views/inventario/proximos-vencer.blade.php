@extends('layouts.app')

@section('title', 'Productos próximos a vencer | Santo Remedio')
@section('page-title', 'Productos próximos a vencer')
@section('page-subtitle', 'Control de lotes con fecha de vencimiento cercana')

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
                <i class="bi bi-calendar-warning"></i>
                Productos próximos a vencer
            </h2>

            <p>
                Revise productos que deben promocionarse, venderse pronto o retirarse antes de caducar.
            </p>
        </div>

        <div class="detail-actions">
            @if (auth()->user()->tienePermiso('ver_inventario'))
                <a href="{{ route('inventario.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Inventario
                </a>

                <a href="{{ route('inventario.productos-vencidos') }}" class="btn-danger">
                    <i class="bi bi-exclamation-octagon"></i>
                    Vencidos
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_bajas_inventario'))
                <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                    <i class="bi bi-archive"></i>
                    Bajas
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('inventario.proximos-vencer') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar producto</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="Nombre, genérico, concentración o laboratorio"
            >
        </div>

        <div class="form-group">
            <label>Vencen en</label>
            <select name="dias">
                <option value="7" @selected((int) $dias === 7)>7 días</option>
                <option value="15" @selected((int) $dias === 15)>15 días</option>
                <option value="30" @selected((int) $dias === 30)>30 días</option>
                <option value="60" @selected((int) $dias === 60)>60 días</option>
                <option value="90" @selected((int) $dias === 90)>90 días</option>
                <option value="180" @selected((int) $dias === 180)>180 días</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-funnel"></i>
                Filtrar
            </button>

            <a href="{{ route('inventario.proximos-vencer') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-hourglass-split"></i>
                Lotes próximos a vencer
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table expiring-products-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Días</th>
                        <th>Stock</th>
                        <th>Sucursal</th>
                        <th class="table-actions-cell">Acciones</th>
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
                                    <small style="color:#6B7280;">
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
                                <strong>{{ $inventario->lote->numero_lote ?? 'Sin lote' }}</strong>
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
                                <strong class="expiring-stock">
                                    {{ $inventario->stock_actual }}
                                </strong>
                            </td>

                            <td>
                                {{ $inventario->sucursal->nombre ?? '-' }}
                            </td>

                            <td>
                                <div class="action-group">
                                    @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
                                        <a
                                            href="{{ route('bajas-inventario.create', ['inventario_id' => $inventario->id]) }}"
                                            class="icon-action icon-action-danger"
                                            title="Registrar baja"
                                        >
                                            <i class="bi bi-dash-circle"></i>
                                        </a>
                                    @endif

                                    @if (auth()->user()->tienePermiso('crear_promocion'))
                                        <a
                                            href="{{ route('promociones.create', ['inventario_id' => $inventario->id]) }}"
                                            class="icon-action icon-action-edit"
                                            title="Crear promoción"
                                        >
                                            <i class="bi bi-tags"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                No hay productos próximos a vencer en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $inventarios->links() }}
        </div>
    </div>

</div>

@endsection 