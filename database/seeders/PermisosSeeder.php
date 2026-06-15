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
                'realizar_venta',
                'aplicar_descuento',
                'ver_productos',
                'ver_inventario',
                'ver_caja',
                'abrir_caja',
                'cerrar_caja',
                'registrar_egreso',
            ])->pluck('id')->toArray();

            $vendedor->permisos()->sync($permisosVendedor);
        }
    }
}
