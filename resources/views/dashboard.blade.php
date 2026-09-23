@extends('layouts.app')

@section('title', 'Panel principal | Santo Remedio')
@section('page-title', 'Panel principal')
@section('page-subtitle', 'Resumen general de la farmacia')

@section('content')

<div class="dashboard-page">

    <div class="compact-card dashboard-hero">
        <div>
            <span class="dashboard-eyebrow">Bienvenido</span>

            <h2>
                {{ auth()->user()->nombre }}
            </h2>

            <p>
                Rol:
                <strong>{{ auth()->user()->rol->nombre ?? 'Sin rol' }}</strong>
                <span>•</span>
                Sucursal:
                <strong>{{ $sucursal->nombre ?? 'Sin sucursal asignada' }}</strong>
            </p>
        </div>

        <div class="dashboard-quick-actions">
            @if (auth()->user()->tienePermiso('realizar_venta'))
                <a href="{{ route('ventas.create') }}" class="btn-primary">
                    <i class="bi bi-cart-plus"></i>
                    Nueva venta
                </a>
            @endif

            @if (auth()->user()->tienePermiso('registrar_atencion_servicio'))
                <a href="{{ route('atenciones-servicio.create') }}" class="btn-secondary">
                    <i class="bi bi-clipboard2-plus"></i>
                    Atención
                </a>
            @endif
        </div>
    </div>

    @if (auth()->user()->tienePermiso('ver_caja'))
        @if (!$cajaAbierta)
            <div class="dashboard-alert dashboard-alert-danger">
                <div>
                    <strong>Caja cerrada</strong>
                    <span>Para registrar ventas o servicios, primero debe abrirse una caja.</span>
                </div>

                <a href="{{ route('caja.index') }}" class="btn-primary">
                    Ir a caja
                </a>
            </div>
        @else
            <div class="dashboard-alert dashboard-alert-success">
                <div>
                    <strong>Caja abierta</strong>
                    <span>
                        Desde {{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}
                        — Turno: {{ $cajaAbierta->turno->nombre ?? '-' }}
                    </span>
                </div>
            </div>
        @endif
    @endif

    <div class="dashboard-stats-grid">
        @if (auth()->user()->tienePermiso('ver_ventas'))
            <div class="dashboard-stat-card">
                <span>Ventas del día</span>
                <strong>{{ number_format($totalVentasDia ?? 0, 2) }} Bs</strong>
            </div>

            <div class="dashboard-stat-card">
                <span>Cantidad de ventas</span>
                <strong>{{ $cantidadVentasDia ?? 0 }}</strong>
            </div>
        @endif

        @if (auth()->user()->tienePermiso('ver_atenciones_servicio'))
            <div class="dashboard-stat-card">
                <span>Servicios hoy</span>
                <strong>{{ number_format($ingresosServiciosHoy ?? 0, 2) }} Bs</strong>
            </div>

            <div class="dashboard-stat-card">
                <span>Atenciones hoy</span>
                <strong>{{ $atencionesServiciosHoy ?? 0 }}</strong>
            </div>
        @endif

        @if (auth()->user()->tienePermiso('ver_caja'))
            <div class="dashboard-stat-card">
                <span>Caja actual</span>
                <strong>
                    {{ number_format($cajaAbierta?->total_final ?? 0, 2) }} Bs
                </strong>
            </div>
        @endif

        @if (auth()->user()->tienePermiso('ver_ventas') || auth()->user()->tienePermiso('ver_atenciones_servicio'))
            <div class="dashboard-stat-card dashboard-stat-main">
                <span>Ingresos totales hoy</span>
                <strong>{{ number_format($ingresosTotalesHoy ?? 0, 2) }} Bs</strong>
            </div>
        @endif

        @if (auth()->user()->tienePermiso('ver_inventario'))
            <div class="dashboard-stat-card dashboard-stat-warning">
                <span>Stock bajo</span>
                <strong>{{ $stockBajoCantidad ?? 0 }}</strong>
            </div>

            <div class="dashboard-stat-card dashboard-stat-danger">
                <span>Agotados</span>
                <strong>{{ $productosAgotadosCantidad ?? 0 }}</strong>
            </div>

            <div class="dashboard-stat-card dashboard-stat-warning">
                <span>Por vencer</span>
                <strong>{{ $productosPorVencerCantidad ?? 0 }}</strong>
            </div>
        @endif
    </div>

    <div class="dashboard-content-grid">
        @if (auth()->user()->tienePermiso('ver_ventas'))
            <div class="compact-card dashboard-chart-card">
                <div class="dashboard-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-bar-chart"></i>
                            Productos más vendidos
                        </h3>
                        <p>Top 5 de los últimos 30 días.</p>
                    </div>
                </div>

                <div class="dashboard-top-product">
                    <span>Más vendido</span>
                    <strong>{{ $productoMasVendidoNombre ?? 'Sin ventas' }}</strong>
                    <small>{{ number_format($productoMasVendidoCantidad ?? 0, 0) }} unidad(es)</small>
                </div>

                <div class="dashboard-bars">
                    @forelse ($productosMasVendidos ?? [] as $item)
                        @php
                            $maximo = max(($productoMasVendidoCantidad ?? 0), 1);
                            $porcentaje = min(100, (($item->total_vendido ?? 0) / $maximo) * 100);
                        @endphp

                        <div class="dashboard-bar-item">
                            <div class="dashboard-bar-info">
                                <span>{{ $item->producto->nombre_comercial ?? 'Producto' }}</span>
                                <strong>{{ number_format($item->total_vendido ?? 0, 0) }}</strong>
                            </div>

                            <div class="dashboard-bar-track">
                                <div class="dashboard-bar-fill" style="width: {{ $porcentaje }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">
                            No hay ventas suficientes para generar el gráfico.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if (auth()->user()->tienePermiso('ver_ventas'))
            <div class="compact-card dashboard-list-card">
                <div class="dashboard-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-receipt"></i>
                            Últimas ventas
                        </h3>
                        <p>Movimientos recientes de la sucursal.</p>
                    </div>

                    <a href="{{ route('ventas.index') }}" class="btn-secondary">
                        Ver
                    </a>
                </div>

                <div class="dashboard-mini-list">
                    @forelse ($ultimasVentas as $venta)
                        <a href="{{ route('ventas.show', $venta) }}" class="dashboard-mini-item">
                            <div>
                                <strong>{{ $venta->numero_venta }}</strong>
                                <span>{{ $venta->fecha_hora->format('d/m/Y H:i') }}</span>
                            </div>

                            <b>{{ number_format($venta->total, 2) }} Bs</b>
                        </a>
                    @empty
                        <div class="dashboard-empty">
                            No hay ventas registradas todavía.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    @if (auth()->user()->tienePermiso('ver_atenciones_servicio') && isset($ultimasAtencionesServicio))
        <div class="compact-card dashboard-list-card">
            <div class="dashboard-section-head">
                <div>
                    <h3>
                        <i class="bi bi-heart-pulse"></i>
                        Últimas atenciones de servicio
                    </h3>
                    <p>Servicios registrados recientemente.</p>
                </div>

                <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                    Ver atenciones
                </a>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="table-actions-cell">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ultimasAtencionesServicio as $atencion)
                            <tr>
                                <td>{{ $atencion->servicio->nombre ?? '-' }}</td>
                                <td>{{ $atencion->fecha_hora->format('d/m/Y H:i') }}</td>
                                <td>{{ $atencion->cliente->nombre ?? 'Consumidor final' }}</td>
                                <td>
                                    <strong>{{ number_format($atencion->total, 2) }} Bs</strong>
                                </td>

                                <td>
                                    @if ($atencion->estado === 'completada')
                                        <span class="badge badge-success">Completada</span>
                                    @else
                                        <span class="badge badge-danger">Anulada</span>
                                    @endif
                                </td>

                                <td>
                                    <a
                                        href="{{ route('atenciones-servicio.show', $atencion) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-table-message">
                                    No hay atenciones registradas todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

@endsection