@extends('layouts.app')

@section('title', 'Reportes | Santo Remedio')
@section('page-title', 'Reportes')
@section('page-subtitle', 'Panel general de reportes del sistema')

@section('content')

<div class="reports-workspace">

    <div class="reports-topbar compact-card">
        <div class="reports-topbar-title">
            <h2>
                <i class="bi bi-bar-chart-line"></i>
                Centro de reportes
            </h2>
            <p>
                Elija un módulo o busque directamente el reporte que necesita.
            </p>
        </div>

        <div class="reports-search-box">
            <i class="bi bi-search"></i>
            <input
                type="text"
                id="buscar_reporte"
                placeholder="Buscar: ventas, caja, stock, deudas..."
                autocomplete="off"
            >
        </div>
    </div>

    <div class="reports-fast-access">
        <a href="{{ route('reportes.ventas') }}" class="quick-report">
            <i class="bi bi-receipt"></i>
            <span>Ventas</span>
        </a>

        <a href="{{ route('reportes.caja-diaria') }}" class="quick-report">
            <i class="bi bi-cash-stack"></i>
            <span>Caja diaria</span>
        </a>

        <a href="{{ route('reportes.inventario-critico') }}" class="quick-report quick-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <span>Inventario crítico</span>
        </a>

        <a href="{{ route('reportes.productos-vendidos') }}" class="quick-report">
            <i class="bi bi-trophy"></i>
            <span>Productos vendidos</span>
        </a>

        <a href="{{ route('reportes.resumen-administrativo') }}" class="quick-report">
            <i class="bi bi-speedometer2"></i>
            <span>Resumen</span>
        </a>
    </div>

    <div class="reports-tabs-card">
        <div class="reports-tabs">
            <button type="button" class="report-tab active" data-panel="panel_usados">
                <i class="bi bi-lightning-charge"></i>
                Más usados
            </button>

            <button type="button" class="report-tab" data-panel="panel_ventas">
                <i class="bi bi-receipt"></i>
                Ventas
            </button>

            <button type="button" class="report-tab" data-panel="panel_inventario">
                <i class="bi bi-box-seam"></i>
                Inventario
            </button>

            <button type="button" class="report-tab" data-panel="panel_compras">
                <i class="bi bi-bag-plus"></i>
                Compras
            </button>

            <button type="button" class="report-tab" data-panel="panel_caja">
                <i class="bi bi-cash-stack"></i>
                Caja
            </button>

            <button type="button" class="report-tab" data-panel="panel_servicios">
                <i class="bi bi-heart-pulse"></i>
                Servicios
            </button>

            <button type="button" class="report-tab" data-panel="panel_admin">
                <i class="bi bi-speedometer2"></i>
                Admin
            </button>
        </div>

        <div class="reports-panels">

            <div class="report-panel active" id="panel_usados">
                <div class="report-panel-head">
                    <h3>Reportes más usados</h3>
                    <span>Accesos principales para consulta rápida</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.ventas') }}" class="report-row report-important" data-report="ventas completadas anuladas descuentos metodos pago">
                        <i class="bi bi-cart-check"></i>
                        <div>
                            <strong>Reporte de ventas</strong>
                            <small>Ventas, anulaciones, descuentos y pagos.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.caja-diaria') }}" class="report-row report-important" data-report="caja diaria ventas servicios egresos anulaciones cierre total final">
                        <i class="bi bi-safe"></i>
                        <div>
                            <strong>Caja diaria</strong>
                            <small>Ventas, servicios, egresos, anulaciones y total final.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.inventario-critico') }}" class="report-row report-danger" data-report="inventario critico stock bajo agotados vencidos proximos vencer">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>
                            <strong>Inventario crítico</strong>
                            <small>Agotados, bajo stock, próximos y vencidos.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.productos-vendidos') }}" class="report-row" data-report="productos vendidos ranking cantidad ingresos">
                        <i class="bi bi-trophy"></i>
                        <div>
                            <strong>Productos vendidos</strong>
                            <small>Ranking por cantidad e ingresos.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.resumen-administrativo') }}" class="report-row" data-report="resumen administrativo ventas servicios compras utilidad caja inventario">
                        <i class="bi bi-clipboard-data"></i>
                        <div>
                            <strong>Resumen administrativo</strong>
                            <small>Indicadores generales de todo el sistema.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

            <div class="report-panel" id="panel_ventas">
                <div class="report-panel-head">
                    <h3>Ventas e ingresos</h3>
                    <span>Ventas, clientes, promociones y métodos de pago</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.ventas') }}" class="report-row report-important" data-report="ventas completadas anuladas descuentos metodos pago">
                        <i class="bi bi-cart-check"></i>
                        <div>
                            <strong>Reporte de ventas</strong>
                            <small>Ventas, anulaciones, descuentos y pagos.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.productos-vendidos') }}" class="report-row" data-report="productos vendidos ranking cantidad ingresos">
                        <i class="bi bi-trophy"></i>
                        <div>
                            <strong>Productos vendidos</strong>
                            <small>Ranking por cantidad e ingresos.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.clientes-frecuentes') }}" class="report-row" data-report="clientes frecuentes compras ticket promedio">
                        <i class="bi bi-people"></i>
                        <div>
                            <strong>Clientes frecuentes</strong>
                            <small>Clientes con más compras registradas.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.promociones') }}" class="report-row" data-report="promociones vendidas combos descuentos">
                        <i class="bi bi-tags"></i>
                        <div>
                            <strong>Promociones vendidas</strong>
                            <small>Promociones y productos descontados.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.metodos-pago') }}" class="report-row" data-report="metodos pago efectivo qr transferencia ingresos">
                        <i class="bi bi-credit-card"></i>
                        <div>
                            <strong>Métodos de pago</strong>
                            <small>Efectivo, QR, transferencia u otros.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

            <div class="report-panel" id="panel_inventario">
                <div class="report-panel-head">
                    <h3>Inventario</h3>
                    <span>Stock, vencimientos, reposición y bajas</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.inventario-critico') }}" class="report-row report-danger" data-report="inventario critico stock bajo agotados vencidos proximos vencer">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>
                            <strong>Inventario crítico</strong>
                            <small>Agotados, bajo stock, próximos y vencidos.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.productos-reponer') }}" class="report-row" data-report="productos reponer reposicion stock bajo agotado">
                        <i class="bi bi-arrow-repeat"></i>
                        <div>
                            <strong>Productos a reponer</strong>
                            <small>Prioridad de reposición según stock y ventas.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.movimientos-inventario') }}" class="report-row" data-report="movimientos inventario entradas salidas ajustes bajas">
                        <i class="bi bi-arrow-left-right"></i>
                        <div>
                            <strong>Movimientos de inventario</strong>
                            <small>Entradas, salidas, bajas y ajustes.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.bajas-inventario') }}" class="report-row report-danger" data-report="bajas inventario vencimiento daño perdida retiro">
                        <i class="bi bi-archive"></i>
                        <div>
                            <strong>Bajas de inventario</strong>
                            <small>Productos retirados del stock.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

            <div class="report-panel" id="panel_compras">
                <div class="report-panel-head">
                    <h3>Compras y proveedores</h3>
                    <span>Compras, pagos y deudas pendientes</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.compras') }}" class="report-row" data-report="compras pagos saldos anulaciones proveedores">
                        <i class="bi bi-bag-check"></i>
                        <div>
                            <strong>Reporte de compras</strong>
                            <small>Compras, pagos, saldos y anulaciones.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.deudas-proveedores') }}" class="report-row report-danger" data-report="deudas proveedores compras pendientes pagar">
                        <i class="bi bi-exclamation-circle"></i>
                        <div>
                            <strong>Deudas a proveedores</strong>
                            <small>Compras pendientes agrupadas por proveedor.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

            <div class="report-panel" id="panel_caja">
                <div class="report-panel-head">
                    <h3>Caja</h3>
                    <span>Control diario de ingresos, egresos y cierre</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.caja-diaria') }}" class="report-row report-important" data-report="caja diaria ventas servicios egresos anulaciones cierre total final">
                        <i class="bi bi-safe"></i>
                        <div>
                            <strong>Caja diaria</strong>
                            <small>Ventas, servicios, egresos, anulaciones y total final.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

            <div class="report-panel" id="panel_servicios">
                <div class="report-panel-head">
                    <h3>Servicios de farmacia</h3>
                    <span>Atenciones, ingresos e insumos utilizados</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.servicios') }}" class="report-row" data-report="servicios farmacia atenciones ingresos insumos utilizados">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <div>
                            <strong>Servicios</strong>
                            <small>Servicios realizados, ingresos e insumos usados.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

            <div class="report-panel" id="panel_admin">
                <div class="report-panel-head">
                    <h3>Administración general</h3>
                    <span>Indicadores globales del sistema</span>
                </div>

                <div class="reports-list">
                    <a href="{{ route('reportes.resumen-administrativo') }}" class="report-row" data-report="resumen administrativo ventas servicios compras utilidad caja inventario">
                        <i class="bi bi-clipboard-data"></i>
                        <div>
                            <strong>Resumen administrativo</strong>
                            <small>Indicadores generales de todo el sistema.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.utilidad-estimada') }}" class="report-row" data-report="utilidad estimada ganancia aproximada precio compra precio venta">
                        <i class="bi bi-graph-up-arrow"></i>
                        <div>
                            <strong>Utilidad estimada</strong>
                            <small>Ganancia aproximada por ventas registradas.</small>
                        </div>
                        <span>Ver</span>
                    </a>

                    <a href="{{ route('reportes.ingresos-diarios') }}" class="report-row" data-report="ingresos diarios ventas servicios farmacia">
                        <i class="bi bi-calendar-check"></i>
                        <div>
                            <strong>Ingresos diarios</strong>
                            <small>Ingresos por ventas y servicios.</small>
                        </div>
                        <span>Ver</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <div class="empty-table-message" id="sin_reportes" style="display:none;">
        No se encontraron reportes con ese criterio.
    </div>

