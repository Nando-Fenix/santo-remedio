<?php

namespace App\Http\Controllers;

use App\Models\CodigoBarra;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoPresentacionController extends Controller
{
    public function index(Producto $producto)
    {
        $producto->load([
            'categoria',
            'laboratorio',
            'proveedor',
            'presentaciones.presentacion',
            'presentaciones.codigosBarras',
        ]);

        $presentaciones = Presentacion::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('productos.presentaciones', compact('producto', 'presentaciones'));
    }

    public function store(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'presentacion_id' => ['required', 'exists:presentaciones,id'],
            'nombre_mostrado' => ['required', 'string', 'max:150'],
            'unidades_equivalentes' => ['required', 'integer', 'min:1'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'es_principal' => ['nullable', 'boolean'],
            'codigo_barras' => ['nullable', 'string', 'max:100', 'unique:codigos_barras,codigo'],
        ], [
            'presentacion_id.required' => 'Seleccione una presentación.',
            'presentacion_id.exists' => 'La presentación seleccionada no es válida.',
            'nombre_mostrado.required' => 'Ingrese el nombre mostrado de la presentación.',
            'unidades_equivalentes.required' => 'Ingrese las unidades equivalentes.',
            'unidades_equivalentes.min' => 'Las unidades equivalentes deben ser al menos 1.',
            'precio_compra.required' => 'Ingrese el precio de compra.',
            'precio_venta.required' => 'Ingrese el precio de venta.',
            'codigo_barras.unique' => 'Este código de barras ya está registrado.',
        ]);

        $existePresentacion = ProductoPresentacion::where('producto_id', $producto->id)
            ->where('presentacion_id', $datos['presentacion_id'])
            ->where('estado', 'activo')
            ->exists();

        if ($existePresentacion) {
            return back()
                ->withErrors([
                    'presentacion_id' => 'Este producto ya tiene registrada esa presentación.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($datos, $producto, $request) {
            $esPrincipal = $request->boolean('es_principal');

            if ($esPrincipal) {
                ProductoPresentacion::where('producto_id', $producto->id)
                    ->update(['es_principal' => false]);
            }

            $productoPresentacion = ProductoPresentacion::create([
                'producto_id' => $producto->id,
                'presentacion_id' => $datos['presentacion_id'],
                'nombre_mostrado' => $datos['nombre_mostrado'],
                'unidades_equivalentes' => $datos['unidades_equivalentes'],
                'precio_compra' => round((float) $datos['precio_compra'], 2),
                'precio_venta' => round((float) $datos['precio_venta'], 2),
                'es_principal' => $esPrincipal,
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
            ->route('productos.presentaciones.index', $producto)
            ->with('success', 'Presentación registrada correctamente.');
    }

    public function edit(Producto $producto, ProductoPresentacion $productoPresentacion)
    {
        if ($productoPresentacion->producto_id !== $producto->id) {
            abort(404);
        }

        $producto->load(['categoria', 'laboratorio', 'proveedor']);

        $productoPresentacion->load(['presentacion', 'codigosBarras']);

        $presentaciones = Presentacion::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('productos.presentaciones-edit', compact(
            'producto',
            'productoPresentacion',
            'presentaciones'
        ));
    }

    public function update(Request $request, Producto $producto, ProductoPresentacion $productoPresentacion)
    {
        if ($productoPresentacion->producto_id !== $producto->id) {
            abort(404);
        }

        $codigoBarraActual = $productoPresentacion->codigosBarras()
            ->where('estado', 'activo')
            ->first();

        $datos = $request->validate([
            'presentacion_id' => ['required', 'exists:presentaciones,id'],
            'nombre_mostrado' => ['required', 'string', 'max:150'],
            'unidades_equivalentes' => ['required', 'integer', 'min:1'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'es_principal' => ['nullable', 'boolean'],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:100',
                'unique:codigos_barras,codigo,' . ($codigoBarraActual?->id ?? 'NULL'),
            ],
        ], [
            'presentacion_id.required' => 'Seleccione una forma de venta.',
            'presentacion_id.exists' => 'La forma de venta seleccionada no es válida.',
            'nombre_mostrado.required' => 'Ingrese el nombre visible para venta.',
            'unidades_equivalentes.required' => 'Ingrese cuántas unidades descuenta.',
            'unidades_equivalentes.min' => 'Debe descontar al menos 1 unidad.',
            'precio_compra.required' => 'Ingrese el precio de compra.',
            'precio_venta.required' => 'Ingrese el precio de venta.',
            'codigo_barras.unique' => 'Este código de barras ya está registrado.',
        ]);

        DB::transaction(function () use ($datos, $producto, $productoPresentacion, $codigoBarraActual, $request) {
            $esPrincipal = $request->boolean('es_principal');

            if ($esPrincipal) {
                ProductoPresentacion::where('producto_id', $producto->id)
                    ->where('id', '!=', $productoPresentacion->id)
                    ->update(['es_principal' => false]);
            }

            $productoPresentacion->update([
                'presentacion_id' => $datos['presentacion_id'],
                'nombre_mostrado' => $datos['nombre_mostrado'],
                'unidades_equivalentes' => (int) $datos['unidades_equivalentes'],
                'precio_compra' => round((float) $datos['precio_compra'], 2),
                'precio_venta' => round((float) $datos['precio_venta'], 2),
                'es_principal' => $esPrincipal,
            ]);

            if (!empty($datos['codigo_barras'])) {
                if ($codigoBarraActual) {
                    $codigoBarraActual->update([
                        'codigo' => $datos['codigo_barras'],
                        'estado' => 'activo',
                    ]);
                } else {
                    CodigoBarra::create([
                        'producto_id' => $producto->id,
                        'producto_presentacion_id' => $productoPresentacion->id,
                        'codigo' => $datos['codigo_barras'],
                        'tipo_codigo' => 'fabricante',
                        'generado_por_sistema' => false,
                        'estado' => 'activo',
                    ]);
                }
            }
        });

        return redirect()
            ->route('productos.presentaciones.index', $producto)
            ->with('success', 'Forma de venta actualizada correctamente.');
    }

    public function destroy(Producto $producto, ProductoPresentacion $productoPresentacion)
    {
        if ($productoPresentacion->producto_id !== $producto->id) {
            abort(404);
        }

        $stockDisponible = $producto->inventarios()
            ->where('stock_actual', '>', 0)
            ->sum('stock_actual');

        if ($stockDisponible > 0) {
            return redirect()
                ->route('productos.presentaciones.index', $producto)
                ->with('error', 'No se puede desactivar la presentación porque el producto todavía tiene stock disponible.');
        }

        $productoPresentacion->update([
            'estado' => 'inactivo',
            'es_principal' => false,
        ]);

        CodigoBarra::where('producto_presentacion_id', $productoPresentacion->id)
            ->update(['estado' => 'inactivo']);

        return redirect()
            ->route('productos.presentaciones.index', $producto)
            ->with('success', 'Presentación desactivada correctamente.');
    }
}
