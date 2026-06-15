@extends('layouts.app')

@section('title', 'Dashboard | Santo Remedio')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen general de la farmacia')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <h2 style="margin-top: 0; color: #4C1D95;">Bienvenido al sistema</h2>

    <p>
        Has iniciado sesión como
        <strong>{{ auth()->user()->nombre }}</strong>.
    </p>

    <p>
        Rol:
        <strong>{{ auth()->user()->rol->nombre ?? 'Sin rol' }}</strong>
        —
        Sucursal:
        <strong>{{ $sucursal->nombre ?? 'Sin sucursal asignada' }}</strong>
    </p>

    @if (!$cajaAbierta)
        <div class="alert-danger" style="margin-bottom: 0;">
            No tienes una caja abierta. Para registrar ventas, primero debes abrir caja.
            <br><br>
            <a href="{{ route('caja.index') }}" class="btn-primary">
                Ir a caja
            </a>
        </div>
    @else
        <div class="alert-success" style="margin-bottom: 0;">
            Caja abierta desde {{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}
            — Turno: {{ $cajaAbierta->turno->nombre ?? '-' }}
        </div>
    @endif
</div>

<div class="grid">
    <div class="stat-card">
        <span>Ventas del día</span>
        <h3>{{ number_format($totalVentasDia, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad de ventas</span>
        <h3>{{ $cantidadVentasDia }}</h3>
    </div>

    <div class="stat-card">
        <span>Caja actual</span>
        <h3>
            @if ($cajaAbierta)
                {{ number_format($cajaAbierta->total_final, 2) }} Bs
            @else
                0.00 Bs
            @endif
        </h3>
    </div>

    <div class="stat-card">
        <span>Productos por vencer</span>
        <h3>{{ $productosPorVencerCantidad }}</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr);">
    <div class="stat-card">
        <span>Productos con stock bajo</span>
        <h3>{{ $stockBajoCantidad }}</h3>
    </div>

    <div class="stat-card">
        <span>Productos agotados</span>
        <h3>{{ $productosAgotadosCantidad }}</h3>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px;">
        <div>
            <h3 style="margin: 0; color: #4C1D95;">Últimas ventas</h3>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Últimas ventas registradas en la sucursal actual.
            </p>
        </div>

        <a href="{{ route('ventas.index') }}" class="btn-secondary">
            Ver ventas
        </a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° venta</th>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Método</th>
                    <th>Total</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ultimasVentas as $venta)
                    <tr>
                        <td>{{ $venta->numero_venta }}</td>
                        <td>{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
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
                        <td>
                            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6B7280;">
                            No hay ventas registradas todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection