@extends('layouts.app')

@section('title', 'Detalle de proveedor | Santo Remedio')
@section('page-title', 'Detalle de proveedor')
@section('page-subtitle', 'Datos del proveedor y productos relacionados')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">{{ $proveedor->nombre }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Información general del proveedor.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn-primary">
                Editar proveedor
            </a>

            <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                Volver
            </a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Contacto</span>
            <h3>{{ $proveedor->contacto ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Teléfono</span>
            <h3>{{ $proveedor->telefono ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Estado</span>
            <h3>{{ ucfirst($proveedor->estado) }}</h3>
        </div>

        <div class="stat-card">
            <span>Productos</span>
            <h3>{{ $cantidadProductos }}</h3>
        </div>
    </div>
    @if ($proveedor->direccion)
        <p style="margin-top: 18px;">
            <strong>Dirección:</strong> {{ $proveedor->direccion }}
        </p>
    @endif
</div>

<div class="grid" style="margin-top: 22px;">
    <div class="stat-card">
        <span>Total comprado</span>
        <h3>{{ number_format($totalComprado, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total pagado</span>
        <h3>{{ number_format($totalPagado, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Saldo pendiente</span>
        <h3>{{ number_format($saldoPendiente, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Cantidad de compras</span>
        <h3>{{ $cantidadCompras }}</h3>
    </div>
</div>

<div class="grid">
    <div class="stat-card">
        <span>Productos activos</span>
        <h3>{{ $productosActivos }}</h3>
    </div>

    <div class="stat-card">
        <span>Productos inactivos</span>
        <h3>{{ $productosInactivos }}</h3>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Últimas compras al proveedor</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° compra</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Estado pago</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comprasProveedor as $compra)
                    <tr>
                        <td><strong>{{ $compra->numero_compra }}</strong></td>
                        <td>{{ $compra->fecha_compra->format('d/m/Y H:i') }}</td>
                        <td>{{ number_format($compra->total, 2) }} Bs</td>
                        <td>{{ number_format($compra->monto_pagado, 2) }} Bs</td>
                        <td>{{ number_format($compra->saldo_pendiente, 2) }} Bs</td>
                        <td>
                            @if ($compra->estado_pago === 'pagado')
                                <span class="badge badge-success">Pagado</span>
                            @elseif ($compra->estado_pago === 'parcial')
                                <span class="badge badge-warning">Parcial</span>
                            @else
                                <span class="badge badge-danger">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                                Ver compra
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6B7280;">
                            Este proveedor todavía no tiene compras registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos asociados</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Genérico</th>
                    <th>Concentración</th>
                    <th>Categoría</th>
                    <th>Laboratorio</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td><strong>{{ $producto->nombre_comercial }}</strong></td>
                        <td>{{ $producto->nombre_generico ?? '-' }}</td>
                        <td>{{ $producto->concentracion ?? '-' }}</td>
                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                        <td>{{ $producto->laboratorio->nombre ?? '-' }}</td>
                        <td>
                            @if ($producto->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('productos.edit', $producto) }}" class="btn-secondary">
                                Ver producto
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6B7280;">
                            Este proveedor todavía no tiene productos asociados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $productos->links() }}
    </div>
</div>

@endsection