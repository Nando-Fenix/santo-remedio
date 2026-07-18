<?php

namespace App\Http\Controllers;

use App\Models\CodigoBarra;
use App\Models\Inventario;
use App\Models\ProductoPresentacion;
use Illuminate\Http\Request;
use App\Models\Promocion;
use Carbon\Carbon;

class VentaProductoController extends Controller
{
    public function buscar(Request $request)
    {
        $termino = trim($request->get('busqueda', $request->get('q', '')));

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return response()->json([
                'error' => 'El usuario no tiene una sucursal asignada.',
            ], 422);
        }

        $codigo = CodigoBarra::with([
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->where('codigo', $termino)
            ->where('estado', 'activo')
            ->first();

        if ($codigo && $codigo->productoPresentacion) {
            $presentacion = $codigo->productoPresentacion;
            $producto = $presentacion->producto;

            if ($producto && $producto->estado === 'activo' && $presentacion->estado === 'activo') {
                $stockDisponible = $this->stockDisponible($producto->id, $sucursal->id);

                if ($stockDisponible <= 0) {
                    return response()->json([]);
                }

                $unidadesEquivalentes = max((int) $presentacion->unidades_equivalentes, 1);

                return response()->json([
                    [
                        'id' => $presentacion->id,
                        'producto_id' => $producto->id,

                        'nombre' => $producto->nombre_comercial,
                        'nombre_mostrado' => $presentacion->nombre_mostrado,
                        'generico' => $producto->nombre_generico,
                        'concentracion' => $producto->concentracion,
                        'laboratorio' => $producto->laboratorio->nombre ?? '',
                        'tipo_producto' => $producto->tipo_producto ?? '',

                        'presentacion' => $presentacion->presentacion->nombre ?? '',
                        'precio_venta' => (float) $presentacion->precio_venta,
                        'unidades_equivalentes' => $unidadesEquivalentes,
                        'stock_disponible' => (int) $stockDisponible,
                        'stock_aproximado_presentacion' => floor($stockDisponible / $unidadesEquivalentes),

                        'codigo_barras' => $codigo->codigo,
                    ],
                ]);
            }
        }

        $presentaciones = ProductoPresentacion::with([
                'producto.laboratorio',
                'presentacion',
            ])
            ->where('estado', 'activo')
            ->where(function ($query) use ($termino) {
                $query->where('nombre_mostrado', 'like', "%{$termino}%")
                    ->orWhereHas('producto', function ($query) use ($termino) {
                        $query->where('estado', 'activo')
                            ->where(function ($q) use ($termino) {
                                $q->where('nombre_comercial', 'like', "%{$termino}%")
                                    ->orWhere('nombre_generico', 'like', "%{$termino}%")
                                    ->orWhere('concentracion', 'like', "%{$termino}%")
                                    ->orWhereHas('laboratorio', function ($lab) use ($termino) {
                                        $lab->where('nombre', 'like', "%{$termino}%");
                                    });
                            });
                    });
            })
            ->whereHas('producto', function ($query) {
                $query->where('estado', 'activo');
            })
            ->orderByDesc('es_principal')
            ->limit(10)
            ->get();

        $resultados = $presentaciones->map(function ($presentacion) use ($sucursal) {
            $producto = $presentacion->producto;

            $stockDisponible = $this->stockDisponible($producto->id, $sucursal->id);
            $unidadesEquivalentes = max((int) $presentacion->unidades_equivalentes, 1);

            return [
                'id' => $presentacion->id,
                'producto_id' => $producto->id,

                'nombre' => $producto->nombre_comercial,
                'nombre_mostrado' => $presentacion->nombre_mostrado,
                'generico' => $producto->nombre_generico,
                'concentracion' => $producto->concentracion,
                'laboratorio' => $producto->laboratorio->nombre ?? '',
                'tipo_producto' => $producto->tipo_producto ?? '',

                'presentacion' => $presentacion->presentacion->nombre ?? '',
                'precio_venta' => (float) $presentacion->precio_venta,
                'unidades_equivalentes' => $unidadesEquivalentes,
                'stock_disponible' => (int) $stockDisponible,
                'stock_aproximado_presentacion' => floor($stockDisponible / $unidadesEquivalentes),

                'codigo_barras' => null,
            ];
        })
        ->filter(function ($producto) {
            return $producto['stock_disponible'] > 0;
        })
        ->values();

        return response()->json($resultados);
    }

    private function stockDisponible(int $productoId, int $sucursalId): int
    {
        return (int) Inventario::leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
            ->where('inventarios.producto_id', $productoId)
            ->where('inventarios.sucursal_id', $sucursalId)
            ->where('inventarios.estado', 'activo')
            ->where(function ($query) {
                $query->whereNull('lotes.fecha_vencimiento')
                    ->orWhereDate('lotes.fecha_vencimiento', '>=', now()->toDateString());
            })
            ->sum('inventarios.stock_actual');
    }

    public function buscarPromociones(Request $request)
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

        $hoy = Carbon::today();

        $promociones = Promocion::with([
                'items.producto.laboratorio',
                'items.productoPresentacion.presentacion',
                'items.lote',
            ])
            ->where('estado', 'activo')
            ->where(function ($query) use ($sucursal) {
                $query->whereNull('sucursal_id')
                    ->orWhere('sucursal_id', $sucursal->id);
            })
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_inicio')
                    ->orWhereDate('fecha_inicio', '<=', $hoy);
            })
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%")
                    ->orWhere('motivo', 'like', "%{$termino}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($promocion) use ($sucursal, $hoy) {
                $stockMaximoPromocion = null;

                foreach ($promocion->items as $item) {
                    if (
                        $item->lote &&
                        $item->lote->fecha_vencimiento &&
                        $item->lote->fecha_vencimiento->lt($hoy)
                    ) {
                        return null;
                    }

                    $stockQuery = Inventario::leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                        ->where('inventarios.producto_id', $item->producto_id)
                        ->where('inventarios.sucursal_id', $sucursal->id)
                        ->where('inventarios.estado', 'activo')
                        ->where(function ($query) {
                            $query->whereNull('lotes.fecha_vencimiento')
                                ->orWhereDate('lotes.fecha_vencimiento', '>=', now()->toDateString());
                        });

                    if ($item->lote_id) {
                        $stockQuery->where('inventarios.lote_id', $item->lote_id);
                    }

                    $stockDisponible = (int) $stockQuery->sum('inventarios.stock_actual');

                    $unidadesNecesarias = max((int) $item->unidades_necesarias, 1);

                    $vecesDisponibles = intdiv($stockDisponible, $unidadesNecesarias);

                    if ($stockMaximoPromocion === null || $vecesDisponibles < $stockMaximoPromocion) {
                        $stockMaximoPromocion = $vecesDisponibles;
                    }
                }

                $promocion->stock_promocion = $stockMaximoPromocion ?? 0;

                return $promocion;
            })
            ->filter(function ($promocion) {
                return $promocion && $promocion->stock_promocion > 0;
            })
            ->map(function ($promocion) {
                return [
                    'id' => $promocion->id,
                    'nombre' => $promocion->nombre,
                    'descripcion' => $promocion->descripcion,
                    'tipo' => $promocion->tipo,
                    'precio_promocional' => (float) $promocion->precio_promocional,
                    'stock_promocion' => (int) $promocion->stock_promocion,
                    'items_count' => $promocion->items->count(),
                    'items' => $promocion->items->map(function ($item) {
                        return [
                            'producto' => $item->productoPresentacion->nombre_mostrado
                                ?? $item->producto->nombre_comercial
                                ?? '-',
                            'cantidad' => $item->cantidad,
                            'unidades_necesarias' => $item->unidades_necesarias,
                            'lote' => $item->lote->numero_lote ?? null,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json($promociones);
    }
}
