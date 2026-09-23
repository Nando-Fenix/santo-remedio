<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{

    private function obtenerSucursalActual()
    {
        $user = auth()->user();

        return $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('buscar'));
        $categoriaId = $request->get('categoria_id');
        $estadoStock = $request->get('estado_stock');
        $estadoVencimiento = $request->get('estado_vencimiento');

        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $inventarios = Inventario::with(['producto.categoria', 'sucursal', 'lote'])
            ->where('sucursal_id', $sucursal->id)
            ->when($buscar, function ($query) use ($buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%");
                });
            })
            ->when($categoriaId, function ($query) use ($categoriaId) {
                $query->whereHas('producto', function ($q) use ($categoriaId) {
                    $q->where('categoria_id', $categoriaId);
                });
            })
            ->when($estadoStock === 'disponible', function ($query) {
                $query->whereColumn('stock_actual', '>', 'stock_minimo');
            })
            ->when($estadoStock === 'bajo', function ($query) {
                $query->where('stock_actual', '>', 0)
                    ->whereColumn('stock_actual', '<=', 'stock_minimo');
            })
            ->when($estadoStock === 'agotado', function ($query) {
                $query->where('stock_actual', '<=', 0);
            })
            ->when($estadoVencimiento === 'vigente', function ($query) {
                $query->whereHas('lote', function ($q) {
                    $q->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '>', now()->addDays(30)->toDateString());
                });
            })
            ->when($estadoVencimiento === 'proximo', function ($query) {
                $query->where('stock_actual', '>', 0)
                    ->whereHas('lote', function ($q) {
                        $q->whereNotNull('fecha_vencimiento')
                            ->whereBetween('fecha_vencimiento', [
                                now()->toDateString(),
                                now()->addDays(30)->toDateString(),
                            ]);
                    });
            })
            ->when($estadoVencimiento === 'vencido', function ($query) {
                $query->where('stock_actual', '>', 0)
                    ->whereHas('lote', function ($q) {
                        $q->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<', now()->toDateString());
                    });
            })
            ->when($estadoVencimiento === 'sin_fecha', function ($query) {
                $query->where(function ($q) {
                    $q->whereDoesntHave('lote')
                        ->orWhereHas('lote', function ($loteQuery) {
                            $loteQuery->whereNull('fecha_vencimiento');
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categorias = Categoria::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $sucursales = collect([$sucursal]);
        $sucursalId = $sucursal->id;

        return view('inventario.index', compact(
            'inventarios',
            'sucursales',
            'categorias',
            'buscar',
            'sucursalId',
            'categoriaId',
            'estadoStock',
            'estadoVencimiento'
        ));
    }

    public function create()
    {
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $categorias = Categoria::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
            
        $productos = Producto::where('estado', 'activo')
            ->orderBy('nombre_comercial')
            ->get();

        $sucursales = collect([$sucursal]);

        return view('inventario.create', compact('productos', 'sucursales', 'sucursal', 'categorias'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
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

        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return back()
                ->withErrors([
                    'sucursal' => 'El usuario no tiene una sucursal asignada.',
                ])
                ->withInput();
        }

        $datos['sucursal_id'] = $sucursal->id;

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
        $tipoMovimiento = $request->get('tipo_movimiento');
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $movimientos = MovimientoInventario::with(['producto', 'sucursal', 'lote', 'usuario'])
            ->where('sucursal_id', $sucursal->id)
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%");
                });
            })
            ->when($tipoMovimiento, function ($query, $tipoMovimiento) {
                $query->where('tipo_movimiento', $tipoMovimiento);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sucursales = collect([$sucursal]);
        $sucursalId = $sucursal->id;

        return view('inventario.movimientos', compact(
            'movimientos',
            'sucursales',
            'buscar',
            'sucursalId',
            'tipoMovimiento'
        ));
    }

    public function proximosVencer(Request $request)
    {
        $dias = (int) $request->get('dias', 30);
        $buscar = $request->get('buscar');

        if ($dias < 1) {
            $dias = 30;
        }

        $hoy = Carbon::today();
        $fechaLimite = Carbon::today()->addDays($dias);

        $inventarios = Inventario::with([
                'producto.laboratorio',
                'sucursal',
                'lote',
            ])
            ->where('stock_actual', '>', 0)
            ->whereHas('lote', function ($query) use ($hoy, $fechaLimite) {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->whereDate('fecha_vencimiento', '<=', $fechaLimite);
            })
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%")
                        ->orWhereHas('laboratorio', function ($lab) use ($buscar) {
                            $lab->where('nombre', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderBy(
                Lote::select('fecha_vencimiento')
                    ->whereColumn('lotes.id', 'inventarios.lote_id')
            )
            ->paginate(15)
            ->withQueryString();

        return view('inventario.proximos-vencer', compact(
            'inventarios',
            'dias',
            'buscar',
            'hoy'
        ));
    }

    public function productosVencidos(Request $request)
    {
        $buscar = $request->get('buscar');

        $hoy = Carbon::today();

        $inventarios = Inventario::with([
                'producto.laboratorio',
                'sucursal',
                'lote',
            ])
            ->where('stock_actual', '>', 0)
            ->whereHas('lote', function ($query) use ($hoy) {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '<', $hoy);
            })
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('producto', function ($q) use ($buscar) {
                    $q->where('nombre_comercial', 'like', "%{$buscar}%")
                        ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                        ->orWhere('concentracion', 'like', "%{$buscar}%")
                        ->orWhereHas('laboratorio', function ($lab) use ($buscar) {
                            $lab->where('nombre', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderBy(
                Lote::select('fecha_vencimiento')
                    ->whereColumn('lotes.id', 'inventarios.lote_id')
            )
            ->paginate(15)
            ->withQueryString();

        return view('inventario.productos-vencidos', compact(
            'inventarios',
            'buscar',
            'hoy'
        ));
    }

    public function categoriaRapida(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre'],
        ]);

        $categoria = Categoria::create([
            'nombre' => $datos['nombre'],
            'descripcion' => 'Categoría creada desde entrada de inventario',
            'estado' => 'activo',
        ]);

        return response()->json([
            'categoria' => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
            ],
        ]);
    }

    public function productoRapido(Request $request)
    {
        $datos = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'nombre_generico' => ['nullable', 'string', 'max:150'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'tipo_producto' => ['required', 'string', 'in:medicamento,insumo_medico,producto_general,higiene,bebe,otro'],
            'categoria_id' => ['required', 'exists:categorias,id'],
        ]);


        $producto = Producto::create([
            'nombre_comercial' => $datos['nombre_comercial'],
            'nombre_generico' => $datos['nombre_generico'] ?? null,
            'concentracion' => $datos['concentracion'] ?? null,
            'tipo_producto' => $datos['tipo_producto'],
            'categoria_id' => $datos['categoria_id'],
            'laboratorio_id' => null,
            'proveedor_id' => null,
            'estado' => 'activo',
        ]);

        return response()->json([
            'producto' => [
                'id' => $producto->id,
                'nombre' => trim($producto->nombre_comercial . ' ' . ($producto->concentracion ? '- ' . $producto->concentracion : '')),
            ],
        ]);
    }
}
