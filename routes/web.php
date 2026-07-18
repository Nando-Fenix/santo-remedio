<?php

use App\Http\Controllers\BajaInventarioController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CambioProductoController;
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
    });

    /*
    |--------------------------------------------------------------------------
    | Reportes - solo Administrador
    |--------------------------------------------------------------------------
    */

    Route::middleware('permiso:ver_reportes')->group(function () {
        Route::get('/reportes', [ReporteController::class, 'index'])
            ->name('reportes.index');

        Route::get('/reportes/promociones', [ReporteController::class, 'promociones'])
            ->name('reportes.promociones');
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
});