@extends('layouts.app')

@section('title', 'Reportes | Santo Remedio')
@section('page-title', 'Reportes')
@section('page-subtitle', 'Panel general de reportes del sistema')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <h2 style="margin:0; color:#4C1D95;">Centro de reportes</h2>

    <p style="margin:8px 0 0; color:#6B7280;">
        Accede a los reportes administrativos, ventas, inventario, compras, caja y servicios de farmacia.
    </p>
</div>

{{-- ADMINISTRACIÓN --}}
<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Administración general</h3>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <span>Resumen completo</span>
            <h3>Resumen administrativo</h3>
            <p style="color:#6B7280;">
                Indicadores generales de ventas, servicios, compras, utilidad, caja e inventario crítico.
            </p>

            <a href="{{ route('reportes.resumen-administrativo') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Ganancia aproximada</span>
            <h3>Utilidad estimada</h3>
            <p style="color:#6B7280;">
                Ganancia aproximada según precio de venta y precio de compra registrado.
            </p>

            <a href="{{ route('reportes.utilidad-estimada') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Ingresos globales</span>
            <h3>Ingresos diarios</h3>
            <p style="color:#6B7280;">
                Resumen general de ingresos por ventas y servicios de farmacia.
            </p>

            <a href="{{ route('reportes.ingresos-diarios') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>
    </div>
</div>

{{-- VENTAS --}}
<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Ventas e ingresos</h3>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <span>Ventas</span>
            <h3>Reporte de ventas</h3>
            <p style="color:#6B7280;">
                Ventas completadas, anuladas, descuentos y métodos de pago.
            </p>

            <a href="{{ route('reportes.ventas') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Ranking</span>
            <h3>Productos vendidos</h3>
            <p style="color:#6B7280;">
                Ranking de productos vendidos por cantidad e ingresos generados.
            </p>

            <a href="{{ route('reportes.productos-vendidos') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Clientes</span>
            <h3>Clientes frecuentes</h3>
            <p style="color:#6B7280;">
                Ranking de clientes por compras, total comprado y ticket promedio.
            </p>

            <a href="{{ route('reportes.clientes-frecuentes') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Promociones</span>
            <h3>Promociones vendidas</h3>
            <p style="color:#6B7280;">
                Promociones vendidas y productos descontados por cada promoción.
            </p>

            <a href="{{ route('reportes.promociones') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Pagos</span>
            <h3>Métodos de pago</h3>
            <p style="color:#6B7280;">
                Resumen de ingresos por efectivo, QR, transferencia u otros métodos.
            </p>

            <a href="{{ route('reportes.metodos-pago') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>
    </div>
</div>

{{-- INVENTARIO --}}
<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Inventario</h3>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <span>Alertas</span>
            <h3>Inventario crítico</h3>
            <p style="color:#6B7280;">
                Productos agotados, con stock bajo, próximos a vencer o vencidos.
            </p>

            <a href="{{ route('reportes.inventario-critico') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Reposición</span>
            <h3>Productos a reponer</h3>
            <p style="color:#6B7280;">
                Productos agotados o con stock bajo, priorizados según ventas recientes.
            </p>

            <a href="{{ route('reportes.productos-reponer') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Historial</span>
            <h3>Movimientos de inventario</h3>
            <p style="color:#6B7280;">
                Historial de entradas, salidas, bajas y ajustes de stock.
            </p>

            <a href="{{ route('reportes.movimientos-inventario') }}" class="btn-primary">
                Ver reporte
            </a>

            <a href="{{ route('reportes.bajas-inventario') }}" class="btn-secondary">
                Bajas de inventario
            </a>
        </div>
    </div>
</div>

{{-- COMPRAS --}}
<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Compras y proveedores</h3>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <span>Compras</span>
            <h3>Reporte de compras</h3>
            <p style="color:#6B7280;">
                Compras, pagos realizados, saldos pendientes y anulaciones.
            </p>

            <a href="{{ route('reportes.compras') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>

        <div class="stat-card">
            <span>Deudas</span>
            <h3>Deudas a proveedores</h3>
            <p style="color:#6B7280;">
                Control de compras pendientes agrupadas por proveedor.
            </p>

            <a href="{{ route('reportes.deudas-proveedores') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>
    </div>
</div>

{{-- CAJA --}}
<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Caja</h3>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <span>Control diario</span>
            <h3>Caja diaria</h3>
            <p style="color:#6B7280;">
                Resumen de ventas, servicios, egresos, anulaciones y total final de caja.
            </p>

            <a href="{{ route('reportes.caja-diaria') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>
    </div>
</div>

{{-- SERVICIOS --}}
<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Servicios de farmacia</h3>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <span>Atenciones</span>
            <h3>Servicios</h3>
            <p style="color:#6B7280;">
                Reporte de servicios realizados, ingresos e insumos utilizados.
            </p>

            <a href="{{ route('reportes.servicios') }}" class="btn-primary">
                Ver reporte
            </a>
        </div>
    </div>
</div>

@endsection