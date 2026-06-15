@extends('layouts.app')

@section('title', 'Detalle de cliente | Santo Remedio')
@section('page-title', 'Detalle de cliente')
@section('page-subtitle', 'Historial de compras y datos del cliente')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">{{ $cliente->nombre }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Cliente {{ $cliente->tipo_cliente }} registrado en el sistema.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn-primary">
                Editar cliente
            </a>

            <a href="{{ route('clientes.index') }}" class="btn-secondary">
                Volver
            </a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>CI/NIT</span>
            <h3>{{ $cliente->ci_nit ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Teléfono</span>
            <h3>{{ $cliente->telefono ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Descuento</span>
            <h3>{{ number_format($cliente->descuento_default, 2) }}%</h3>
        </div>

        <div class="stat-card">
            <span>Estado</span>
            <h3>{{ ucfirst($cliente->estado) }}</h3>
        </div>
    </div>

    @if ($cliente->direccion)
        <p style="margin-top: 18px;">
            <strong>Dirección:</strong> {{ $cliente->direccion }}
        </p>
    @endif
</div>

<div class="grid">
    <div class="stat-card">
        <span>Total comprado</span>
        <h3>{{ number_format($totalComprado, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad de compras</span>
        <h3>{{ $cantidadCompras }}</h3>
    </div>

    <div class="stat-card">
        <span>Última compra</span>
        <h3>
            {{ $ultimaCompra ? $ultimaCompra->fecha_hora->format('d/m/Y') : '-' }}
        </h3>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Historial de compras</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° venta</th>
                    <th>Fecha</th>
                    <th>Sucursal</th>
                    <th>Vendedor</th>
                    <th>Método pago</th>
                    <th>Subtotal</th>
                    <th>Descuento</th>
                    <th>Total</th>
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
                        <td>
                            @foreach ($venta->pagos as $pago)
                                <span class="badge badge-soft">
                                    {{ $pago->metodoPago->nombre ?? '-' }}
                                </span>
                            @endforeach
                        </td>
                        <td>{{ number_format($venta->subtotal, 2) }} Bs</td>
                        <td>{{ number_format($venta->descuento_total, 2) }} Bs</td>
                        <td>
                            <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                        </td>
                        <td>
                            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                                Ver venta
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6B7280;">
                            Este cliente todavía no tiene compras registradas.
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