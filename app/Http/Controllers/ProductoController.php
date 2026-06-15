<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');

        $productos = Producto::with(['categoria', 'laboratorio', 'proveedor'])
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
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('productos.index', compact('productos', 'buscar', 'estado'));
    }

    public function create()
    {
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre')->get();
        $laboratorios = Laboratorio::where('estado', 'activo')->orderBy('nombre')->get();
        $proveedores = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();

        return view('productos.create', compact('categorias', 'laboratorios', 'proveedores'));
    }

    public function store(Request $request)
    {   
        $datos = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'nombre_generico' => ['nullable', 'string', 'max:150'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'laboratorio_id' => ['nullable', 'exists:laboratorios,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no es válido.',
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',
        ]);

        $datos['estado'] = 'activo';

        Producto::create($datos);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
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
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'laboratorio_id' => ['nullable', 'exists:laboratorios,id'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
            'estado' => ['required', 'in:activo,inactivo'],
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no es válido.',
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
        ]);

        $producto->update($datos);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto desactivado correctamente.');
    }
}
