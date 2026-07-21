<?php

namespace App\Http\Controllers;

use App\Models\ProductoPresentacion;
use App\Models\ServicioFarmacia;
use App\Models\ServicioFarmaciaInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioFarmaciaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $tipo = $request->get('tipo');
        $estado = $request->get('estado');

        $servicios = ServicioFarmacia::withCount('insumos')
            ->when($buscar, function ($query, $buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            })
            ->when($tipo, function ($query, $tipo) {
                $query->where('tipo', $tipo);
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('servicios_farmacia.index', compact(
            'servicios',
            'buscar',
            'tipo',
            'estado'
        ));
    }

    public function create()
    {
        return view('servicios_farmacia.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'tipo' => ['required', 'in:inyectable,control,curacion,nebulizacion,orientacion,otro'],

            'insumos' => ['nullable', 'array'],
            'insumos.*.producto_id' => ['required', 'exists:productos,id'],
            'insumos.*.producto_presentacion_id' => ['required', 'exists:producto_presentaciones,id'],
            'insumos.*.cantidad' => ['required', 'integer', 'min:1'],
            'insumos.*.unidades_necesarias' => ['required', 'integer', 'min:1'],
        ], [
            'nombre.required' => 'Debe ingresar el nombre del servicio.',
            'precio.required' => 'Debe ingresar el precio del servicio.',
            'tipo.required' => 'Debe seleccionar el tipo de servicio.',
        ]);

        $servicio = DB::transaction(function () use ($datos) {
            $servicio = ServicioFarmacia::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'precio' => $datos['precio'],
                'tipo' => $datos['tipo'],
                'estado' => 'activo',
                'creado_por' => auth()->id(),
            ]);

            foreach (($datos['insumos'] ?? []) as $item) {
                $presentacion = ProductoPresentacion::where('estado', 'activo')
                    ->whereHas('producto', function ($query) {
                        $query->where('estado', 'activo');
                    })
                    ->findOrFail($item['producto_presentacion_id']);

                $cantidad = (int) $item['cantidad'];
                $unidadesNecesarias = $cantidad * max((int) $presentacion->unidades_equivalentes, 1);

                ServicioFarmaciaInsumo::create([
                    'servicio_farmacia_id' => $servicio->id,
                    'producto_id' => $presentacion->producto_id,
                    'producto_presentacion_id' => $presentacion->id,
                    'cantidad' => $cantidad,
                    'unidades_necesarias' => $unidadesNecesarias,
                ]);
            }

            return $servicio;
        });

        return redirect()
            ->route('servicios-farmacia.show', $servicio)
            ->with('success', 'Servicio de farmacia creado correctamente.');
    }

    public function show(ServicioFarmacia $servicioFarmacia)
    {
        $servicioFarmacia->load([
            'insumos.producto.laboratorio',
            'insumos.productoPresentacion.presentacion',
            'creadoPor',
        ]);

        return view('servicios_farmacia.show', compact('servicioFarmacia'));
    }

    public function edit(ServicioFarmacia $servicioFarmacia)
    {
        $servicioFarmacia->load([
            'insumos.producto.laboratorio',
            'insumos.productoPresentacion.presentacion',
        ]);

        return view('servicios_farmacia.edit', compact('servicioFarmacia'));
    }

    public function update(Request $request, ServicioFarmacia $servicioFarmacia)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'tipo' => ['required', 'in:inyectable,control,curacion,nebulizacion,orientacion,otro'],
            'estado' => ['required', 'in:activo,inactivo'],

            'insumos_enviados' => ['nullable'],
            'insumos' => ['nullable', 'array'],
            'insumos.*.producto_id' => ['required', 'exists:productos,id'],
            'insumos.*.producto_presentacion_id' => ['required', 'exists:producto_presentaciones,id'],
            'insumos.*.cantidad' => ['required', 'integer', 'min:1'],
            'insumos.*.unidades_necesarias' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($datos, $servicioFarmacia) {
            $servicioFarmacia->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'precio' => $datos['precio'],
                'tipo' => $datos['tipo'],
                'estado' => $datos['estado'],
            ]);

            $servicioFarmacia->insumos()->delete();

            foreach (($datos['insumos'] ?? []) as $item) {
                $presentacion = ProductoPresentacion::where('estado', 'activo')
                    ->whereHas('producto', function ($query) {
                        $query->where('estado', 'activo');
                    })
                    ->findOrFail($item['producto_presentacion_id']);

                $cantidad = (int) $item['cantidad'];
                $unidadesNecesarias = $cantidad * max((int) $presentacion->unidades_equivalentes, 1);

                ServicioFarmaciaInsumo::create([
                    'servicio_farmacia_id' => $servicioFarmacia->id,
                    'producto_id' => $presentacion->producto_id,
                    'producto_presentacion_id' => $presentacion->id,
                    'cantidad' => $cantidad,
                    'unidades_necesarias' => $unidadesNecesarias,
                ]);
            }
        });

        return redirect()
            ->route('servicios-farmacia.show', $servicioFarmacia)
            ->with('success', 'Servicio de farmacia actualizado correctamente.');
    }

    public function destroy(ServicioFarmacia $servicioFarmacia)
    {
        $servicioFarmacia->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('servicios-farmacia.index')
            ->with('success', 'Servicio de farmacia desactivado correctamente.');
    }

    public function buscarProductos(Request $request)
    {
        $termino = trim($request->get('busqueda', ''));

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $productos = ProductoPresentacion::with([
                'producto.laboratorio',
                'presentacion',
            ])
            ->where('estado', 'activo')
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
            ->limit(15)
            ->get()
            ->map(function ($presentacion) {
                $producto = $presentacion->producto;

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
                    'unidades_equivalentes' => max((int) $presentacion->unidades_equivalentes, 1),
                ];
            })
            ->values();

        return response()->json($productos);
    }
}