</div>

<script>
const inputBuscarReporte = document.getElementById('buscar_reporte');
const tabsReporte = document.querySelectorAll('.report-tab');
const panelesReporte = document.querySelectorAll('.report-panel');
const filasReporte = document.querySelectorAll('.report-row');
const sinReportes = document.getElementById('sin_reportes');

tabsReporte.forEach((tab) => {
    tab.addEventListener('click', function () {
        const panelId = this.dataset.panel;

        tabsReporte.forEach((item) => item.classList.remove('active'));
        panelesReporte.forEach((panel) => panel.classList.remove('active'));

        this.classList.add('active');

        const panelActivo = document.getElementById(panelId);

        if (panelActivo) {
            panelActivo.classList.add('active');
        }

        if (inputBuscarReporte) {
            inputBuscarReporte.value = '';
        }

        filasReporte.forEach((fila) => {
            fila.style.display = 'flex';
        });

        if (sinReportes) {
            sinReportes.style.display = 'none';
        }
    });
});

if (inputBuscarReporte) {
    inputBuscarReporte.addEventListener('input', function () {
        const texto = this.value.toLowerCase().trim();
        let visibles = 0;

        if (texto.length === 0) {
            panelesReporte.forEach((panel) => panel.classList.remove('active'));

            const panelUsados = document.getElementById('panel_usados');

            if (panelUsados) {
                panelUsados.classList.add('active');
            }

            tabsReporte.forEach((tab) => tab.classList.remove('active'));

            const tabUsados = document.querySelector('.report-tab[data-panel="panel_usados"]');

            if (tabUsados) {
                tabUsados.classList.add('active');
            }

            filasReporte.forEach((fila) => {
                fila.style.display = 'flex';
            });

            if (sinReportes) {
                sinReportes.style.display = 'none';
            }

            return;
        }

        panelesReporte.forEach((panel) => {
            panel.classList.add('active');
            panel.classList.remove('search-hidden');
        });

        tabsReporte.forEach((tab) => {
            tab.classList.remove('active');
        });

        filasReporte.forEach((fila) => {
            const contenido = fila.textContent.toLowerCase() + ' ' + (fila.dataset.report || '');
            const coincide = contenido.includes(texto);

            fila.style.display = coincide ? 'flex' : 'none';

            if (coincide) {
                visibles++;
            }
        });

        panelesReporte.forEach((panel) => {
            let tieneCoincidencias = false;

            panel.querySelectorAll('.report-row').forEach((fila) => {
                if (fila.style.display !== 'none') {
                    tieneCoincidencias = true;
                }
            });

            if (!tieneCoincidencias) {
                panel.classList.add('search-hidden');
            }
        });
    });
}
</script>

@endsection