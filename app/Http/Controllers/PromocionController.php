<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\ProductoPresentacion;
use App\Models\Promocion;
use App\Models\PromocionItem;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromocionController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');
        $tipo = $request->get('tipo');

        $promociones = Promocion::with(['sucursal', 'creadoPor'])
            ->withCount('items')
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%")
                        ->orWhere('motivo', 'like', "%{$buscar}%");
                });
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($tipo, function ($query, $tipo) {
                $query->where('tipo', $tipo);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('promociones.index', compact(
            'promociones',
            'buscar',
            'estado',
            'tipo'
        ));
    }

    public function create()
    {
        $sucursales = Sucursal::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('promociones.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'in:producto_individual,combo,por_vencimiento'],
            'precio_promocional' => ['required', 'numeric', 'min:0'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.producto_presentacion_id' => ['required', 'exists:producto_presentaciones,id'],
            'items.*.lote_id' => ['nullable', 'exists:lotes,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.unidades_necesarias' => ['required', 'integer', 'min:1'],
            'items.*.precio_referencia' => ['required', 'numeric', 'min:0'],
        ], [
            'nombre.required' => 'El nombre de la promoción es obligatorio.',
            'tipo.required' => 'Seleccione el tipo de promoción.',
            'precio_promocional.required' => 'Ingrese el precio promocional.',
            'fecha_fin.after_or_equal' => 'La fecha fin no puede ser anterior a la fecha inicio.',
            'items.required' => 'Debe agregar al menos un producto a la promoción.',
            'items.min' => 'Debe agregar al menos un producto a la promoción.',
        ]);

        if ($datos['tipo'] === 'producto_individual' && count($datos['items']) !== 1) {
            return back()
                ->withErrors([
                    'items' => 'Una promoción individual debe tener exactamente un producto.',
                ])
                ->withInput();
        }

        if ($datos['tipo'] === 'combo' && count($datos['items']) < 2) {
            return back()
                ->withErrors([
                    'items' => 'Una promoción tipo combo debe tener dos o más productos.',
                ])
                ->withInput();
        }

        if ($datos['tipo'] === 'por_vencimiento' && count($datos['items']) < 1) {
            return back()
                ->withErrors([
                    'items' => 'Una promoción por vencimiento debe tener al menos un producto.',
                ])
                ->withInput();
        }

        $promocion = DB::transaction(function () use ($datos) {
            $promocion = Promocion::create([
                'sucursal_id' => $datos['sucursal_id'] ?? null,
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'tipo' => $datos['tipo'],
                'precio_promocional' => round((float) $datos['precio_promocional'], 2),
                'fecha_inicio' => $datos['fecha_inicio'] ?? null,
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'motivo' => $datos['motivo'] ?? null,
                'estado' => 'activo',
                'creado_por' => auth()->id(),
            ]);

            foreach ($datos['items'] as $item) {
                $productoPresentacion = ProductoPresentacion::where('id', $item['producto_presentacion_id'])
                    ->where('producto_id', $item['producto_id'])
                    ->where('estado', 'activo')
                    ->firstOrFail();

                $cantidad = (int) $item['cantidad'];
                $unidadesEquivalentes = max((int) $productoPresentacion->unidades_equivalentes, 1);
                $unidadesNecesarias = $cantidad * $unidadesEquivalentes;

                PromocionItem::create([
                    'promocion_id' => $promocion->id,
                    'producto_id' => $productoPresentacion->producto_id,
                    'producto_presentacion_id' => $productoPresentacion->id,
                    'lote_id' => $item['lote_id'] ?? null,
                    'cantidad' => $cantidad,
                    'unidades_necesarias' => $unidadesNecesarias,
                    'precio_referencia' => round((float) $item['precio_referencia'], 2),
                ]);
            }

            return $promocion;
        });

        return redirect()
            ->route('promociones.show', $promocion)
            ->with('success', 'Promoción registrada correctamente.');
    }

    public function show(Promocion $promocion)
    {
        $promocion->load([
            'sucursal',
            'creadoPor',
            'items.producto.laboratorio',
            'items.productoPresentacion.presentacion',
            'items.lote',
        ]);

        return view('promociones.show', compact('promocion'));
    }

    public function edit(Promocion $promocion)
    {
        return view('promociones.edit', compact('promocion'));
    }

    public function update(Request $request, Promocion $promocion)
    {
        return back()->with('success', 'Pendiente: aquí actualizaremos la promoción.');
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('promociones.index')
            ->with('success', 'Promoción desactivada correctamente.');
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

        $productos = ProductoPresentacion::with([
                'producto.laboratorio',
                'presentacion',
                'codigosBarras',
            ])
            ->where('estado', 'activo')
            ->whereHas('producto', function ($query) {
                $query->where('estado', 'activo');
            })
            ->where(function ($query) use ($termino) {
                $query->where('nombre_mostrado', 'like', "%{$termino}%")
                    ->orWhereHas('producto', function ($q) use ($termino) {
                        $q->where('nombre_comercial', 'like', "%{$termino}%")
                            ->orWhere('nombre_generico', 'like', "%{$termino}%")
                            ->orWhere('concentracion', 'like', "%{$termino}%")
                            ->orWhereHas('laboratorio', function ($lab) use ($termino) {
                                $lab->where('nombre', 'like', "%{$termino}%");
                            });
                    })
                    ->orWhereHas('codigosBarras', function ($q) use ($termino) {
                        $q->where('codigo', $termino)
                            ->where('estado', 'activo');
                    });
            })
            ->orderByDesc('es_principal')
            ->limit(10)
            ->get()
            ->map(function ($presentacion) use ($sucursal) {
                $producto = $presentacion->producto;
                $unidadesEquivalentes = max((int) $presentacion->unidades_equivalentes, 1);

                $stockDisponible = 0;

                if ($sucursal) {
                    $stockDisponible = Inventario::where('producto_id', $presentacion->producto_id)
                        ->where('sucursal_id', $sucursal->id)
                        ->where('estado', 'activo')
                        ->sum('stock_actual');
                }

                return [
                    'id' => $presentacion->id,
                    'producto_id' => $presentacion->producto_id,

                    'nombre' => $producto->nombre_comercial ?? '-',
                    'nombre_mostrado' => $presentacion->nombre_mostrado,
                    'concentracion' => $producto->concentracion ?? '',
                    'laboratorio' => $producto->laboratorio->nombre ?? '',
                    'tipo_producto' => $producto->tipo_producto ?? '',

                    'presentacion' => $presentacion->presentacion->nombre ?? '',
                    'unidades_equivalentes' => $unidadesEquivalentes,

                    'precio_venta' => (float) $presentacion->precio_venta,
                    'stock_disponible' => (int) $stockDisponible,
                    'stock_aproximado_presentacion' => floor($stockDisponible / $unidadesEquivalentes),
                ];
            })
            ->values();

        return response()->json($productos);
    }
}