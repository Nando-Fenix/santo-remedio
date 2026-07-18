@extends('layouts.app')

@section('title', 'Reportes | Santo Remedio')
@section('page-title', 'Reportes')
@section('page-subtitle', 'Resumen de ventas, stock y alertas importantes')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Reporte general</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Consulte ventas del día, pagos, stock bajo y productos próximos a vencer.
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('reportes.index') }}" style="margin-top: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Fecha del reporte</label>
                <input type="date" name="fecha" value="{{ $fecha }}">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Consultar
            </button>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                Hoy
            </a>
        </div>
    </form>
</div>

<div class="grid">
    <div class="stat-card">
        <span>Total vendido</span>
        <h3>{{ number_format($totalVentas, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad de ventas</span>
        <h3>{{ $cantidadVentas }}</h3>
    </div>

    <div class="stat-card">
        <span>Total efectivo</span>
        <h3>{{ number_format($totalEfectivo, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total QR</span>
        <h3>{{ number_format($totalQr, 2) }} Bs</h3>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Ventas del día</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° venta</th>
                    <th>Hora</th>
                    <th>Sucursal</th>
                    <th>Vendedor</th>
                    <th>Método</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ventasDelDia as $venta)
                    <tr>
                        <td>
                            @if (auth()->user()->tienePermiso('ver_ventas'))
                                <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                                    {{ $venta->numero_venta }}
                                </a>
                            @else
                                {{ $venta->numero_venta }}
                            @endif
                        </td>
                        <td>{{ $venta->fecha_hora->format('H:i') }}</td>
                        <td>{{ $venta->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $venta->usuario->nombre ?? '-' }}</td>
                        <td>
                            @foreach ($venta->pagos as $pago)
                                <span class="badge badge-soft">
                                    {{ $pago->metodoPago->nombre ?? '-' }}
                                </span>
                            @endforeach
                        </td>
                        <td>
                            <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6B7280;">
                            No hay ventas registradas en esta fecha.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if (auth()->user()->tienePermiso('ver_inventario'))
<div class="grid" style="grid-template-columns: repeat(2, 1fr);">
    <div class="card">

    
        <h3 style="margin-top: 0; color: #4C1D95;">Stock bajo</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockBajo as $item)
                        <tr>
                            <td>
                                {{ $item->producto->nombre_comercial ?? '-' }}
                                @if($item->producto?->concentracion)
                                    <br>
                                    <small style="color: #6B7280;">{{ $item->producto->concentracion }}</small>
                                @endif
                            </td>
                            <td>{{ $item->sucursal->nombre ?? '-' }}</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $item->stock_actual }}
                                </span>
                            </td>
                            <td>{{ $item->stock_minimo }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6B7280;">
                                No hay productos con stock bajo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top: 0; color: #4C1D95;">Promociones</h3>
        <p style="color: #6B7280;">
            Consulte promociones vendidas, ingresos generados y productos descontados.
        </p>

        <a href="{{ route('reportes.promociones') }}" class="btn-primary">
            Ver reporte
        </a>
    </div>

    @if (auth()->user()->tienePermiso('ver_bajas_inventario'))
        <div class="card">
            <h3 style="margin-top: 0; color: #4C1D95;">Bajas de inventario</h3>
            <p style="color: #6B7280;">
                Consulte productos retirados por vencimiento, daño, pérdida o ajuste autorizado.
            </p>

            <a href="{{ route('bajas-inventario.index') }}" class="btn-primary">
                Ver bajas
            </a>
        </div>
    @endif

    <div class="card">
        <h3 style="margin-top: 0; color: #4C1D95;">Productos próximos a vencer</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Lote</th>
                        <th>Vence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productosPorVencer as $item)
                        <tr>
                            <td>
                                {{ $item->producto->nombre_comercial ?? '-' }}
                                @if($item->producto?->concentracion)
                                    <br>
                                    <small style="color: #6B7280;">{{ $item->producto->concentracion }}</small>
                                @endif
                            </td>
                            <td>{{ $item->sucursal->nombre ?? '-' }}</td>
                            <td>{{ $item->lote->numero_lote ?? 'Sin lote' }}</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $item->lote?->fecha_vencimiento?->format('d/m/Y') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6B7280;">
                                No hay productos próximos a vencer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if (auth()->user()->tienePermiso('ver_inventario'))
<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos agotados</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Lote</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productosAgotados as $item)
                    <tr>
                        <td>
                            {{ $item->producto->nombre_comercial ?? '-' }}
                            @if($item->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">{{ $item->producto->concentracion }}</small>
                            @endif
                        </td>
                        <td>{{ $item->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $item->lote->numero_lote ?? 'Sin lote' }}</td>
                        <td>
                            <span class="badge badge-danger">
                                {{ $item->stock_actual }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #6B7280;">
                            No hay productos agotados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection