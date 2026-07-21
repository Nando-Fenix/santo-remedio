@extends('layouts.app')

@section('title', 'Dashboard | Santo Remedio')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen general de la farmacia')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; flex-wrap: wrap;">
        <div>
            <h2 style="margin-top: 0; color: #4C1D95;">Bienvenido al sistema</h2>

            <p style="margin-bottom: 6px;">
                Has iniciado sesión como
                <strong>{{ auth()->user()->nombre }}</strong>.
            </p>

            <p style="margin-bottom: 0;">
                Rol:
                <strong>{{ auth()->user()->rol->nombre ?? 'Sin rol' }}</strong>
                —
                Sucursal:
                <strong>{{ $sucursal->nombre ?? 'Sin sucursal asignada' }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if (auth()->user()->tienePermiso('realizar_venta'))
                <a href="{{ route('ventas.create') }}" class="btn-primary">
                    Nueva venta
                </a>
            @endif

            @if (auth()->user()->tienePermiso('registrar_atencion_servicio'))
                <a href="{{ route('atenciones-servicio.create') }}" class="btn-primary">
                    Registrar atención
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_atenciones_servicio'))
                <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                    Ver atenciones
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_servicios_farmacia'))
                <a href="{{ route('servicios-farmacia.index') }}" class="btn-secondary">
                    Servicios
                </a>
            @endif
        </div>
    </div>

    @if (auth()->user()->tienePermiso('ver_caja'))
        <div style="margin-top: 18px;">
            @if (!$cajaAbierta)
                <div class="alert-danger" style="margin-bottom: 0;">
                    No existe una caja abierta en esta sucursal. Para registrar ventas o servicios, primero debe abrirse una caja.
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
    @endif
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr);">
    @if (auth()->user()->tienePermiso('ver_ventas'))
        <div class="stat-card">
            <span>Ventas del día</span>
            <h3>{{ number_format($totalVentasDia ?? 0, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Cantidad de ventas</span>
            <h3>{{ $cantidadVentasDia ?? 0 }}</h3>
        </div>
    @endif

    @if (auth()->user()->tienePermiso('ver_atenciones_servicio'))
        <div class="stat-card">
            <span>Servicios hoy</span>
            <h3>{{ number_format($ingresosServiciosHoy ?? 0, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Atenciones hoy</span>
            <h3>{{ $atencionesServiciosHoy ?? 0 }}</h3>
        </div>
    @endif
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px;">
    @if (auth()->user()->tienePermiso('ver_caja'))
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
    @endif

    @if (auth()->user()->tienePermiso('ver_ventas') || auth()->user()->tienePermiso('ver_atenciones_servicio'))
        <div class="stat-card">
            <span>Ingresos totales hoy</span>
            <h3>{{ number_format($ingresosTotalesHoy ?? 0, 2) }} Bs</h3>
        </div>
    @endif

    @if (auth()->user()->tienePermiso('ver_atenciones_servicio'))
        <div class="stat-card">
            <span>Servicios anulados</span>
            <h3>{{ $serviciosAnuladosHoy ?? 0 }}</h3>
        </div>
    @endif

    @if (auth()->user()->tienePermiso('ver_inventario'))
        <div class="stat-card">
            <span>Productos por vencer</span>
            <h3>{{ $productosPorVencerCantidad ?? 0 }}</h3>
        </div>
    @endif
</div>

@if (auth()->user()->tienePermiso('ver_inventario'))
    <div class="grid" style="grid-template-columns: repeat(2, 1fr); margin-top: 22px;">
        <div class="stat-card">
            <span>Productos con stock bajo</span>
            <h3>{{ $stockBajoCantidad ?? 0 }}</h3>
        </div>

        <div class="stat-card">
            <span>Productos agotados</span>
            <h3>{{ $productosAgotadosCantidad ?? 0 }}</h3>
        </div>
    </div>
@endif

@if (auth()->user()->tienePermiso('ver_ventas'))
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
@endif

@if (auth()->user()->tienePermiso('ver_atenciones_servicio') && isset($ultimasAtencionesServicio))
    <div class="card" style="margin-top: 22px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px;">
            <div>
                <h3 style="margin: 0; color: #4C1D95;">Últimas atenciones de servicio</h3>
                <p style="margin: 6px 0 0; color: #6B7280;">
                    Servicios registrados recientemente en la sucursal actual.
                </p>
            </div>

            <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                Ver atenciones
            </a>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acción</th>
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
                                <a href="{{ route('atenciones-servicio.show', $atencion) }}" class="btn-secondary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #6B7280;">
                                No hay atenciones registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection