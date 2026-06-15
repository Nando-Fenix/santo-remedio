<?php

namespace App\Http\Controllers;

use App\Models\CodigoBarra;
use App\Models\Inventario;
use App\Models\ProductoPresentacion;
use Illuminate\Http\Request;

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

        // 1. Buscar por código de barras exacto
        $codigo = CodigoBarra::with([
                'producto',
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

                return response()->json([
                    [
                        'id' => $presentacion->id,
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre_comercial,
                        'generico' => $producto->nombre_generico,
                        'concentracion' => $producto->concentracion,
                        'presentacion' => $presentacion->nombre_mostrado,
                        'precio_venta' => (float) $presentacion->precio_venta,
                        'unidades_equivalentes' => $presentacion->unidades_equivalentes,
                        'stock_disponible' => $stockDisponible,
                        'codigo_barras' => $codigo->codigo,
                    ],
                ]);
            }
        }

        // 2. Buscar por nombre comercial, genérico o concentración
        $presentaciones = ProductoPresentacion::with(['producto', 'presentacion'])
            ->where('estado', 'activo')
            ->whereHas('producto', function ($query) use ($termino) {
                $query->where('estado', 'activo')
                    ->where(function ($q) use ($termino) {
                        $q->where('nombre_comercial', 'like', "%{$termino}%")
                            ->orWhere('nombre_generico', 'like', "%{$termino}%")
                            ->orWhere('concentracion', 'like', "%{$termino}%");
                    });
            })
            ->orderByDesc('es_principal')
            ->limit(10)
            ->get();

        $resultados = $presentaciones->map(function ($presentacion) use ($sucursal) {
            $producto = $presentacion->producto;

            $stockDisponible = $this->stockDisponible($producto->id, $sucursal->id);

            return [
                'id' => $presentacion->id,
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre_comercial,
                'generico' => $producto->nombre_generico,
                'concentracion' => $producto->concentracion,
                'presentacion' => $presentacion->nombre_mostrado,
                'precio_venta' => (float) $presentacion->precio_venta,
                'unidades_equivalentes' => $presentacion->unidades_equivalentes,
                'stock_disponible' => $stockDisponible,
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
        return Inventario::where('producto_id', $productoId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'activo')
            ->sum('stock_actual');
    }
}
