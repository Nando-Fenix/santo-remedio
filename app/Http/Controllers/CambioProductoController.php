<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CambioProducto;
use App\Models\DetalleCambioProducto;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\ProductoPresentacion;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CambioProductoController extends Controller
{
    public function create(Venta $venta)
    {
        $venta->load([
            'usuario',
            'sucursal',
            'cliente',
            'caja',
            'detalles.producto',
            'detalles.productoPresentacion.presentacion',
            'detalles.lotesDescontados.lote',
            'detalles.reembolsos.reembolso',
            'detalles.cambiosProductoDevueltos.cambioProducto',
            'pagos.metodoPago',
            'cambiosProducto.detalles',
        ]);

        if ($venta->estado !== 'completada') {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar cambios en ventas completadas.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $venta->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $venta->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar cambios de ventas de la caja abierta actual.');
        }

        return view('cambios_producto.create', compact('venta'));
    }

    /**
     * Busca productos disponibles para entregar como producto nuevo en un cambio.
     *
     * Reglas:
     * - Solo muestra productos activos.
     * - Solo muestra presentaciones activas.
     * - Solo devuelve productos con stock disponible en la sucursal actual.
     * - Permite buscar por nombre comercial, nombre genérico, concentración o código de barras.
     */
    public function buscarProductos(Request $request)
    {
        $termino = trim($request->get('busqueda', $request->get('q', '')));

        if ($termino === '') {
            return response()->json([]);
        }

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return response()->json([]);
        }

        $productos = ProductoPresentacion::with([
                'producto.codigosBarras',
                'presentacion',
            ])
            ->where('estado', 'activo')
            ->whereHas('producto', function ($query) use ($termino) {
                $query->where('estado', 'activo')
                    ->where(function ($subQuery) use ($termino) {
                        $subQuery->where('nombre_comercial', 'like', "%{$termino}%")
                            ->orWhere('nombre_generico', 'like', "%{$termino}%")
                            ->orWhere('concentracion', 'like', "%{$termino}%")
                            ->orWhereHas('codigosBarras', function ($codigoQuery) use ($termino) {
                                $codigoQuery->where('codigo', $termino)
                                    ->where('estado', 'activo');
                            });
                    });
            })
            ->limit(12)
            ->get()
            ->map(function ($presentacion) use ($sucursal) {
                $stockDisponible = Inventario::where('producto_id', $presentacion->producto_id)
                    ->where('sucursal_id', $sucursal->id)
                    ->where('stock_actual', '>', 0)
                    ->sum('stock_actual');

                return [
                    'producto_presentacion_id' => $presentacion->id,
                    'producto_id' => $presentacion->producto_id,
                    'nombre_producto' => $presentacion->producto->nombre_comercial,
                    'nombre_generico' => $presentacion->producto->nombre_generico,
                    'concentracion' => $presentacion->producto->concentracion,
                    'presentacion' => $presentacion->nombre_mostrado,
                    'unidades_equivalentes' => $presentacion->unidades_equivalentes,
                    'precio_venta' => (float) $presentacion->precio_venta,
                    'stock_disponible' => (int) $stockDisponible,
                ];
            })
            ->filter(fn ($producto) => $producto['stock_disponible'] > 0)
            ->values();

        return response()->json($productos);
    }

    /**
     * Registra un cambio de producto.
     *
     * Reglas:
     * - La venta original no se elimina.
     * - El producto devuelto vuelve al inventario.
     * - El producto nuevo descuenta inventario usando FEFO.
     * - Si el producto nuevo cuesta más, el cliente paga diferencia.
     * - Si el producto nuevo cuesta menos, la farmacia devuelve diferencia.
     * - Se registran movimientos de inventario y caja para trazabilidad.
     */
    public function store(Request $request, Venta $venta)
    {
        $datos = $request->validate([
            'detalle_venta_devuelto_id' => ['required', 'exists:detalle_ventas,id'],
            'cantidad_devuelta' => ['required', 'integer', 'min:1'],

            'producto_presentacion_nueva_id' => ['required', 'exists:producto_presentaciones,id'],
            'cantidad_nueva' => ['required', 'integer', 'min:1'],

            'motivo' => ['required', 'string', 'min:5'],
        ], [
            'detalle_venta_devuelto_id.required' => 'Debe seleccionar el producto que será devuelto.',
            'cantidad_devuelta.required' => 'Debe ingresar la cantidad devuelta.',
            'producto_presentacion_nueva_id.required' => 'Debe seleccionar el producto nuevo.',
            'cantidad_nueva.required' => 'Debe ingresar la cantidad nueva.',
            'motivo.required' => 'Debe ingresar el motivo del cambio.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($venta->estado !== 'completada') {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar cambios en ventas completadas.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $venta->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $venta->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar cambios de ventas de la caja abierta actual.');
        }

        $detalleDevuelto = DetalleVenta::with([
                'producto',
                'productoPresentacion',
                'lotesDescontados.lote',
                'reembolsos.reembolso',
                'cambiosProductoDevueltos.cambioProducto',
            ])
            ->where('venta_id', $venta->id)
            ->findOrFail($datos['detalle_venta_devuelto_id']);

        $cantidadDevuelta = (int) $datos['cantidad_devuelta'];

        $cantidadYaReembolsada = $detalleDevuelto->reembolsos()
            ->whereHas('reembolso', function ($query) {
                $query->where('estado', 'registrado');
            })
            ->sum('cantidad_devuelta');

        $cantidadYaCambiada = $detalleDevuelto->cambiosProductoDevueltos()
            ->whereHas('cambioProducto', function ($query) {
                $query->where('estado', 'registrado');
            })
            ->sum('cantidad_devuelta');

        $cantidadDisponibleParaDevolver = $detalleDevuelto->cantidad - $cantidadYaReembolsada - $cantidadYaCambiada;

        if ($cantidadDevuelta > $cantidadDisponibleParaDevolver) {
            return back()
                ->withErrors([
                    'cantidad_devuelta' => 'La cantidad devuelta supera lo disponible para cambiar.',
                ])
                ->withInput();
        }

        $presentacionNueva = ProductoPresentacion::with('producto')
            ->where('estado', 'activo')
            ->findOrFail($datos['producto_presentacion_nueva_id']);

        $cantidadNueva = (int) $datos['cantidad_nueva'];

        $unidadesDevueltas = $cantidadDevuelta * $detalleDevuelto->productoPresentacion->unidades_equivalentes;
        $unidadesNuevas = $cantidadNueva * $presentacionNueva->unidades_equivalentes;

        $montoDevuelto = round($cantidadDevuelta * (float) $detalleDevuelto->precio_unitario, 2);
        $montoNuevo = round($cantidadNueva * (float) $presentacionNueva->precio_venta, 2);

        $diferencia = round(abs($montoNuevo - $montoDevuelto), 2);

        if ($montoNuevo > $montoDevuelto) {
            $tipoDiferencia = 'cliente_paga';
        } elseif ($montoNuevo < $montoDevuelto) {
            $tipoDiferencia = 'farmacia_devuelve';
        } else {
            $tipoDiferencia = 'sin_diferencia';
        }

        $stockDisponibleNuevo = Inventario::where('producto_id', $presentacionNueva->producto_id)
            ->where('sucursal_id', $venta->sucursal_id)
            ->where('stock_actual', '>', 0)
            ->sum('stock_actual');

        if ($stockDisponibleNuevo < $unidadesNuevas) {
            return back()
                ->withErrors([
                    'producto_nuevo' => 'No hay stock suficiente del producto nuevo seleccionado.',
                ])
                ->withInput();
        }

        $cambioProducto = DB::transaction(function () use (
            $venta,
            $datos,
            $user,
            $cajaAbierta,
            $detalleDevuelto,
            $presentacionNueva,
            $cantidadDevuelta,
            $cantidadNueva,
            $unidadesDevueltas,
            $unidadesNuevas,
            $montoDevuelto,
            $montoNuevo,
            $diferencia,
            $tipoDiferencia
        ) {
            $numeroCambio = $this->generarNumeroCambio();

            $cambioProducto = CambioProducto::create([
                'venta_id' => $venta->id,
                'usuario_id' => $user->id,
                'sucursal_id' => $venta->sucursal_id,
                'caja_id' => $cajaAbierta->id,
                'numero_cambio' => $numeroCambio,
                'fecha_cambio' => now(),
                'monto_devuelto' => $montoDevuelto,
                'monto_nuevo' => $montoNuevo,
                'diferencia' => $diferencia,
                'tipo_diferencia' => $tipoDiferencia,
                'motivo' => $datos['motivo'],
                'estado' => 'registrado',
            ]);

            /*
             * 1. Devolvemos al inventario el producto que el cliente entrega.
             * Se devuelve a los mismos lotes que fueron descontados en la venta original.
             */
            $unidadesPendientesDevueltas = $unidadesDevueltas;
            $primerLoteDevueltoId = null;

            foreach ($detalleDevuelto->lotesDescontados as $loteDescontado) {
                if ($unidadesPendientesDevueltas <= 0) {
                    break;
                }

                $unidadesADevolver = min($loteDescontado->unidades_descontadas, $unidadesPendientesDevueltas);

                if (!$primerLoteDevueltoId) {
                    $primerLoteDevueltoId = $loteDescontado->lote_id;
                }

                $inventario = Inventario::firstOrCreate(
                    [
                        'producto_id' => $detalleDevuelto->producto_id,
                        'sucursal_id' => $venta->sucursal_id,
                        'lote_id' => $loteDescontado->lote_id,
                    ],
                    [
                        'stock_actual' => 0,
                        'stock_minimo' => 5,
                        'estado' => 'activo',
                    ]
                );

                $stockAnterior = $inventario->stock_actual;
                $stockNuevo = $stockAnterior + $unidadesADevolver;

                $inventario->update([
                    'stock_actual' => $stockNuevo,
                    'estado' => 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $detalleDevuelto->producto_id,
                    'sucursal_id' => $venta->sucursal_id,
                    'lote_id' => $loteDescontado->lote_id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $unidadesADevolver,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => 'Entrada por cambio ' . $cambioProducto->numero_cambio . ' de venta ' . $venta->numero_venta,
                    'referencia_tipo' => 'cambio_producto',
                    'referencia_id' => $cambioProducto->id,
                ]);

                $unidadesPendientesDevueltas -= $unidadesADevolver;
            }

            /*
             * 2. Descontamos del inventario el producto nuevo que se entrega.
             * Se usa FEFO: primero el lote con vencimiento más próximo.
             */
            $inventariosNuevoProducto = Inventario::with('lote')
                ->where('inventarios.producto_id', $presentacionNueva->producto_id)
                ->where('inventarios.sucursal_id', $venta->sucursal_id)
                ->where('inventarios.stock_actual', '>', 0)
                ->leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                ->orderByRaw('lotes.fecha_vencimiento IS NULL')
                ->orderBy('lotes.fecha_vencimiento')
                ->select('inventarios.*')
                ->get();

            $unidadesPendientesNuevas = $unidadesNuevas;
            $primerLoteNuevoId = null;

            foreach ($inventariosNuevoProducto as $inventario) {
                if ($unidadesPendientesNuevas <= 0) {
                    break;
                }

                $cantidadADescontar = min($inventario->stock_actual, $unidadesPendientesNuevas);

                if (!$primerLoteNuevoId) {
                    $primerLoteNuevoId = $inventario->lote_id;
                }

                $stockAnterior = $inventario->stock_actual;
                $stockNuevo = $stockAnterior - $cantidadADescontar;

                $inventario->update([
                    'stock_actual' => $stockNuevo,
                    'estado' => $stockNuevo <= 0 ? 'agotado' : 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $presentacionNueva->producto_id,
                    'sucursal_id' => $venta->sucursal_id,
                    'lote_id' => $inventario->lote_id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $cantidadADescontar,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => 'Salida por cambio ' . $cambioProducto->numero_cambio . ' de venta ' . $venta->numero_venta,
                    'referencia_tipo' => 'cambio_producto',
                    'referencia_id' => $cambioProducto->id,
                ]);

                $unidadesPendientesNuevas -= $cantidadADescontar;
            }

            DetalleCambioProducto::create([
                'cambio_producto_id' => $cambioProducto->id,
                'detalle_venta_devuelto_id' => $detalleDevuelto->id,

                'producto_devuelto_id' => $detalleDevuelto->producto_id,
                'producto_presentacion_devuelta_id' => $detalleDevuelto->producto_presentacion_id,
                'lote_devuelto_id' => $primerLoteDevueltoId,
                'cantidad_devuelta' => $cantidadDevuelta,
                'unidades_devueltas' => $unidadesDevueltas,
                'monto_devuelto' => $montoDevuelto,

                'producto_nuevo_id' => $presentacionNueva->producto_id,
                'producto_presentacion_nueva_id' => $presentacionNueva->id,
                'lote_nuevo_id' => $primerLoteNuevoId,
                'cantidad_nueva' => $cantidadNueva,
                'unidades_nuevas' => $unidadesNuevas,
                'precio_unitario_nuevo' => round((float) $presentacionNueva->precio_venta, 2),
                'monto_nuevo' => $montoNuevo,
            ]);

            /*
             * 3. Ajustamos caja solamente si existe diferencia.
             * - cliente_paga: entra dinero a caja.
             * - farmacia_devuelve: sale dinero de caja.
             */
            if ($diferencia > 0) {
                $metodoPagoPrincipal = $venta->pagos()->with('metodoPago')->first();
                $tipoPago = $metodoPagoPrincipal?->metodoPago?->tipo ?? 'efectivo';

                if ($tipoDiferencia === 'cliente_paga') {
                    if ($tipoPago === 'efectivo') {
                        $cajaAbierta->increment('total_efectivo', $diferencia);
                    } else {
                        $cajaAbierta->increment('total_qr', $diferencia);
                    }

                    MovimientoCaja::create([
                        'caja_id' => $cajaAbierta->id,
                        'venta_id' => $venta->id,
                        'usuario_id' => $user->id,
                        'sucursal_id' => $venta->sucursal_id,
                        'metodo_pago_id' => $metodoPagoPrincipal?->metodo_pago_id,
                        'tipo_movimiento' => 'venta',
                        'monto' => $diferencia,
                        'descripcion' => 'Diferencia pagada por cliente en cambio ' . $cambioProducto->numero_cambio,
                        'autorizado_por' => null,
                    ]);
                }

                if ($tipoDiferencia === 'farmacia_devuelve') {
                    if ($tipoPago === 'efectivo') {
                        $cajaAbierta->decrement('total_efectivo', $diferencia);
                    } else {
                        $cajaAbierta->decrement('total_qr', $diferencia);
                    }

                    $cajaAbierta->increment('total_reembolsos', $diferencia);

                    MovimientoCaja::create([
                        'caja_id' => $cajaAbierta->id,
                        'venta_id' => $venta->id,
                        'usuario_id' => $user->id,
                        'sucursal_id' => $venta->sucursal_id,
                        'metodo_pago_id' => $metodoPagoPrincipal?->metodo_pago_id,
                        'tipo_movimiento' => 'egreso',
                        'monto' => $diferencia,
                        'descripcion' => 'Diferencia devuelta al cliente en cambio ' . $cambioProducto->numero_cambio,
                        'autorizado_por' => null,
                    ]);
                }

                $cajaAbierta->refresh();

                $cajaAbierta->update([
                    'total_final' => $cajaAbierta->monto_inicial
                        + $cajaAbierta->total_efectivo
                        - $cajaAbierta->total_egresos
                        - $cajaAbierta->total_reembolsos,
                ]);
            }

            return $cambioProducto;
        });

        return redirect()
            ->route('cambios-producto.show', $cambioProducto)
            ->with('success', 'Cambio de producto registrado correctamente.');
    }
    
    public function anularCreate(CambioProducto $cambioProducto)
    {
        $cambioProducto->load([
            'venta',
            'usuario',
            'sucursal',
            'caja',
            'detalles.productoDevuelto',
            'detalles.productoPresentacionDevuelta.presentacion',
            'detalles.loteDevuelto',
            'detalles.productoNuevo',
            'detalles.productoPresentacionNueva.presentacion',
            'detalles.loteNuevo',
        ]);

        if ($cambioProducto->estado !== 'registrado') {
            return redirect()
                ->route('cambios-producto.show', $cambioProducto)
                ->with('error', 'Solo se pueden anular cambios registrados.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $cambioProducto->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $cambioProducto->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('cambios-producto.show', $cambioProducto)
                ->with('error', 'Solo se pueden anular cambios de la caja abierta actual.');
        }

        return view('cambios_producto.anular', compact('cambioProducto'));
    }

    /**
     * Anula un cambio de producto.
     *
     * Reglas:
     * - Solo anula cambios registrados de la caja abierta actual.
     * - Revierte inventario:
     *   - El producto devuelto en el cambio se descuenta nuevamente.
     *   - El producto nuevo entregado en el cambio vuelve al inventario.
     * - Revierte la diferencia de caja si existió.
     * - No elimina el cambio, solo lo marca como anulado.
     */
    public function anularStore(Request $request, CambioProducto $cambioProducto)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'min:5'],
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($cambioProducto->estado !== 'registrado') {
            return redirect()
                ->route('cambios-producto.show', $cambioProducto)
                ->with('error', 'Solo se pueden anular cambios registrados.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $cambioProducto->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $cambioProducto->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('cambios-producto.show', $cambioProducto)
                ->with('error', 'Solo se pueden anular cambios de la caja abierta actual.');
        }

        $cambioProducto->load([
            'venta.pagos.metodoPago',
            'detalles.productoDevuelto',
            'detalles.productoNuevo',
        ]);

        /*
        * Validación previa:
        * Para anular el cambio debemos restar del inventario el producto que había sido devuelto.
        * Si ese stock ya fue vendido o movido, no se puede anular con seguridad.
        */
        foreach ($cambioProducto->detalles as $detalle) {
            $inventarioDevuelto = Inventario::where('producto_id', $detalle->producto_devuelto_id)
                ->where('sucursal_id', $cambioProducto->sucursal_id)
                ->where('lote_id', $detalle->lote_devuelto_id)
                ->first();

            if (!$inventarioDevuelto || $inventarioDevuelto->stock_actual < $detalle->unidades_devueltas) {
                return redirect()
                    ->route('cambios-producto.show', $cambioProducto)
                    ->with(
                        'error',
                        'No se puede anular el cambio porque el producto devuelto ya fue vendido o movido. Producto: ' .
                        ($detalle->productoDevuelto->nombre_comercial ?? 'Sin nombre')
                    );
            }
        }

        DB::transaction(function () use ($cambioProducto, $datos, $user, $cajaAbierta) {
            $cambioProducto->load([
                'venta.pagos.metodoPago',
                'detalles.productoDevuelto',
                'detalles.productoNuevo',
            ]);

            foreach ($cambioProducto->detalles as $detalle) {
                /*
                * 1. Restar nuevamente el producto que el cliente había devuelto.
                * Esto revierte la entrada generada por el cambio.
                */
                $inventarioDevuelto = Inventario::where('producto_id', $detalle->producto_devuelto_id)
                    ->where('sucursal_id', $cambioProducto->sucursal_id)
                    ->where('lote_id', $detalle->lote_devuelto_id)
                    ->first();

                $stockAnteriorDevuelto = $inventarioDevuelto->stock_actual;
                $stockNuevoDevuelto = $stockAnteriorDevuelto - $detalle->unidades_devueltas;

                $inventarioDevuelto->update([
                    'stock_actual' => $stockNuevoDevuelto,
                    'estado' => $stockNuevoDevuelto <= 0 ? 'agotado' : 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_devuelto_id,
                    'sucursal_id' => $cambioProducto->sucursal_id,
                    'lote_id' => $detalle->lote_devuelto_id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $detalle->unidades_devueltas,
                    'stock_anterior' => $stockAnteriorDevuelto,
                    'stock_nuevo' => $stockNuevoDevuelto,
                    'motivo' => 'Salida por anulación del cambio ' . $cambioProducto->numero_cambio . '. Motivo: ' . $datos['motivo_anulacion'],
                    'referencia_tipo' => 'anulacion_cambio_producto',
                    'referencia_id' => $cambioProducto->id,
                ]);

                /*
                * 2. Devolver al inventario el producto nuevo que fue entregado.
                * Esto revierte la salida generada por el cambio.
                */
                $inventarioNuevo = Inventario::firstOrCreate(
                    [
                        'producto_id' => $detalle->producto_nuevo_id,
                        'sucursal_id' => $cambioProducto->sucursal_id,
                        'lote_id' => $detalle->lote_nuevo_id,
                    ],
                    [
                        'stock_actual' => 0,
                        'stock_minimo' => 5,
                        'estado' => 'activo',
                    ]
                );

                $stockAnteriorNuevo = $inventarioNuevo->stock_actual;
                $stockNuevoNuevo = $stockAnteriorNuevo + $detalle->unidades_nuevas;

                $inventarioNuevo->update([
                    'stock_actual' => $stockNuevoNuevo,
                    'estado' => 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_nuevo_id,
                    'sucursal_id' => $cambioProducto->sucursal_id,
                    'lote_id' => $detalle->lote_nuevo_id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $detalle->unidades_nuevas,
                    'stock_anterior' => $stockAnteriorNuevo,
                    'stock_nuevo' => $stockNuevoNuevo,
                    'motivo' => 'Entrada por anulación del cambio ' . $cambioProducto->numero_cambio . '. Motivo: ' . $datos['motivo_anulacion'],
                    'referencia_tipo' => 'anulacion_cambio_producto',
                    'referencia_id' => $cambioProducto->id,
                ]);
            }

            /*
            * 3. Revertir diferencia de caja.
            */
            if ((float) $cambioProducto->diferencia > 0) {
                $metodoPagoPrincipal = $cambioProducto->venta->pagos()->with('metodoPago')->first();
                $tipoPago = $metodoPagoPrincipal?->metodoPago?->tipo ?? 'efectivo';
                $diferencia = round((float) $cambioProducto->diferencia, 2);

                if ($cambioProducto->tipo_diferencia === 'cliente_paga') {
                    /*
                    * En el cambio original el cliente pagó dinero.
                    * Al anular, se retira ese dinero de la caja.
                    */
                    if ($tipoPago === 'efectivo') {
                        $cajaAbierta->decrement('total_efectivo', $diferencia);
                    } else {
                        $cajaAbierta->decrement('total_qr', $diferencia);
                    }

                    $cajaAbierta->increment('total_reembolsos', $diferencia);

                    MovimientoCaja::create([
                        'caja_id' => $cajaAbierta->id,
                        'venta_id' => $cambioProducto->venta_id,
                        'usuario_id' => $user->id,
                        'sucursal_id' => $cambioProducto->sucursal_id,
                        'metodo_pago_id' => $metodoPagoPrincipal?->metodo_pago_id,
                        'tipo_movimiento' => 'egreso',
                        'monto' => $diferencia,
                        'descripcion' => 'Anulación de diferencia pagada por cliente en cambio ' . $cambioProducto->numero_cambio,
                        'autorizado_por' => null,
                    ]);
                }

                if ($cambioProducto->tipo_diferencia === 'farmacia_devuelve') {
                    /*
                    * En el cambio original la farmacia devolvió dinero.
                    * Al anular, ese dinero vuelve a contabilizarse en caja.
                    */
                    if ($tipoPago === 'efectivo') {
                        $cajaAbierta->increment('total_efectivo', $diferencia);
                    } else {
                        $cajaAbierta->increment('total_qr', $diferencia);
                    }

                    $cajaAbierta->decrement('total_reembolsos', $diferencia);

                    MovimientoCaja::create([
                        'caja_id' => $cajaAbierta->id,
                        'venta_id' => $cambioProducto->venta_id,
                        'usuario_id' => $user->id,
                        'sucursal_id' => $cambioProducto->sucursal_id,
                        'metodo_pago_id' => $metodoPagoPrincipal?->metodo_pago_id,
                        'tipo_movimiento' => 'entrada',
                        'monto' => $diferencia,
                        'descripcion' => 'Anulación de diferencia devuelta por farmacia en cambio ' . $cambioProducto->numero_cambio,
                        'autorizado_por' => null,
                    ]);
                }

                $cajaAbierta->refresh();

                $cajaAbierta->update([
                    'total_final' => round(
                        $cajaAbierta->monto_inicial
                        + $cajaAbierta->total_efectivo
                        - $cajaAbierta->total_egresos
                        - $cajaAbierta->total_reembolsos,
                        2
                    ),
                ]);
            }

            $cambioProducto->update([
                'estado' => 'anulado',
                'usuario_anulacion_id' => $user->id,
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $datos['motivo_anulacion'],
            ]);
        });

        return redirect()
            ->route('cambios-producto.show', $cambioProducto)
            ->with('success', 'Cambio de producto anulado correctamente. Inventario y caja fueron revertidos.');
    }

    public function show(CambioProducto $cambioProducto)
    {
        $cambioProducto->load([
            'venta',
            'usuario',
            'usuarioAnulacion',
            'sucursal',
            'caja',
            'detalles.productoDevuelto',
            'detalles.productoPresentacionDevuelta.presentacion',
            'detalles.loteDevuelto',
            'detalles.productoNuevo',
            'detalles.productoPresentacionNueva.presentacion',
            'detalles.loteNuevo',
        ]);

        return view('cambios_producto.show', compact('cambioProducto'));
    }

    private function generarNumeroCambio(): string
    {
        $ultimoCambio = CambioProducto::latest('id')->first();
        $numero = $ultimoCambio ? $ultimoCambio->id + 1 : 1;

        return 'CP-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}
