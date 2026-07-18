<?php

namespace App\Http\Controllers;

use App\Models\BajaInventario;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BajaInventarioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $motivo = $request->get('motivo');
        $estado = $request->get('estado');

        $bajas = BajaInventario::with([
                'producto.laboratorio',
                'sucursal',
                'lote',
                'usuario',
                'usuarioAnulacion',
            ])
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%");
                });
            })
            ->when($motivo, function ($query, $motivo) {
                $query->where('motivo', $motivo);
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('bajas_inventario.index', compact(
            'bajas',
            'buscar',
            'motivo',
            'estado'
        ));
    }

    public function create(Request $request)
    {
        $inventarioSeleccionado = null;

        if ($request->filled('inventario_id')) {
            $user = auth()->user();

            $sucursal = $user->sucursalPrincipal()->first()
                ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

            if ($sucursal) {
                $inventarioSeleccionado = Inventario::with([
                        'producto.laboratorio',
                        'lote',
                    ])
                    ->where('sucursal_id', $sucursal->id)
                    ->where('stock_actual', '>', 0)
                    ->find($request->inventario_id);
            }
        }

        return view('bajas_inventario.create', compact('inventarioSeleccionado'));
    }

    public function buscarProductos(Request $request)
    {
        $termino = trim($request->get('busqueda', ''));

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return response()->json([]);
        }

        $inventarios = Inventario::with([
                'producto.laboratorio',
                'lote',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('stock_actual', '>', 0)
            ->whereHas('producto', function ($query) use ($termino) {
                $query->where('estado', 'activo')
                    ->where(function ($q) use ($termino) {
                        $q->where('nombre_comercial', 'like', "%{$termino}%")
                            ->orWhere('nombre_generico', 'like', "%{$termino}%")
                            ->orWhere('concentracion', 'like', "%{$termino}%")
                            ->orWhereHas('laboratorio', function ($lab) use ($termino) {
                                $lab->where('nombre', 'like', "%{$termino}%");
                            });
                    });
            })
            ->orderBy('producto_id')
            ->limit(20)
            ->get()
            ->map(function ($inventario) {
                return [
                    'inventario_id' => $inventario->id,
                    'producto_id' => $inventario->producto_id,
                    'lote_id' => $inventario->lote_id,
                    'producto' => $inventario->producto->nombre_comercial ?? '-',
                    'generico' => $inventario->producto->nombre_generico ?? '',
                    'concentracion' => $inventario->producto->concentracion ?? '',
                    'laboratorio' => $inventario->producto->laboratorio->nombre ?? '',
                    'lote' => $inventario->lote->numero_lote ?? 'Sin lote',
                    'fecha_vencimiento' => $inventario->lote?->fecha_vencimiento
                        ? $inventario->lote->fecha_vencimiento->format('d/m/Y')
                        : null,
                    'stock_actual' => (int) $inventario->stock_actual,
                ];
            })
            ->values();

        return response()->json($inventarios);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'inventario_id' => ['required', 'exists:inventarios,id'],
            'motivo' => ['required', 'in:vencimiento,danado,perdido,ajuste_autorizado,otro'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'observacion' => ['nullable', 'string'],
        ], [
            'inventario_id.required' => 'Debe seleccionar un producto del inventario.',
            'motivo.required' => 'Debe seleccionar el motivo de la baja.',
            'cantidad.required' => 'Debe ingresar la cantidad a dar de baja.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return back()
                ->withErrors(['sucursal' => 'El usuario no tiene una sucursal asignada.'])
                ->withInput();
        }

        $inventario = Inventario::with(['producto', 'lote'])
            ->where('sucursal_id', $sucursal->id)
            ->findOrFail($datos['inventario_id']);

        $cantidad = (int) $datos['cantidad'];

        if ($inventario->stock_actual < $cantidad) {
            return back()
                ->withErrors([
                    'cantidad' => 'La cantidad a dar de baja supera el stock disponible.',
                ])
                ->withInput();
        }

        $baja = DB::transaction(function () use ($datos, $user, $sucursal, $inventario, $cantidad) {
            $stockAnterior = $inventario->stock_actual;
            $stockNuevo = $stockAnterior - $cantidad;

            $inventario->update([
                'stock_actual' => $stockNuevo,
                'estado' => $stockNuevo <= 0 ? 'agotado' : 'activo',
            ]);

            $baja = BajaInventario::create([
                'producto_id' => $inventario->producto_id,
                'sucursal_id' => $sucursal->id,
                'lote_id' => $inventario->lote_id,
                'usuario_id' => $user->id,
                'motivo' => $datos['motivo'],
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'observacion' => $datos['observacion'] ?? null,
                'estado' => 'registrado',
            ]);

            MovimientoInventario::create([
                'producto_id' => $inventario->producto_id,
                'sucursal_id' => $sucursal->id,
                'lote_id' => $inventario->lote_id,
                'usuario_id' => $user->id,
                'tipo_movimiento' => 'salida',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => 'Baja de inventario por ' . $datos['motivo'],
                'referencia_tipo' => 'baja_inventario',
                'referencia_id' => $baja->id,
            ]);

            return $baja;
        });

        return redirect()
            ->route('bajas-inventario.show', $baja)
            ->with('success', 'Baja de inventario registrada correctamente.');
    }

    public function show(BajaInventario $bajaInventario)
    {
        $bajaInventario->load([
            'producto.laboratorio',
            'sucursal',
            'lote',
            'usuario',
            'usuarioAnulacion',
        ]);

        return view('bajas_inventario.show', compact('bajaInventario'));
    }

    public function anularCreate(BajaInventario $bajaInventario)
    {
        $bajaInventario->load([
            'producto.laboratorio',
            'sucursal',
            'lote',
            'usuario',
        ]);

        if ($bajaInventario->estado !== 'registrado') {
            return redirect()
                ->route('bajas-inventario.show', $bajaInventario)
                ->with('error', 'Solo se pueden anular bajas registradas.');
        }

        return view('bajas_inventario.anular', compact('bajaInventario'));
    }

    public function anularStore(Request $request, BajaInventario $bajaInventario)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'min:5'],
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($bajaInventario->estado !== 'registrado') {
            return redirect()
                ->route('bajas-inventario.show', $bajaInventario)
                ->with('error', 'Solo se pueden anular bajas registradas.');
        }

        $user = auth()->user();

        DB::transaction(function () use ($bajaInventario, $datos, $user) {
            $inventario = Inventario::firstOrCreate(
                [
                    'producto_id' => $bajaInventario->producto_id,
                    'sucursal_id' => $bajaInventario->sucursal_id,
                    'lote_id' => $bajaInventario->lote_id,
                ],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => 5,
                    'estado' => 'activo',
                ]
            );

            $stockAnterior = $inventario->stock_actual;
            $stockNuevo = $stockAnterior + $bajaInventario->cantidad;

            $inventario->update([
                'stock_actual' => $stockNuevo,
                'estado' => 'activo',
            ]);

            MovimientoInventario::create([
                'producto_id' => $bajaInventario->producto_id,
                'sucursal_id' => $bajaInventario->sucursal_id,
                'lote_id' => $bajaInventario->lote_id,
                'usuario_id' => $user->id,
                'tipo_movimiento' => 'entrada',
                'cantidad' => $bajaInventario->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => 'Anulación de baja de inventario. Motivo: ' . $datos['motivo_anulacion'],
                'referencia_tipo' => 'anulacion_baja_inventario',
                'referencia_id' => $bajaInventario->id,
            ]);

            $bajaInventario->update([
                'estado' => 'anulado',
                'usuario_anulacion_id' => $user->id,
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $datos['motivo_anulacion'],
            ]);
        });

        return redirect()
            ->route('bajas-inventario.show', $bajaInventario)
            ->with('success', 'Baja de inventario anulada correctamente. El stock fue devuelto.');
    }
}
