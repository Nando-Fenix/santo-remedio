<?php

namespace App\Http\Controllers;

use App\Models\AtencionServicio;
use App\Models\AtencionServicioInsumo;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\MetodoPago;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\ServicioFarmacia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtencionServicioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $atenciones = AtencionServicio::with([
                'servicio',
                'sucursal',
                'usuario',
                'cliente',
                'metodoPago',
            ])
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('servicio', function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%");
                })
                ->orWhereHas('cliente', function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('ci_nit', 'like', "%{$buscar}%");
                });
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($fechaInicio, function ($query, $fechaInicio) {
                $query->whereDate('fecha_hora', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($query, $fechaFin) {
                $query->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->latest('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        return view('atenciones_servicio.index', compact(
            'atenciones',
            'buscar',
            'estado',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function create()
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $cajaAbierta = Caja::where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'Debe abrir caja antes de registrar servicios.');
        }

        $servicios = ServicioFarmacia::with([
                'insumos.producto',
                'insumos.productoPresentacion.presentacion',
            ])
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $servicios->each(function ($servicio) use ($sucursal) {
            $servicio->insumos->each(function ($insumo) use ($sucursal) {
                $stockDisponible = Inventario::leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                    ->where('inventarios.producto_id', $insumo->producto_id)
                    ->where('inventarios.sucursal_id', $sucursal->id)
                    ->where('inventarios.estado', 'activo')
                    ->where(function ($query) {
                        $query->whereNull('lotes.fecha_vencimiento')
                            ->orWhereDate('lotes.fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->sum('inventarios.stock_actual');

                $unidadesEquivalentes = max((int) ($insumo->productoPresentacion->unidades_equivalentes ?? 1), 1);

                $insumo->stock_disponible = (int) $stockDisponible;
                $insumo->stock_aproximado_presentacion = intdiv((int) $stockDisponible, $unidadesEquivalentes);
            });
        });

        $clientes = Cliente::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $metodosPago = MetodoPago::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('atenciones_servicio.create', compact(
            'sucursal',
            'cajaAbierta',
            'servicios',
            'clientes',
            'metodosPago'
        ));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'servicio_farmacia_id' => ['required', 'exists:servicios_farmacia,id'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'metodo_pago_id' => ['required', 'exists:metodos_pago,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string'],

            'insumos_filtrados' => ['nullable'],
            'insumos_usados' => ['nullable', 'array'],
            'insumos_usados.*' => ['integer', 'exists:servicio_farmacia_insumos,id'],
        ], [
            'servicio_farmacia_id.required' => 'Debe seleccionar el servicio.',
            'metodo_pago_id.required' => 'Debe seleccionar el método de pago.',
            'cantidad.required' => 'Debe ingresar la cantidad.',
        ]);

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return back()
                ->withErrors(['sucursal' => 'El usuario no tiene una sucursal asignada.'])
                ->withInput();
        }

        $cajaAbierta = Caja::where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta) {
            return back()
                ->withErrors(['caja' => 'Debe abrir caja antes de registrar servicios.'])
                ->withInput();
        }

        $servicio = ServicioFarmacia::with([
                'insumos.producto',
                'insumos.productoPresentacion',
            ])
            ->where('estado', 'activo')
            ->findOrFail($datos['servicio_farmacia_id']);

        $insumosUsadosIds = collect($datos['insumos_usados'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $insumosADescontar = ($request->filled('insumos_filtrados'))
            ? $servicio->insumos->whereIn('id', $insumosUsadosIds)->values()
            : $servicio->insumos;

        $cantidad = (int) $datos['cantidad'];
        $precioUnitario = round((float) $servicio->precio, 2);
        $subtotal = round($precioUnitario * $cantidad, 2);
        $descuento = round((float) ($datos['descuento'] ?? 0), 2);

        if ($descuento > $subtotal) {
            return back()
                ->withErrors(['descuento' => 'El descuento no puede ser mayor al subtotal.'])
                ->withInput();
        }

        $total = round($subtotal - $descuento, 2);

        foreach ($insumosADescontar as $insumo) {
            $unidadesNecesarias = $insumo->unidades_necesarias * $cantidad;

            $stockDisponible = Inventario::leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                ->where('inventarios.producto_id', $insumo->producto_id)
                ->where('inventarios.sucursal_id', $sucursal->id)
                ->where('inventarios.estado', 'activo')
                ->where(function ($query) {
                    $query->whereNull('lotes.fecha_vencimiento')
                        ->orWhereDate('lotes.fecha_vencimiento', '>=', now()->toDateString());
                })
                ->sum('inventarios.stock_actual');

            if ($stockDisponible < $unidadesNecesarias) {
                return back()
                    ->withErrors([
                        'stock' => 'No hay stock suficiente para el insumo: ' . ($insumo->producto->nombre_comercial ?? 'Producto'),
                    ])
                    ->withInput();
            }
        }

        $atencion = DB::transaction(function () use ($datos, $user, $sucursal, $cajaAbierta, $servicio, $insumosADescontar, $cantidad, $precioUnitario, $subtotal, $descuento, $total) {            $atencion = AtencionServicio::create([
                'servicio_farmacia_id' => $servicio->id,
                'sucursal_id' => $sucursal->id,
                'usuario_id' => $user->id,
                'cliente_id' => $datos['cliente_id'] ?? null,
                'caja_id' => $cajaAbierta->id,
                'metodo_pago_id' => $datos['metodo_pago_id'],
                'fecha_hora' => now(),
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'estado' => 'completada',
                'observacion' => $datos['observacion'] ?? null,
            ]);

            foreach ($insumosADescontar as $insumo) {
                $unidadesPendientes = $insumo->unidades_necesarias * $cantidad;

                $inventarios = Inventario::with('lote')
                    ->leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                    ->where('inventarios.producto_id', $insumo->producto_id)
                    ->where('inventarios.sucursal_id', $sucursal->id)
                    ->where('inventarios.estado', 'activo')
                    ->where('inventarios.stock_actual', '>', 0)
                    ->where(function ($query) {
                        $query->whereNull('lotes.fecha_vencimiento')
                            ->orWhereDate('lotes.fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderByRaw('lotes.fecha_vencimiento IS NULL')
                    ->orderBy('lotes.fecha_vencimiento')
                    ->select('inventarios.*')
                    ->get();

                foreach ($inventarios as $inventario) {
                    if ($unidadesPendientes <= 0) {
                        break;
                    }

                    $stockAnterior = $inventario->stock_actual;
                    $descontar = min($stockAnterior, $unidadesPendientes);
                    $stockNuevo = $stockAnterior - $descontar;

                    $inventario->update([
                        'stock_actual' => $stockNuevo,
                        'estado' => $stockNuevo <= 0 ? 'agotado' : 'activo',
                    ]);

                    AtencionServicioInsumo::create([
                        'atencion_servicio_id' => $atencion->id,
                        'servicio_farmacia_insumo_id' => $insumo->id,
                        'producto_id' => $insumo->producto_id,
                        'producto_presentacion_id' => $insumo->producto_presentacion_id,
                        'lote_id' => $inventario->lote_id,
                        'unidades_descontadas' => $descontar,
                    ]);

                    MovimientoInventario::create([
                        'producto_id' => $insumo->producto_id,
                        'sucursal_id' => $sucursal->id,
                        'lote_id' => $inventario->lote_id,
                        'usuario_id' => $user->id,
                        'tipo_movimiento' => 'salida',
                        'cantidad' => $descontar,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'Salida por servicio ' . $servicio->nombre,
                        'referencia_tipo' => 'atencion_servicio',
                        'referencia_id' => $atencion->id,
                    ]);

                    $unidadesPendientes -= $descontar;
                }
            }

            $metodoPago = MetodoPago::find($datos['metodo_pago_id']);

            if ($metodoPago?->tipo === 'efectivo') {
                $cajaAbierta->increment('total_efectivo', $total);
            } else {
                $cajaAbierta->increment('total_qr', $total);
            }

            $cajaAbierta->refresh();

            $cajaAbierta->update([
                'total_final' => round(
                    $cajaAbierta->monto_inicial
                    + $cajaAbierta->total_efectivo
                    + $cajaAbierta->total_qr
                    - $cajaAbierta->total_egresos
                    - $cajaAbierta->total_reembolsos,
                    2
                ),
            ]);

            MovimientoCaja::create([
                'caja_id' => $cajaAbierta->id,
                'usuario_id' => $user->id,
                'sucursal_id' => $sucursal->id,
                'metodo_pago_id' => $datos['metodo_pago_id'],
                'tipo_movimiento' => 'ingreso',
                'monto' => $total,
                'descripcion' => 'Ingreso por servicio ' . $servicio->nombre,
                'autorizado_por' => null,
            ]);

            return $atencion;
        });

        return redirect()
            ->route('atenciones-servicio.show', $atencion)
            ->with('success', 'Atención de servicio registrada correctamente.');
    }

    public function show(AtencionServicio $atencionServicio)
    {
        $atencionServicio->load([
            'servicio',
            'sucursal',
            'usuario',
            'cliente',
            'caja',
            'metodoPago',
            'insumos.producto.laboratorio',
            'insumos.productoPresentacion.presentacion',
            'insumos.lote',
        ]);

        return view('atenciones_servicio.show', compact('atencionServicio'));
    }

    public function anularCreate(AtencionServicio $atencionServicio)
    {
        $atencionServicio->load([
            'servicio',
            'sucursal',
            'usuario',
            'cliente',
            'caja',
            'metodoPago',
            'insumos.producto',
            'insumos.lote',
        ]);

        if ($atencionServicio->estado !== 'completada') {
            return redirect()
                ->route('atenciones-servicio.show', $atencionServicio)
                ->with('error', 'Solo se pueden anular atenciones completadas.');
        }

        $cajaAbierta = Caja::where('sucursal_id', $atencionServicio->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $atencionServicio->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('atenciones-servicio.show', $atencionServicio)
                ->with('error', 'Solo se pueden anular atenciones de la caja abierta actual.');
        }

        return view('atenciones_servicio.anular', compact('atencionServicio'));
    }

    public function anularStore(Request $request, AtencionServicio $atencionServicio)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'min:5'],
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($atencionServicio->estado !== 'completada') {
            return redirect()
                ->route('atenciones-servicio.show', $atencionServicio)
                ->with('error', 'Solo se pueden anular atenciones completadas.');
        }

        $cajaAbierta = Caja::where('sucursal_id', $atencionServicio->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $atencionServicio->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('atenciones-servicio.show', $atencionServicio)
                ->with('error', 'Solo se pueden anular atenciones de la caja abierta actual.');
        }

        $user = auth()->user();

        DB::transaction(function () use ($atencionServicio, $datos, $user, $cajaAbierta) {
            $atencionServicio->load([
                'insumos',
                'metodoPago',
                'servicio',
            ]);

            foreach ($atencionServicio->insumos as $insumoUsado) {
                $inventario = Inventario::firstOrCreate(
                    [
                        'producto_id' => $insumoUsado->producto_id,
                        'sucursal_id' => $atencionServicio->sucursal_id,
                        'lote_id' => $insumoUsado->lote_id,
                    ],
                    [
                        'stock_actual' => 0,
                        'stock_minimo' => 5,
                        'estado' => 'activo',
                    ]
                );

                $stockAnterior = $inventario->stock_actual;
                $stockNuevo = $stockAnterior + $insumoUsado->unidades_descontadas;

                $inventario->update([
                    'stock_actual' => $stockNuevo,
                    'estado' => 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $insumoUsado->producto_id,
                    'sucursal_id' => $atencionServicio->sucursal_id,
                    'lote_id' => $insumoUsado->lote_id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $insumoUsado->unidades_descontadas,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => 'Devolución de insumo por anulación de servicio ' . $atencionServicio->servicio->nombre,
                    'referencia_tipo' => 'anulacion_atencion_servicio',
                    'referencia_id' => $atencionServicio->id,
                ]);
            }

            if ($atencionServicio->metodoPago?->tipo === 'efectivo') {
                $cajaAbierta->decrement('total_efectivo', $atencionServicio->total);
            } else {
                $cajaAbierta->decrement('total_qr', $atencionServicio->total);
            }

            $cajaAbierta->refresh();

            $cajaAbierta->update([
                'total_final' => round(
                    $cajaAbierta->monto_inicial
                    + $cajaAbierta->total_efectivo
                    + $cajaAbierta->total_qr
                    - $cajaAbierta->total_egresos
                    - $cajaAbierta->total_reembolsos,
                    2
                ),
            ]);

            MovimientoCaja::create([
                'caja_id' => $cajaAbierta->id,
                'usuario_id' => $user->id,
                'sucursal_id' => $atencionServicio->sucursal_id,
                'metodo_pago_id' => $atencionServicio->metodo_pago_id,
                'tipo_movimiento' => 'anulacion',
                'monto' => $atencionServicio->total,
                'descripcion' => 'Anulación de servicio ' . $atencionServicio->servicio->nombre . '. Motivo: ' . $datos['motivo_anulacion'],
                'autorizado_por' => null,
            ]);

            $atencionServicio->update([
                'estado' => 'anulada',
                'motivo_anulacion' => $datos['motivo_anulacion'],
                'fecha_anulacion' => now(),
            ]);
        });

        return redirect()
            ->route('atenciones-servicio.show', $atencionServicio)
            ->with('success', 'Atención de servicio anulada correctamente.');
    }
}