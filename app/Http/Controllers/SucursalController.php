<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $sucursales = Sucursal::query()
            ->withCount('usuarios')
            ->when($buscar, function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('direccion', 'like', "%{$buscar}%");
            })
            ->orderByRaw("CASE WHEN estado = 'activo' THEN 0 ELSE 1 END")
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('sucursales.index', compact('sucursales', 'buscar'));
    }

    public function create()
    {
        return view('sucursales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:sucursales,nombre'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:activo,inactivo'],
        ], [
            'nombre.required' => 'El nombre de la sucursal es obligatorio.',
            'nombre.unique' => 'Ya existe una sucursal con ese nombre.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ]);

        Sucursal::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'estado' => $request->estado,
        ]);

        return redirect()
            ->route('sucursales.index')
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        return view('sucursales.edit', compact('sucursal'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:sucursales,nombre,' . $sucursal->id],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:activo,inactivo'],
        ], [
            'nombre.required' => 'El nombre de la sucursal es obligatorio.',
            'nombre.unique' => 'Ya existe una sucursal con ese nombre.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ]);

        $sucursal->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'estado' => $request->estado,
        ]);

        return redirect()
            ->route('sucursales.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        $nuevoEstado = $sucursal->estado === 'activo' ? 'inactivo' : 'activo';

        $sucursal->update([
            'estado' => $nuevoEstado,
        ]);

        return redirect()
            ->route('sucursales.index')
            ->with('success', 'Estado de la sucursal actualizado correctamente.');
    }
}