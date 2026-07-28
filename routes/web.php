<?php

use App\Http\Controllers\AtencionServicioController;
use App\Http\Controllers\BajaInventarioController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CambioProductoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\VentaProductoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProductoPresentacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\ReembolsoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ServicioFarmaciaController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permiso:ver_dashboard');


    /*
    |--------------------------------------------------------------------------
    | Ventas
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:realizar_venta')->group(function () {
        Route::get('/ventas/crear', [VentaController::class, 'create'])
            ->name('ventas.create');

        Route::post('/ventas', [VentaController::class, 'store'])
            ->name('ventas.store');

        Route::get('/ventas/buscar-promociones', [VentaProductoController::class, 'buscarPromociones'])
            ->name('ventas.buscar-promociones');

        Route::get('/ventas/buscar-productos', [VentaProductoController::class, 'buscar'])
            ->name('ventas.buscar-productos');
    });

    Route::middleware('permiso:ver_ventas')->group(function () {
        Route::get('/ventas', [VentaController::class, 'index'])
            ->name('ventas.index');

        Route::get('/ventas/{venta}/recibo', [VentaController::class, 'recibo'])
            ->name('ventas.recibo');
    });

    Route::middleware('permiso:anular_venta')->group(function () {
        Route::get('/ventas/{venta}/anular', [VentaController::class, 'anularCreate'])
            ->name('ventas.anular.create');

        Route::post('/ventas/{venta}/anular', [VentaController::class, 'anularStore'])
            ->name('ventas.anular.store');
    });

    Route::middleware('permiso:reembolsar_venta')->group(function () {
        Route::get('/ventas/{venta}/reembolso', [ReembolsoController::class, 'create'])
            ->name('reembolsos.create');

        Route::post('/ventas/{venta}/reembolso', [ReembolsoController::class, 'store'])
            ->name('reembolsos.store');

        Route::get('/reembolsos/{reembolso}', [ReembolsoController::class, 'show'])
            ->name('reembolsos.show');
    });

    Route::middleware('permiso:cambiar_producto')->group(function () {
        Route::get('/ventas/{venta}/cambio-producto', [CambioProductoController::class, 'create'])
            ->name('cambios-producto.create');

        Route::post('/ventas/{venta}/cambio-producto', [CambioProductoController::class, 'store'])
            ->name('cambios-producto.store');

        Route::get('/cambios-producto/buscar-productos', [CambioProductoController::class, 'buscarProductos'])
            ->name('cambios-producto.buscar-productos');

        Route::get('/cambios-producto/{cambioProducto}', [CambioProductoController::class, 'show'])
            ->name('cambios-producto.show');
    });

    Route::middleware('permiso:anular_cambio_producto')->group(function () {
        Route::get('/cambios-producto/{cambioProducto}/anular', [CambioProductoController::class, 'anularCreate'])
            ->name('cambios-producto.anular.create');

        Route::post('/cambios-producto/{cambioProducto}/anular', [CambioProductoController::class, 'anularStore'])
            ->name('cambios-producto.anular.store');
    });

    Route::middleware('permiso:ver_ventas')->group(function () {
        Route::get('/ventas/{venta}', [VentaController::class, 'show'])
            ->name('ventas.show');
    });


    /*
    |--------------------------------------------------------------------------
    | Caja
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:ver_caja')->group(function () {
        Route::get('/caja', [CajaController::class, 'index'])
            ->name('caja.index');

        Route::get('/caja/movimientos', [CajaController::class, 'movimientos'])
            ->name('caja.movimientos');
    });

    Route::middleware('permiso:abrir_caja')->group(function () {
        Route::get('/caja/abrir', [CajaController::class, 'create'])
            ->name('caja.create');

        Route::post('/caja/abrir', [CajaController::class, 'store'])
            ->name('caja.store');
    });

    Route::middleware('permiso:cerrar_caja')->group(function () {
        Route::get('/caja/cerrar', [CajaController::class, 'cierreCreate'])
            ->name('caja.cierre.create');

        Route::post('/caja/cerrar', [CajaController::class, 'cierreStore'])
            ->name('caja.cierre.store');

        Route::get('/caja/cerrar/exportar-csv', [CajaController::class, 'cierreExportarCsv'])
            ->name('caja.cierre.exportar-csv');

        Route::get('/caja/cerrar/exportar-excel', [CajaController::class, 'cierreExportarExcel'])
            ->name('caja.cierre.exportar-excel');
    });

    Route::middleware('permiso:registrar_egreso')->group(function () {
        Route::get('/caja/egreso', [CajaController::class, 'egresoCreate'])
            ->name('caja.egreso.create');

        Route::post('/caja/egreso', [CajaController::class, 'egresoStore'])
            ->name('caja.egreso.store');
    });


    /*
    |--------------------------------------------------------------------------
    | Clientes
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:crear_cliente')->group(function () {
        Route::get('/clientes/crear', [ClienteController::class, 'create'])
            ->name('clientes.create');

        Route::post('/clientes', [ClienteController::class, 'store'])
            ->name('clientes.store');
    });

    Route::middleware('permiso:editar_cliente')->group(function () {
        Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])
            ->name('clientes.edit');

        Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])
            ->name('clientes.update');
    });

    Route::middleware('permiso:eliminar_cliente')->group(function () {
        Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])
            ->name('clientes.destroy');
    });

    Route::middleware('permiso:ver_clientes')->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index'])
            ->name('clientes.index');

        Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])
            ->name('clientes.show');
    });


    /*
    |--------------------------------------------------------------------------
    | Inventario
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:ver_inventario')->group(function () {
        Route::get('/inventario', [InventarioController::class, 'index'])
            ->name('inventario.index');

        Route::get('/inventario/proximos-vencer', [InventarioController::class, 'proximosVencer'])
            ->name('inventario.proximos-vencer');

        Route::get('/inventario/productos-vencidos', [InventarioController::class, 'productosVencidos'])
            ->name('inventario.productos-vencidos');
    });

    Route::middleware('permiso:ver_movimientos_inventario')->group(function () {
        Route::get('/inventario/movimientos', [InventarioController::class, 'movimientos'])
            ->name('inventario.movimientos');
    });

    Route::middleware('permiso:ajustar_inventario')->group(function () {
        Route::get('/inventario/entrada', [InventarioController::class, 'create'])
            ->name('inventario.create');

        Route::post('/inventario/entrada', [InventarioController::class, 'store'])
            ->name('inventario.store');
    });


    /*
    |--------------------------------------------------------------------------
    | Productos y presentaciones
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:crear_producto')->group(function () {
        Route::get('/productos/crear', [ProductoController::class, 'create'])
            ->name('productos.create');

        Route::post('/productos', [ProductoController::class, 'store'])
            ->name('productos.store');

        
    });

    Route::middleware('permiso:editar_producto')->group(function () {
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])
            ->name('productos.edit');

        Route::put('/productos/{producto}', [ProductoController::class, 'update'])
            ->name('productos.update');

        Route::post('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'store'])
            ->name('productos.presentaciones.store');

        Route::get('/productos/{producto}/presentaciones/{productoPresentacion}/editar', [ProductoPresentacionController::class, 'edit'])
            ->name('productos.presentaciones.edit');

        Route::put('/productos/{producto}/presentaciones/{productoPresentacion}', [ProductoPresentacionController::class, 'update'])
            ->name('productos.presentaciones.update');
    });

    Route::middleware('permiso:desactivar_producto')->group(function () {
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->name('productos.destroy');

        Route::delete('/productos/{producto}/presentaciones/{productoPresentacion}', [ProductoPresentacionController::class, 'destroy'])
            ->name('productos.presentaciones.destroy');
    });

    Route::middleware('permiso:ver_productos')->group(function () {
        Route::get('/productos', [ProductoController::class, 'index'])
            ->name('productos.index');

        Route::get('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'index'])
            ->name('productos.presentaciones.index');
    });


    /*
    |--------------------------------------------------------------------------
    | Proveedores
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:crear_proveedor')->group(function () {
        Route::get('/proveedores/crear', [ProveedorController::class, 'create'])
            ->name('proveedores.create');

        Route::post('/proveedores', [ProveedorController::class, 'store'])
            ->name('proveedores.store');
    });

    Route::middleware('permiso:editar_proveedor')->group(function () {
        Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])
            ->name('proveedores.edit');

        Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])
            ->name('proveedores.update');
    });

    Route::middleware('permiso:eliminar_proveedor')->group(function () {
        Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])
            ->name('proveedores.destroy');
    });

    Route::middleware('permiso:ver_proveedores')->group(function () {
        Route::get('/proveedores', [ProveedorController::class, 'index'])
            ->name('proveedores.index');

        Route::get('/proveedores/{proveedor}', [ProveedorController::class, 'show'])
            ->name('proveedores.show');
    });


    /*
    |--------------------------------------------------------------------------
    | Compras
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:registrar_compra')->group(function () {
        Route::get('/compras/crear', [CompraController::class, 'create'])
            ->name('compras.create');

        Route::post('/compras', [CompraController::class, 'store'])
            ->name('compras.store');

        Route::get('/compras/buscar-productos', [CompraController::class, 'buscarProductos'])
            ->name('compras.buscar-productos');
    });

    Route::middleware('permiso:creacion_rapida_compras')->group(function () {
        Route::post('/compras/producto-rapido', [CompraController::class, 'productoRapido'])
            ->name('compras.producto-rapido');

        Route::post('/compras/proveedor-rapido', [CompraController::class, 'proveedorRapido'])
            ->name('compras.proveedor-rapido');

        Route::post('/compras/categoria-rapida', [CompraController::class, 'categoriaRapida'])
            ->name('compras.categoria-rapida');

        Route::post('/compras/presentacion-rapida', [CompraController::class, 'presentacionRapida'])
            ->name('compras.presentacion-rapida');

        Route::post('/compras/laboratorio-rapido', [CompraController::class, 'laboratorioRapido'])
            ->name('compras.laboratorio-rapido');
    });

    Route::middleware('permiso:ver_deudas_proveedores')->group(function () {
        Route::get('/compras/deudas/proveedores', [CompraController::class, 'deudas'])
            ->name('compras.deudas');
    });

    Route::middleware('permiso:pagar_compra')->group(function () {
        Route::get('/compras/{compra}/pagar', [CompraController::class, 'pagoCreate'])
            ->name('compras.pago.create');

        Route::post('/compras/{compra}/pagar', [CompraController::class, 'pagoStore'])
            ->name('compras.pago.store');
    });

    Route::middleware('permiso:anular_compra')->group(function () {
        Route::get('/compras/{compra}/anular', [CompraController::class, 'anularCreate'])
            ->name('compras.anular.create');

        Route::post('/compras/{compra}/anular', [CompraController::class, 'anularStore'])
            ->name('compras.anular.store');
    });

    Route::middleware('permiso:ver_compras')->group(function () {
        Route::get('/compras', [CompraController::class, 'index'])
            ->name('compras.index');

        Route::get('/compras/{compra}', [CompraController::class, 'show'])
            ->name('compras.show');
    });


    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:administrar_usuarios')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])
            ->name('usuarios.index');

        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])
            ->name('usuarios.create');

        Route::post('/usuarios', [UsuarioController::class, 'store'])
            ->name('usuarios.store');

        Route::get('/usuarios/{user}/editar', [UsuarioController::class, 'edit'])
            ->name('usuarios.edit');

        Route::put('/usuarios/{user}', [UsuarioController::class, 'update'])
            ->name('usuarios.update');

        Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy'])
            ->name('usuarios.destroy');

        Route::post('/usuarios/roles-rapido', [UsuarioController::class, 'rolRapido'])
            ->name('usuarios.roles-rapido');
    });

    /*
    |--------------------------------------------------------------------------
    | Reportes - solo Administrador
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:ver_reportes')->group(function () {
        Route::get('/reportes', [ReporteController::class, 'index'])
            ->name('reportes.index');

        Route::get('/reportes/promociones/exportar-csv', [ReporteController::class, 'promocionesExportarCsv'])
            ->name('reportes.promociones.exportar-csv');

        Route::get('/reportes/promociones', [ReporteController::class, 'promociones'])
            ->name('reportes.promociones');

        Route::get('/reportes/servicios/exportar-csv', [ReporteController::class, 'serviciosExportarCsv'])
            ->name('reportes.servicios.exportar-csv');

        Route::get('/reportes/servicios/exportar-insumos-csv', [ReporteController::class, 'serviciosInsumosExportarCsv'])
            ->name('reportes.servicios.exportar-insumos-csv');

        Route::get('/reportes/servicios', [ReporteController::class, 'servicios'])
            ->name('reportes.servicios');
        
        Route::get('/reportes/caja-diaria', [ReporteController::class, 'cajaDiaria'])
            ->name('reportes.caja-diaria');
        
        Route::get('/reportes/caja-diaria/exportar-csv', [ReporteController::class, 'cajaDiariaExportarCsv'])
            ->name('reportes.caja-diaria.exportar-csv');

        Route::get('/reportes/ingresos-diarios/exportar-csv', [ReporteController::class, 'ingresosDiariosExportarCsv'])
            ->name('reportes.ingresos-diarios.exportar-csv');

        Route::get('/reportes/ingresos-diarios', [ReporteController::class, 'ingresosDiarios'])
            ->name('reportes.ingresos-diarios');

        Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])
            ->name('reportes.ventas');

        Route::get('/reportes/ventas/exportar-csv', [ReporteController::class, 'ventasExportarCsv'])
            ->name('reportes.ventas.exportar-csv');

        Route::get('/reportes/compras', [ReporteController::class, 'compras'])
            ->name('reportes.compras');

        Route::get('/reportes/compras/exportar-csv', [ReporteController::class, 'comprasExportarCsv'])
            ->name('reportes.compras.exportar-csv');

        Route::get('/reportes/deudas-proveedores', [ReporteController::class, 'deudasProveedores'])
            ->name('reportes.deudas-proveedores');

        Route::get('/reportes/deudas-proveedores/exportar-csv', [ReporteController::class, 'deudasProveedoresExportarCsv'])
            ->name('reportes.deudas-proveedores.exportar-csv');

        Route::get('/reportes/inventario-critico', [ReporteController::class, 'inventarioCritico'])
            ->name('reportes.inventario-critico');

        Route::get('/reportes/inventario-critico/exportar-csv', [ReporteController::class, 'inventarioCriticoExportarCsv'])
            ->name('reportes.inventario-critico.exportar-csv');

        Route::get('/reportes/movimientos-inventario', [ReporteController::class, 'movimientosInventario'])
            ->name('reportes.movimientos-inventario');

        Route::get('/reportes/movimientos-inventario/exportar-csv', [ReporteController::class, 'movimientosInventarioExportarCsv'])
            ->name('reportes.movimientos-inventario.exportar-csv');

        Route::get('/reportes/productos-vendidos', [ReporteController::class, 'productosVendidos'])
            ->name('reportes.productos-vendidos');

        Route::get('/reportes/productos-vendidos/exportar-csv', [ReporteController::class, 'productosVendidosExportarCsv'])
            ->name('reportes.productos-vendidos.exportar-csv');

        Route::get('/reportes/clientes-frecuentes', [ReporteController::class, 'clientesFrecuentes'])
            ->name('reportes.clientes-frecuentes');

        Route::get('/reportes/clientes-frecuentes/exportar-csv', [ReporteController::class, 'clientesFrecuentesExportarCsv'])
            ->name('reportes.clientes-frecuentes.exportar-csv');

        Route::get('/reportes/productos-reponer', [ReporteController::class, 'productosReponer'])
            ->name('reportes.productos-reponer');

        Route::get('/reportes/productos-reponer/exportar-csv', [ReporteController::class, 'productosReponerExportarCsv'])
            ->name('reportes.productos-reponer.exportar-csv');

        Route::get('/reportes/utilidad-estimada', [ReporteController::class, 'utilidadEstimada'])
            ->name('reportes.utilidad-estimada');

        Route::get('/reportes/utilidad-estimada/exportar-csv', [ReporteController::class, 'utilidadEstimadaExportarCsv'])
            ->name('reportes.utilidad-estimada.exportar-csv');

        Route::get('/reportes/metodos-pago', [ReporteController::class, 'metodosPago'])
            ->name('reportes.metodos-pago');

        Route::get('/reportes/metodos-pago/exportar-csv', [ReporteController::class, 'metodosPagoExportarCsv'])
            ->name('reportes.metodos-pago.exportar-csv');

        Route::get('/reportes/resumen-administrativo', [ReporteController::class, 'resumenAdministrativo'])
            ->name('reportes.resumen-administrativo');

        Route::get('/reportes/resumen-administrativo/exportar-csv', [ReporteController::class, 'resumenAdministrativoExportarCsv'])
            ->name('reportes.resumen-administrativo.exportar-csv');

        Route::get('/reportes/bajas-inventario', [ReporteController::class, 'bajasInventario'])
            ->name('reportes.bajas-inventario');

        Route::get('/reportes/bajas-inventario/exportar-csv', [ReporteController::class, 'bajasInventarioExportarCsv'])
            ->name('reportes.bajas-inventario.exportar-csv');
    });

    /*
    |--------------------------------------------------------------------------
    | Promociones
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:ver_promociones')->group(function () {
        Route::get('/promociones', [PromocionController::class, 'index'])
            ->name('promociones.index');

        Route::get('/promociones/{promocion}', [PromocionController::class, 'show'])
            ->name('promociones.show');
    });

    Route::middleware('permiso:crear_promocion')->group(function () {
        Route::get('/promociones/crear/nueva', [PromocionController::class, 'create'])
            ->name('promociones.create');

        Route::post('/promociones', [PromocionController::class, 'store'])
            ->name('promociones.store');

        Route::get('/promociones/buscar/productos', [PromocionController::class, 'buscarProductos'])
            ->name('promociones.buscar-productos');
    });

    Route::middleware('permiso:editar_promocion')->group(function () {
        Route::get('/promociones/{promocion}/editar', [PromocionController::class, 'edit'])
            ->name('promociones.edit');

        Route::put('/promociones/{promocion}', [PromocionController::class, 'update'])
            ->name('promociones.update');
    });

    Route::delete('/promociones/{promocion}', [PromocionController::class, 'destroy'])
        ->middleware('permiso:desactivar_promocion')
        ->name('promociones.destroy');
        
    /*
    |--------------------------------------------------------------------------
    | Bajas inventario
    |--------------------------------------------------------------------------
    */

        Route::middleware('permiso:ver_bajas_inventario')->group(function () {
            Route::get('/inventario/bajas', [BajaInventarioController::class, 'index'])
                ->name('bajas-inventario.index');

            Route::get('/inventario/bajas/{bajaInventario}', [BajaInventarioController::class, 'show'])
                ->name('bajas-inventario.show');
            
            Route::get('/inventario/bajas/{bajaInventario}/recibo', [BajaInventarioController::class, 'recibo'])
                ->name('bajas-inventario.recibo');
        });

        Route::middleware('permiso:registrar_baja_inventario')->group(function () {
            Route::get('/inventario/bajas/registrar/nueva', [BajaInventarioController::class, 'create'])
                ->name('bajas-inventario.create');

            Route::post('/inventario/bajas', [BajaInventarioController::class, 'store'])
                ->name('bajas-inventario.store');

            Route::get('/inventario/bajas/buscar/productos', [BajaInventarioController::class, 'buscarProductos'])
                ->name('bajas-inventario.buscar-productos');
        });

        Route::middleware('permiso:anular_baja_inventario')->group(function () {
            Route::get('/inventario/bajas/{bajaInventario}/anular', [BajaInventarioController::class, 'anularCreate'])
                ->name('bajas-inventario.anular.create');

            Route::post('/inventario/bajas/{bajaInventario}/anular', [BajaInventarioController::class, 'anularStore'])
                ->name('bajas-inventario.anular.store');
        });


        /*
        |--------------------------------------------------------------------------
        | servicios farmacia
        |--------------------------------------------------------------------------
        */

        Route::middleware('permiso:ver_servicios_farmacia')->group(function () {
            Route::get('/servicios-farmacia', [ServicioFarmaciaController::class, 'index'])
                ->name('servicios-farmacia.index');

            Route::get('/servicios-farmacia/{servicioFarmacia}', [ServicioFarmaciaController::class, 'show'])
                ->name('servicios-farmacia.show');
        });

        Route::middleware('permiso:crear_servicio_farmacia')->group(function () {
            Route::get('/servicios-farmacia/crear/nuevo', [ServicioFarmaciaController::class, 'create'])
                ->name('servicios-farmacia.create');

            Route::post('/servicios-farmacia', [ServicioFarmaciaController::class, 'store'])
                ->name('servicios-farmacia.store');
        });

        Route::middleware('permiso:editar_servicio_farmacia')->group(function () {
            Route::get('/servicios-farmacia/{servicioFarmacia}/editar', [ServicioFarmaciaController::class, 'edit'])
                ->name('servicios-farmacia.edit');

            Route::put('/servicios-farmacia/{servicioFarmacia}', [ServicioFarmaciaController::class, 'update'])
                ->name('servicios-farmacia.update');

            Route::get('/servicios-farmacia/buscar/productos', [ServicioFarmaciaController::class, 'buscarProductos'])
                ->name('servicios-farmacia.buscar-productos');
        });

        Route::delete('/servicios-farmacia/{servicioFarmacia}', [ServicioFarmaciaController::class, 'destroy'])
            ->middleware('permiso:desactivar_servicio_farmacia')
            ->name('servicios-farmacia.destroy');


        /*
        |--------------------------------------------------------------------------
        | Atenciones de servicios farmacia
        |--------------------------------------------------------------------------
        */

        Route::middleware('permiso:ver_atenciones_servicio')->group(function () {
            Route::get('/atenciones-servicio', [AtencionServicioController::class, 'index'])
                ->name('atenciones-servicio.index');

            Route::get('/atenciones-servicio/{atencionServicio}', [AtencionServicioController::class, 'show'])
                ->name('atenciones-servicio.show');

            Route::get('/atenciones-servicio/{atencionServicio}/recibo', [AtencionServicioController::class, 'recibo'])
                ->name('atenciones-servicio.recibo');
        });

        Route::middleware('permiso:registrar_atencion_servicio')->group(function () {
            Route::get('/atenciones-servicio/registrar/nueva', [AtencionServicioController::class, 'create'])
                ->name('atenciones-servicio.create');

            Route::post('/atenciones-servicio', [AtencionServicioController::class, 'store'])
                ->name('atenciones-servicio.store');
        });

        Route::middleware('permiso:anular_atencion_servicio')->group(function () {
            Route::get('/atenciones-servicio/{atencionServicio}/anular', [AtencionServicioController::class, 'anularCreate'])
                ->name('atenciones-servicio.anular.create');

            Route::post('/atenciones-servicio/{atencionServicio}/anular', [AtencionServicioController::class, 'anularStore'])
                ->name('atenciones-servicio.anular.store');
        });

        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        Route::middleware('permiso:administrar_configuracion')->group(function () {
            Route::get('/configuracion', [ConfiguracionController::class, 'edit'])
                ->name('configuracion.edit');

            Route::put('/configuracion', [ConfiguracionController::class, 'update'])
                ->name('configuracion.update');
        });

        
});