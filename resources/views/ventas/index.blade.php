@extends('layouts.app')

@section('title', 'Ventas | Santo Remedio')
@section('page-title', 'Ventas')
@section('page-subtitle', 'Historial de ventas registradas')

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
                <i class="bi bi-receipt"></i>
                Ventas
            </h2>

            <p>
                Historial de ventas, productos vendidos, promociones aplicadas y métodos de pago.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('realizar_venta'))
            <a href="{{ route('ventas.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nueva venta
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('ventas.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="N° venta, cliente, CI/NIT, producto, promoción o método"
            >
        </div>

        <div class="form-group">
            <label>Desde</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
        </div>

        <div class="form-group">
            <label>Hasta</label>
            <input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="completada" @selected(($estado ?? '') === 'completada')>Completada</option>
                <option value="anulada" @selected(($estado ?? '') === 'anulada')>Anulada</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('ventas.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table sales-table">
            <thead>
                <tr>
                    <th>N° venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Productos / Promociones</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th class="table-actions-cell">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ventas as $venta)
                    <tr>
                        <td>
                            <strong>{{ $venta->numero_venta }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                {{ $venta->sucursal->nombre ?? '-' }}
                            </small>
                        </td>

                        <td>
                            {{ $venta->fecha_hora->format('d/m/Y') }}
                            <br>
                            <small style="color:#6B7280;">
                                {{ $venta->fecha_hora->format('H:i') }}
                            </small>
                        </td>

                        <td>
                            <strong>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                Vendió: {{ $venta->usuario->nombre ?? '-' }}
                            </small>
                        </td>

                        <td>
                            <div class="sale-items-list">
                                @forelse ($venta->detalles as $detalle)
                                    <div class="sale-item-row">
                                        <strong>
                                            {{ $detalle->productoPresentacion->nombre_mostrado
                                                ?? $detalle->producto->nombre_comercial
                                                ?? 'Producto' }}
                                        </strong>

                                        <small>
                                            Cant: {{ $detalle->cantidad }}
                                            ·
                                            {{ number_format($detalle->subtotal, 2) }} Bs
                                        </small>
                                    </div>
                                @empty
                                @endforelse

                                @foreach ($venta->promociones as $promo)
                                    <div class="sale-item-row sale-promo-row">
                                        <strong>
                                            PROMO: {{ $promo->promocion->nombre ?? 'Promoción' }}
                                        </strong>

                                        <small>
                                            Cant: {{ $promo->cantidad }}
                                            ·
                                            {{ number_format($promo->subtotal, 2) }} Bs
                                        </small>
                                    </div>
                                @endforeach

                                @if ($venta->detalles->count() === 0 && $venta->promociones->count() === 0)
                                    <small style="color:#6B7280;">Sin detalle</small>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="payment-badges">
                                @forelse ($venta->pagos as $pago)
                                    <span class="badge badge-soft">
                                        {{ $pago->metodoPago->nombre ?? '-' }}
                                    </span>
                                @empty
                                    -
                                @endforelse
                            </div>
                        </td>

                        <td>
                            <strong>{{ number_format($venta->total, 2) }} Bs</strong>

                            @if (($venta->descuento_total ?? 0) > 0)
                                <br>
                                <small style="color:#DC2626;">
                                    Desc: {{ number_format($venta->descuento_total, 2) }} Bs
                                </small>
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $venta->estado === 'completada' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $venta->estado)) }}
                            </span>
                        </td>

                        <td>
                            <div class="action-group">
                                <a
                                    href="{{ route('ventas.show', $venta) }}"
                                    class="icon-action icon-action-primary"
                                    title="Ver detalle"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <button
                                    type="button"
                                    class="icon-action icon-action-print"
                                    onclick="abrirModalImpresion('{{ route('ventas.recibo', $venta) }}')"
                                    title="Imprimir recibo"
                                >
                                    <i class="bi bi-printer"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-table-message">
                            Todavía no hay ventas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $ventas->links() }}
    </div>

</div>

@endsection