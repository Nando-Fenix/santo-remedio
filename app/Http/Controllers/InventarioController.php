<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $sucursalId = $request->get('sucursal_id');

        $inventarios = Inventario::with(['producto', 'sucursal', 'lote'])
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%");
                });
            })
            ->when($sucursalId, function ($query, $sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sucursales = Sucursal::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('inventario.index', compact('inventarios', 'sucursales', 'buscar', 'sucursalId'));
    }

    public function create()
    {
        $productos = Producto::where('estado', 'activo')
            ->orderBy('nombre_comercial')
            ->get();

        $sucursales = Sucursal::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('inventario.create', compact('productos', 'sucursales'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'numero_lote' => ['nullable', 'string', 'max:100'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'motivo' => ['nullable', 'string'],
        ], [
            'producto_id.required' => 'Seleccione un producto.',
            'sucursal_id.required' => 'Seleccione una sucursal.',
            'cantidad.required' => 'Ingrese la cantidad.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        DB::transaction(function () use ($datos) {
            $lote = Lote::firstOrCreate(
                [
                    'producto_id' => $datos['producto_id'],
                    'numero_lote' => $datos['numero_lote'],
                    'fecha_vencimiento' => $datos['fecha_vencimiento'],
                ],
                [
                    'estado' => 'activo',
                ]
            );

            $inventario = Inventario::firstOrCreate(
                [
                    'producto_id' => $datos['producto_id'],
                    'sucursal_id' => $datos['sucursal_id'],
                    'lote_id' => $lote->id,
                ],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => $datos['stock_minimo'],
                    'estado' => 'activo',
                ]
            );

            $stockAnterior = $inventario->stock_actual;
            $stockNuevo = $stockAnterior + $datos['cantidad'];

            $inventario->update([
                'stock_actual' => $stockNuevo,
                'stock_minimo' => $datos['stock_minimo'],
                'estado' => 'activo',
            ]);

            MovimientoInventario::create([
                'producto_id' => $datos['producto_id'],
                'sucursal_id' => $datos['sucursal_id'],
                'lote_id' => $lote->id,
                'usuario_id' => auth()->id(),
                'tipo_movimiento' => 'entrada',
                'cantidad' => $datos['cantidad'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => $datos['motivo'] ?? 'Entrada inicial de inventario',
                'referencia_tipo' => 'entrada_manual',
                'referencia_id' => null,
            ]);
        });

        return redirect()
            ->route('inventario.index')
            ->with('success', 'Entrada de inventario registrada correctamente.');
    }
    public function movimientos(Request $request)
    {
        $buscar = $request->get('buscar');
        $sucursalId = $request->get('sucursal_id');
        $tipoMovimiento = $request->get('tipo_movimiento');

        $movimientos = MovimientoInventario::with(['producto', 'sucursal', 'lote', 'usuario'])
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%");
                });
            })
            ->when($sucursalId, function ($query, $sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            })
            ->when($tipoMovimiento, function ($query, $tipoMovimiento) {
                $query->where('tipo_movimiento', $tipoMovimiento);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sucursales = Sucursal::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('inventario.movimientos', compact(
            'movimientos',
            'sucursales',
            'buscar',
            'sucursalId',
            'tipoMovimiento'
        ));
    }
}
