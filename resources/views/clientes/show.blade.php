@extends('layouts.app')

@section('title', 'Detalle de cliente | Santo Remedio')
@section('page-title', 'Detalle de cliente')
@section('page-subtitle', 'Historial de compras y datos del cliente')

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
                <i class="bi bi-person-vcard"></i>
                {{ $cliente->nombre }}
            </h2>

            <p>
                Cliente {{ $cliente->tipo_cliente }} registrado en el sistema.
            </p>
        </div>

        <div class="detail-actions">
            @if ($cliente->estado === 'activo')
                <span class="badge badge-success">Activo</span>
            @else
                <span class="badge badge-danger">Inactivo</span>
            @endif

            @if (auth()->user()->tienePermiso('editar_cliente'))
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn-primary">
                    <i class="bi bi-pencil"></i>
                    Editar
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_clientes'))
                <a href="{{ route('clientes.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Total comprado</span>
            <strong>{{ number_format($totalComprado, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Cantidad de compras</span>
            <strong>{{ $cantidadCompras }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Última compra</span>
            <strong>
                {{ $ultimaCompra ? $ultimaCompra->fecha_hora->format('d/m/Y') : '-' }}
            </strong>
        </div>

        <div class="detail-stat-card">
            <span>Descuento default</span>
            <strong>{{ number_format($cliente->descuento_default, 2) }}%</strong>
        </div>
    </div>

    <div class="client-show-mini-info">
        <div>
            <span>CI/NIT</span>
            <strong>{{ $cliente->ci_nit ?? '-' }}</strong>
        </div>

        <div>
            <span>Teléfono</span>
            <strong>{{ $cliente->telefono ?? '-' }}</strong>
        </div>

        <div>
            <span>Tipo cliente</span>
            <strong>{{ ucfirst($cliente->tipo_cliente) }}</strong>
        </div>

        <div>
            <span>Estado</span>
            <strong>{{ ucfirst($cliente->estado) }}</strong>
        </div>
    </div>

    @if ($cliente->direccion)
        <div class="client-address-box">
            <span>Dirección</span>
            <strong>{{ $cliente->direccion }}</strong>
        </div>
    @endif

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-receipt"></i>
                Historial de compras
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table client-sales-table">
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
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($ventas as $venta)
                        <tr>
                            <td>
                                <strong>{{ $venta->numero_venta }}</strong>
                            </td>

                            <td>
                                {{ $venta->fecha_hora->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $venta->fecha_hora->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                {{ $venta->sucursal->nombre ?? '-' }}
                            </td>

                            <td>
                                {{ $venta->usuario->nombre ?? '-' }}
                            </td>

                            <td>
                                <div class="client-payment-badges">
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
                                {{ number_format($venta->subtotal, 2) }} Bs
                            </td>

                            <td>
                                {{ number_format($venta->descuento_total, 2) }} Bs
                            </td>

                            <td>
                                <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                            </td>

                            <td>
                                <div class="action-group">
                                    @if (auth()->user()->tienePermiso('ver_ventas'))
                                        <a
                                            href="{{ route('ventas.show', $venta) }}"
                                            class="icon-action icon-action-primary"
                                            title="Ver venta"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-table-message">
                                Este cliente todavía no tiene compras registradas.
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

</div>

@endsection