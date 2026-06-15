<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $estado = $request->get('estado', 'activo');

        $clientes = Cliente::query()
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('ci_nit', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%")
                        ->orWhere('direccion', 'like', "%{$busqueda}%");
                });
            })
            ->when($estado, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'busqueda', 'estado'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ci_nit' => ['nullable', 'string', 'max:30', 'unique:clientes,ci_nit'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tipo_cliente' => ['required', 'string', 'max:30'],
            'descuento_default' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $datos['descuento_default'] = $datos['descuento_default'] ?? 0;
        $datos['estado'] = 'activo';

        Cliente::create($datos);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'ventas.usuario',
            'ventas.sucursal',
            'ventas.pagos.metodoPago',
            'ventas.detalles.producto',
            'ventas.detalles.productoPresentacion.presentacion',
        ]);

        $ventas = $cliente->ventas()
            ->with(['usuario', 'sucursal', 'pagos.metodoPago'])
            ->latest('fecha_hora')
            ->paginate(10);

        $totalComprado = $cliente->ventas()
            ->where('estado', 'completada')
            ->sum('total');

        $cantidadCompras = $cliente->ventas()
            ->where('estado', 'completada')
            ->count();

        $ultimaCompra = $cliente->ventas()
            ->where('estado', 'completada')
            ->latest('fecha_hora')
            ->first();

        return view('clientes.show', compact(
            'cliente',
            'ventas',
            'totalComprado',
            'cantidadCompras',
            'ultimaCompra'
        ));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ci_nit' => ['nullable', 'string', 'max:30', 'unique:clientes,ci_nit,' . $cliente->id],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tipo_cliente' => ['required', 'string', 'max:30'],
            'descuento_default' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $datos['descuento_default'] = $datos['descuento_default'] ?? 0;

        $cliente->update($datos);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente desactivado correctamente.');
    }
}
