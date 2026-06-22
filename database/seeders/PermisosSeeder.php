<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\Rol;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            ['nombre' => 'ver_dashboard', 'modulo' => 'Dashboard', 'descripcion' => 'Ver pantalla principal del sistema.'],

            ['nombre' => 'realizar_venta', 'modulo' => 'Ventas', 'descripcion' => 'Registrar ventas.'],
            ['nombre' => 'anular_venta', 'modulo' => 'Ventas', 'descripcion' => 'Anular ventas.'],
            ['nombre' => 'reembolsar_venta', 'modulo' => 'Ventas', 'descripcion' => 'Registrar reembolsos.'],
            ['nombre' => 'aplicar_descuento', 'modulo' => 'Ventas', 'descripcion' => 'Aplicar descuentos.'],

            ['nombre' => 'ver_productos', 'modulo' => 'Productos', 'descripcion' => 'Consultar productos.'],
            ['nombre' => 'crear_producto', 'modulo' => 'Productos', 'descripcion' => 'Crear productos.'],
            ['nombre' => 'editar_producto', 'modulo' => 'Productos', 'descripcion' => 'Editar productos.'],
            ['nombre' => 'desactivar_producto', 'modulo' => 'Productos', 'descripcion' => 'Desactivar productos.'],

            ['nombre' => 'ver_inventario', 'modulo' => 'Inventario', 'descripcion' => 'Consultar inventario.'],
            ['nombre' => 'ajustar_inventario', 'modulo' => 'Inventario', 'descripcion' => 'Realizar ajustes de inventario.'],

            ['nombre' => 'ver_caja', 'modulo' => 'Caja', 'descripcion' => 'Ver caja.'],
            ['nombre' => 'abrir_caja', 'modulo' => 'Caja', 'descripcion' => 'Abrir caja.'],
            ['nombre' => 'cerrar_caja', 'modulo' => 'Caja', 'descripcion' => 'Cerrar caja.'],
            ['nombre' => 'registrar_egreso', 'modulo' => 'Caja', 'descripcion' => 'Registrar egresos de caja.'],

            ['nombre' => 'ver_reportes', 'modulo' => 'Reportes', 'descripcion' => 'Ver reportes.'],

            ['nombre' => 'administrar_usuarios', 'modulo' => 'Usuarios', 'descripcion' => 'Crear, editar y desactivar usuarios.'],
            ['nombre' => 'administrar_sucursales', 'modulo' => 'Sucursales', 'descripcion' => 'Administrar sucursales.'],
            ['nombre' => 'administrar_configuracion', 'modulo' => 'Configuración', 'descripcion' => 'Modificar configuración del sistema.'],

            ['nombre' => 'ver_clientes', 'modulo' => 'Clientes', 'descripcion' => 'Consultar clientes.'],
            ['nombre' => 'crear_cliente', 'modulo' => 'Clientes', 'descripcion' => 'Crear clientes.'],
            ['nombre' => 'editar_cliente', 'modulo' => 'Clientes', 'descripcion' => 'Editar clientes.'],
            ['nombre' => 'eliminar_cliente', 'modulo' => 'Clientes', 'descripcion' => 'Eliminar o desactivar clientes.'],

            ['nombre' => 'cambiar_producto', 'modulo' => 'Ventas', 'descripcion' => 'Registrar cambios de producto.'],
            ['nombre' => 'anular_cambio_producto', 'modulo' => 'Ventas', 'descripcion' => 'Anular cambios de producto.'],

            ['nombre' => 'ver_movimientos_inventario', 'modulo' => 'Inventario', 'descripcion' => 'Ver historial de movimientos de inventario.'],

            ['nombre' => 'ver_compras', 'modulo' => 'Compras', 'descripcion' => 'Consultar compras.'],
            ['nombre' => 'registrar_compra', 'modulo' => 'Compras', 'descripcion' => 'Registrar compras a proveedores.'],
            ['nombre' => 'pagar_compra', 'modulo' => 'Compras', 'descripcion' => 'Registrar pagos a proveedores.'],
            ['nombre' => 'anular_compra', 'modulo' => 'Compras', 'descripcion' => 'Anular compras.'],
            ['nombre' => 'ver_deudas_proveedores', 'modulo' => 'Compras', 'descripcion' => 'Ver deudas con proveedores.'],
            ['nombre' => 'creacion_rapida_compras', 'modulo' => 'Compras', 'descripcion' => 'Crear productos, categorías, laboratorios, presentaciones y proveedores desde compras.'],

            ['nombre' => 'ver_proveedores', 'modulo' => 'Proveedores', 'descripcion' => 'Consultar proveedores.'],
            ['nombre' => 'crear_proveedor', 'modulo' => 'Proveedores', 'descripcion' => 'Crear proveedores.'],
            ['nombre' => 'editar_proveedor', 'modulo' => 'Proveedores', 'descripcion' => 'Editar proveedores.'],
            ['nombre' => 'eliminar_proveedor', 'modulo' => 'Proveedores', 'descripcion' => 'Eliminar o desactivar proveedores.'],
            ['nombre' => 'ver_ventas', 'modulo' => 'Ventas', 'descripcion' => 'Consultar ventas.'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        $admin = Rol::where('nombre', 'Administrador')->first();
        $vendedor = Rol::where('nombre', 'Vendedor')->first();

        if ($admin) {
            $admin->permisos()->sync(Permiso::pluck('id')->toArray());
        }

        if ($vendedor) {
            $permisosVendedor = Permiso::whereIn('nombre', [
                'ver_dashboard',

                'ver_ventas',
                'realizar_venta',

                'ver_clientes',
                'crear_cliente',
                'editar_cliente',

                'ver_productos',

                'ver_inventario',
                'ver_movimientos_inventario',

                'ver_caja',
                'abrir_caja',
                'cerrar_caja',
            ])->pluck('id')->toArray();

            $vendedor->permisos()->sync($permisosVendedor);
        }
    }
}
