<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $estado = $request->get('estado', 'activo');

        $proveedores = Proveedor::query()
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%")
                        ->orWhere('direccion', 'like', "%{$busqueda}%")
                        ->orWhere('contacto', 'like', "%{$busqueda}%");
                });
            })
            ->when($estado, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores', 'busqueda', 'estado'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:150'],
        ]);

        $datos['estado'] = 'activo';

        Proveedor::create($datos);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function show(Proveedor $proveedor)
    {
        $productosIds = DetalleCompra::whereHas('compra', function ($query) use ($proveedor) {
                $query->where('proveedor_id', $proveedor->id)
                    ->where('estado', 'registrada');
            })
            ->pluck('producto_id')
            ->unique()
            ->values();

        $productos = Producto::with(['categoria', 'laboratorio'])
            ->whereIn('id', $productosIds)
            ->latest()
            ->paginate(10);

        $cantidadProductos = Producto::whereIn('id', $productosIds)->count();

        $productosActivos = Producto::whereIn('id', $productosIds)
            ->where('estado', 'activo')
            ->count();

        $productosInactivos = Producto::whereIn('id', $productosIds)
            ->where('estado', 'inactivo')
            ->count();

        $comprasProveedor = $proveedor->compras()
            ->where('estado', 'registrada')
            ->latest('fecha_compra')
            ->limit(5)
            ->get();

        $totalComprado = $proveedor->compras()
            ->where('estado', 'registrada')
            ->sum('total');

        $totalPagado = $proveedor->compras()
            ->where('estado', 'registrada')
            ->sum('monto_pagado');

        $saldoPendiente = $proveedor->compras()
            ->where('estado', 'registrada')
            ->sum('saldo_pendiente');

        $cantidadCompras = $proveedor->compras()
            ->where('estado', 'registrada')
            ->count();

        return view('proveedores.show', compact(
            'proveedor',
            'productos',
            'cantidadProductos',
            'productosActivos',
            'productosInactivos',
            'comprasProveedor',
            'totalComprado',
            'totalPagado',
            'saldoPendiente',
            'cantidadCompras'
        ));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $proveedor->update($datos);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor desactivado correctamente.');
    }
}
