<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\CodigoBarra;
use App\Models\Presentacion;
use App\Models\ProductoPresentacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');
        $tipoProducto = $request->get('tipo_producto');

        $productos = Producto::with([
            'categoria',
            'laboratorio',
            'proveedor',
            'presentacionPrincipal.presentacion',
            'inventarios',
        ])
        ->withCount(['presentaciones' => function ($query) {
            $query->where('estado', 'activo');
        }])
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%")
                        ->orWhereHas('categoria', function ($q) use ($buscar) {
                            $q->where('nombre', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('laboratorio', function ($q) use ($buscar) {
                            $q->where('nombre', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('proveedor', function ($q) use ($buscar) {
                            $q->where('nombre', 'like', "%{$buscar}%");
                        });
                });
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($tipoProducto, function ($query, $tipoProducto) {
                $query->where('tipo_producto', $tipoProducto);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('productos.index', compact('productos', 'buscar', 'estado', 'tipoProducto'));
    }

    public function create()
    {
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $laboratorios = Laboratorio::where('estado', 'activo')->orderBy('nombre')->get();
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();

        $presentaciones = Presentacion::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('productos.create', compact(
            'categorias',
            'laboratorios',
            'proveedores',
            'presentaciones'
        ));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | Datos generales del producto
            |--------------------------------------------------------------------------
            */
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'nombre_generico' => ['nullable', 'string', 'max:150'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tipo_producto' => ['required', 'in:medicamento,insumo_medico,producto_general,higiene,bebe,otro'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'laboratorio_id' => ['nullable', 'exists:laboratorios,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],

            /*
            |--------------------------------------------------------------------------
            | Presentación principal
            |--------------------------------------------------------------------------
            */
            'presentacion_id' => ['required', 'exists:presentaciones,id'],
            'nombre_mostrado' => ['required', 'string', 'max:150'],
            'unidades_equivalentes' => ['required', 'integer', 'min:1'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'codigo_barras' => ['nullable', 'string', 'max:100', 'unique:codigos_barras,codigo'],
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',

            'tipo_producto.required' => 'Seleccione el tipo de producto.',
            'tipo_producto.in' => 'El tipo de producto seleccionado no es válido.',

            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no es válido.',
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',

            'presentacion_id.required' => 'Seleccione la presentación principal.',
            'presentacion_id.exists' => 'La presentación seleccionada no es válida.',

            'nombre_mostrado.required' => 'Ingrese el nombre mostrado para venta.',
            'unidades_equivalentes.required' => 'Ingrese las unidades equivalentes.',
            'unidades_equivalentes.min' => 'Las unidades equivalentes deben ser al menos 1.',

            'precio_compra.required' => 'Ingrese el precio de compra.',
            'precio_venta.required' => 'Ingrese el precio de venta.',

            'codigo_barras.unique' => 'Este código de barras ya está registrado.',
        ]);

        DB::transaction(function () use ($datos) {
            $producto = Producto::create([
                'nombre_comercial' => $datos['nombre_comercial'],
                'nombre_generico' => $datos['nombre_generico'] ?? null,
                'concentracion' => $datos['concentracion'] ?? null,
                'descripcion' => $datos['descripcion'] ?? null,
                'tipo_producto' => $datos['tipo_producto'],
                'categoria_id' => $datos['categoria_id'] ?? null,
                'laboratorio_id' => $datos['laboratorio_id'] ?? null,
                'proveedor_id' => $datos['proveedor_id'] ?? null,
                'estado' => 'activo',
            ]);

            $productoPresentacion = ProductoPresentacion::create([
                'producto_id' => $producto->id,
                'presentacion_id' => $datos['presentacion_id'],
                'nombre_mostrado' => $datos['nombre_mostrado'],
                'unidades_equivalentes' => (int) $datos['unidades_equivalentes'],
                'precio_compra' => round((float) $datos['precio_compra'], 2),
                'precio_venta' => round((float) $datos['precio_venta'], 2),
                'es_principal' => true,
                'estado' => 'activo',
            ]);

            if (!empty($datos['codigo_barras'])) {
                CodigoBarra::create([
                    'producto_id' => $producto->id,
                    'producto_presentacion_id' => $productoPresentacion->id,
                    'codigo' => $datos['codigo_barras'],
                    'tipo_codigo' => 'fabricante',
                    'generado_por_sistema' => false,
                    'estado' => 'activo',
                ]);
            }
        });

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto registrado correctamente y listo para vender.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $laboratorios = Laboratorio::where('estado', 'activo')->orderBy('nombre')->get();
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'categorias', 'laboratorios', 'proveedores'));
    }

    public function update(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'nombre_generico' => ['nullable', 'string', 'max:150'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tipo_producto' => ['required', 'in:medicamento,insumo_medico,producto_general,higiene,bebe,otro'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'laboratorio_id' => ['nullable', 'exists:laboratorios,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
        ], [
            'tipo_producto.required' => 'Seleccione el tipo de producto.',
            'tipo_producto.in' => 'El tipo de producto seleccionado no es válido.',
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no es válido.',
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',
        ]);

        $datos['estado'] = $producto->estado;

        $producto->update($datos);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $stockDisponible = $producto->inventarios()
            ->where('stock_actual', '>', 0)
            ->sum('stock_actual');

        if ($stockDisponible > 0) {
            return redirect()
                ->route('productos.index')
                ->with('error', 'No se puede desactivar el producto porque todavía tiene stock disponible.');
        }

        $producto->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto desactivado correctamente.');
    }
}
