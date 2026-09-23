@extends('layouts.app')

@section('title', 'Productos vencidos | Santo Remedio')
@section('page-title', 'Productos vencidos')
@section('page-subtitle', 'Control de lotes vencidos con stock pendiente de baja')

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
            <h2 class="expired-title">
                <i class="bi bi-exclamation-octagon"></i>
                Productos vencidos
            </h2>

            <p>
                Estos productos ya no deben venderse. Deben retirarse mediante una baja de inventario.
            </p>
        </div>

        <div class="detail-actions">
            @if (auth()->user()->tienePermiso('ver_inventario'))
                <a href="{{ route('inventario.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Inventario
                </a>

                <a href="{{ route('inventario.proximos-vencer') }}" class="btn-secondary">
                    <i class="bi bi-calendar-warning"></i>
                    Próximos
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

    <div class="expired-warning-box">
        <i class="bi bi-shield-exclamation"></i>

        <div>
            <strong>Atención</strong>
            <p>
                Los lotes vencidos con stock pendiente deben retirarse del inventario.
                No deben utilizarse en ventas, promociones ni servicios.
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('inventario.productos-vencidos') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar producto</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="Nombre, genérico, concentración o laboratorio"
            >
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('inventario.productos-vencidos') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3 class="expired-section-title">
                <i class="bi bi-calendar-x"></i>
                Lotes vencidos con stock
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table expired-products-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Días vencido</th>
                        <th>Stock</th>
                        <th>Sucursal</th>
                        <th class="table-actions-cell">Acción</th>
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
                                @if ($diasVencido !== null)
                                    <span class="badge badge-danger">
                                        {{ $diasVencido }} día(s)
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <strong class="expired-stock">
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
                                    @else
                                        <span class="no-permission-text">
                                            Sin permiso
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                No hay productos vencidos con stock pendiente.
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