<?php

use App\Http\Controllers\CajaController;
use App\Http\Controllers\CambioProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\VentaProductoController;
use App\Http\Controllers\ProductoPresentacionController;
use App\Http\Controllers\ProveedorController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/crear', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');

    Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');

    Route::get('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'index'])
    ->name('productos.presentaciones.index');

    Route::post('/productos/{producto}/presentaciones', [ProductoPresentacionController::class, 'store'])
        ->name('productos.presentaciones.store');

    Route::delete('/productos/{producto}/presentaciones/{productoPresentacion}', [ProductoPresentacionController::class, 'destroy'])
        ->name('productos.presentaciones.destroy');

    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/entrada', [InventarioController::class, 'create'])->name('inventario.create');
    Route::post('/inventario/entrada', [InventarioController::class, 'store'])->name('inventario.store');

    Route::get('/inventario/movimientos', [InventarioController::class, 'movimientos'])
    ->name('inventario.movimientos');

    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/crear', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    
    Route::get('/ventas/{venta}/anular', [VentaController::class, 'anularCreate'])
        ->name('ventas.anular.create');

    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anularStore'])
        ->name('ventas.anular.store');

    Route::get('/ventas/buscar-productos', [VentaProductoController::class, 'buscar'])
    ->name('ventas.buscar-productos');

    Route::get('/ventas/{venta}/reembolso', [ReembolsoController::class, 'create'])
        ->name('reembolsos.create');

    Route::post('/ventas/{venta}/reembolso', [ReembolsoController::class, 'store'])
        ->name('reembolsos.store');

    Route::get('/reembolsos/{reembolso}', [ReembolsoController::class, 'show'])
        ->name('reembolsos.show');

    Route::get('/ventas/{venta}/cambio-producto', [CambioProductoController::class, 'create'])
        ->name('cambios-producto.create');
    Route::post('/ventas/{venta}/cambio-producto', [CambioProductoController::class, 'store'])
        ->name('cambios-producto.store');
    Route::get('/cambios-producto/buscar-productos', [CambioProductoController::class, 'buscarProductos'])
        ->name('cambios-producto.buscar-productos');

    Route::get('/cambios-producto/{cambioProducto}/anular', [CambioProductoController::class, 'anularCreate'])
        ->name('cambios-producto.anular.create');

    Route::post('/cambios-producto/{cambioProducto}/anular', [CambioProductoController::class, 'anularStore'])
        ->name('cambios-producto.anular.store');
        
    Route::get('/cambios-producto/{cambioProducto}', [CambioProductoController::class, 'show'])
        ->name('cambios-producto.show');

    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');

    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::get('/caja/abrir', [CajaController::class, 'create'])->name('caja.create');
    Route::post('/caja/abrir', [CajaController::class, 'store'])->name('caja.store');

    Route::get('/caja/egreso', [CajaController::class, 'egresoCreate'])->name('caja.egreso.create');
    Route::post('/caja/egreso', [CajaController::class, 'egresoStore'])->name('caja.egreso.store');

    Route::get('/caja/movimientos', [CajaController::class, 'movimientos'])
    ->name('caja.movimientos');

    Route::get('/caja/cerrar', [CajaController::class, 'cierreCreate'])->name('caja.cierre.create');
    Route::post('/caja/cerrar', [CajaController::class, 'cierreStore'])->name('caja.cierre.store');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // ruta para las vistas de clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    // Rutas para proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::get('/proveedores/crear', [ProveedorController::class, 'create'])->name('proveedores.create');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::get('/proveedores/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
    Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])->name('proveedores.edit');
    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

    Route::post('/compras/presentacion-rapida', [CompraController::class, 'presentacionRapida'])
        ->name('compras.presentacion-rapida');

    Route::post('/compras/laboratorio-rapido', [CompraController::class, 'laboratorioRapido'])
        ->name('compras.laboratorio-rapido');
    // Compras
    
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/crear', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    
    Route::get('/compras/buscar-productos', [CompraController::class, 'buscarProductos'])
    ->name('compras.buscar-productos');
    Route::post('/compras/producto-rapido', [CompraController::class, 'productoRapido'])
        ->name('compras.producto-rapido');

    Route::get('/compras/{compra}/pagar', [CompraController::class, 'pagoCreate'])
        ->name('compras.pago.create');

    Route::post('/compras/{compra}/pagar', [CompraController::class, 'pagoStore'])
        ->name('compras.pago.store');

    Route::get('/compras/deudas/proveedores', [CompraController::class, 'deudas'])
        ->name('compras.deudas');

    Route::get('/compras/{compra}/anular', [CompraController::class, 'anularCreate'])
        ->name('compras.anular.create');

    Route::post('/compras/{compra}/anular', [CompraController::class, 'anularStore'])
        ->name('compras.anular.store');

    Route::get('/compras/{compra}', [CompraController::class, 'show'])->name('compras.show');

});