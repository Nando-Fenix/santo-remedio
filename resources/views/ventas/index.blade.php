@extends('layouts.app')

@section('title', 'Ventas | Santo Remedio')
@section('page-title', 'Ventas')
@section('page-subtitle', 'Historial de ventas registradas')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Listado de ventas</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Ventas realizadas en el sistema.
            </p>
        </div>

        <a href="{{ route('ventas.create') }}" class="btn-primary">
            + Nueva venta
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° venta</th>
                    <th>Fecha</th>
                    <th>Sucursal</th>
                    <th>Vendedor</th>
                    <th>Cliente</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ventas as $venta)
                    <tr>
                        <td>{{ $venta->numero_venta }}</td>
                        <td>{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $venta->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $venta->usuario->nombre ?? '-' }}</td>
                        <td>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</td>
                        <td>
                            @forelse ($venta->pagos as $pago)
                                <span class="badge badge-soft">
                                    {{ $pago->metodoPago->nombre ?? '-' }}
                                </span>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>
                            <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                        </td>
                        <td>
                            <span class="badge {{ $venta->estado === 'completada' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $venta->estado)) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6B7280;">
                            Todavía no hay ventas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $ventas->links() }}
    </div>

</div>

@endsection