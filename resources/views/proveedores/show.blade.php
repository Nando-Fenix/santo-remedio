@extends('layouts.app')

@section('title', 'Detalle de proveedor | Santo Remedio')
@section('page-title', 'Detalle de proveedor')
@section('page-subtitle', 'Datos del proveedor, compras y productos relacionados')

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
                <i class="bi bi-truck"></i>
                {{ $proveedor->nombre }}
            </h2>

            <p>
                Información general, compras recientes y productos asociados.
            </p>
        </div>

        <div class="detail-actions">
            @if ($proveedor->estado === 'activo')
                <span class="badge badge-success">Activo</span>
            @else
                <span class="badge badge-danger">Inactivo</span>
            @endif

            @if (auth()->user()->tienePermiso('editar_proveedor'))
                <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn-primary">
                    <i class="bi bi-pencil"></i>
                    Editar
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_proveedores'))
                <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Saldo pendiente</span>
            <strong>{{ number_format($saldoPendiente, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Total comprado</span>
            <strong>{{ number_format($totalComprado, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Total pagado</span>
            <strong>{{ number_format($totalPagado, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Cantidad de compras</span>
            <strong>{{ $cantidadCompras }}</strong>
        </div>
    </div>

    <div class="provider-show-mini-info">
        <div>
            <span>Contacto</span>
            <strong>{{ $proveedor->contacto ?? '-' }}</strong>
        </div>

        <div>
            <span>Teléfono</span>
            <strong>{{ $proveedor->telefono ?? '-' }}</strong>
        </div>

        <div>
            <span>Estado</span>
            <strong>{{ ucfirst($proveedor->estado) }}</strong>
        </div>

        <div>
            <span>Productos asociados</span>
            <strong>{{ $cantidadProductos }}</strong>
        </div>

        <div>
            <span>Productos activos</span>
            <strong>{{ $productosActivos }}</strong>
        </div>

        <div>
            <span>Productos inactivos</span>
            <strong>{{ $productosInactivos }}</strong>
        </div>
    </div>

    @if ($proveedor->direccion)
        <div class="provider-address-box">
            <span>Dirección</span>
            <strong>{{ $proveedor->direccion }}</strong>
        </div>
    @endif

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-bag-check"></i>
                Últimas compras al proveedor
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table provider-purchases-table">
                <thead>
                    <tr>
                        <th>N° compra</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Estado pago</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($comprasProveedor as $compra)
                        <tr>
                            <td>
                                <strong>{{ $compra->numero_compra }}</strong>
                            </td>

                            <td>
                                {{ $compra->fecha_compra->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $compra->fecha_compra->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                {{ number_format($compra->total, 2) }} Bs
                            </td>

                            <td>
                                {{ number_format($compra->monto_pagado, 2) }} Bs
                            </td>

                            <td>
                                @if ($compra->saldo_pendiente > 0)
                                    <strong class="provider-debt-amount">
                                        {{ number_format($compra->saldo_pendiente, 2) }} Bs
                                    </strong>
                                @else
                                    <strong class="provider-paid-amount">
                                        0.00 Bs
                                    </strong>
                                @endif
                            </td>

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
                                <div class="action-group">
                                    @if (auth()->user()->tienePermiso('ver_compras'))
                                        <a
                                            href="{{ route('compras.show', $compra) }}"
                                            class="icon-action icon-action-primary"
                                            title="Ver compra"
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
                            <td colspan="7" class="empty-table-message">
                                Este proveedor todavía no tiene compras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-capsule"></i>
                Productos asociados
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table provider-products-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Genérico</th>
                        <th>Concentración</th>
                        <th>Categoría</th>
                        <th>Laboratorio</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($productos as $producto)
                        <tr>
                            <td>
                                <strong>{{ $producto->nombre_comercial }}</strong>
                            </td>

                            <td>
                                {{ $producto->nombre_generico ?? '-' }}
                            </td>

                            <td>
                                {{ $producto->concentracion ?? '-' }}
                            </td>

                            <td>
                                {{ $producto->categoria->nombre ?? '-' }}
                            </td>

                            <td>
                                {{ $producto->laboratorio->nombre ?? '-' }}
                            </td>

                            <td>
                                @if ($producto->estado === 'activo')
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    @if (auth()->user()->tienePermiso('editar_producto'))
                                        <a
                                            href="{{ route('productos.edit', $producto) }}"
                                            class="icon-action icon-action-edit"
                                            title="Editar producto"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @elseif (auth()->user()->tienePermiso('ver_productos'))
                                        <a
                                            href="{{ route('productos.presentaciones.index', $producto) }}"
                                            class="icon-action icon-action-primary"
                                            title="Ver producto"
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
                            <td colspan="7" class="empty-table-message">
                                Este proveedor todavía no tiene productos asociados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $productos->links() }}
        </div>
    </div>

</div>

@endsection