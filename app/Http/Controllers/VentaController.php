<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\MovimientoCaja;
use App\Models\DetalleVenta;
use App\Models\DetalleVentaLote;
use App\Models\Inventario;
use App\Models\MetodoPago;
use App\Models\MovimientoInventario;
use App\Models\PagoVenta;
use App\Models\ProductoPresentacion;
use App\Models\Promocion;
use App\Models\DetalleVentaPromocion;
use App\Models\DetalleVentaPromocionItem;
use Carbon\Carbon;
use App\Models\Recibo;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['usuario', 'sucursal', 'cliente', 'pagos.metodoPago'])
            ->latest('fecha_hora')
            ->paginate(15);

        return view('ventas.index', compact('ventas'));
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
                ->with('error', 'Debe abrir caja antes de registrar ventas.');
        }

        $productoPresentaciones = ProductoPresentacion::with(['producto', 'presentacion'])
            ->where('estado', 'activo')
            ->whereHas('producto', function ($query) {
                $query->where('estado', 'activo');
            })
            ->orderBy('nombre_mostrado')
            ->get();

        $metodosPago = MetodoPago::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $clientes = Cliente::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('productoPresentaciones', 'metodosPago', 'sucursal', 'cajaAbierta', 'clientes'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'items' => ['nullable', 'array'],
            'items.*.producto_presentacion_id' => ['required', 'exists:producto_presentaciones,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],

            'promociones' => ['nullable', 'array'],
            'promociones.*.promocion_id' => ['required', 'exists:promociones,id'],
            'promociones.*.cantidad' => ['required', 'integer', 'min:1'],
            'descuento_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'metodo_pago_id' => ['required', 'exists:metodos_pago,id'],
            'monto_recibido' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string'],
        ], [
            'items.required' => 'Debe agregar al menos un producto a la venta.',
            'items.min' => 'Debe agregar al menos un producto a la venta.',
            'metodo_pago_id.required' => 'Seleccione el método de pago.',
            'monto_recibido.required' => 'Ingrese el monto recibido.',
        ]);

        if (
            empty($datos['items']) &&
            empty($datos['promociones'])
        ) {
            return back()
                ->withErrors([
                    'items' => 'Debe agregar al menos un producto o promoción a la venta.',
                ])
                ->withInput();
        }

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return back()->withErrors([
                'sucursal' => 'El usuario no tiene una sucursal asignada.',
            ]);
        }

        $cajaAbierta = Caja::where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta) {
            return back()
                ->withErrors([
                    'caja' => 'Debe abrir caja antes de registrar una venta.',
                ])
                ->withInput();
        }

        $itemsProcesados = [];
        $totalVentaAntesDescuento = 0;

        $promocionesProcesadas = [];
        $hoy = Carbon::today();

        foreach (($datos['items'] ?? []) as $item) {
            $productoPresentacion = ProductoPresentacion::with('producto')
                ->where('estado', 'activo')
                ->whereHas('producto', function ($query) {
                    $query->where('estado', 'activo');
                })
                ->findOrFail($item['producto_presentacion_id']);

            $cantidad = (int) $item['cantidad'];
            $unidadesNecesarias = $cantidad * $productoPresentacion->unidades_equivalentes;
            $subtotal = round($cantidad * (float) $productoPresentacion->precio_venta, 2);

            $stockDisponible = Inventario::where('producto_id', $productoPresentacion->producto_id)
                ->where('sucursal_id', $sucursal->id)
                ->where('estado', 'activo')
                ->sum('stock_actual');

            if ($stockDisponible < $unidadesNecesarias) {
                return back()
                    ->withErrors([
                        'stock' => 'No hay stock suficiente para: ' . $productoPresentacion->producto->nombre_comercial,
                    ])
                    ->withInput();
            }

            $itemsProcesados[] = [
                'producto_presentacion' => $productoPresentacion,
                'cantidad' => $cantidad,
                'unidades_necesarias' => $unidadesNecesarias,
                'subtotal' => $subtotal,
            ];

            $totalVentaAntesDescuento += $subtotal;
        }

        foreach (($datos['promociones'] ?? []) as $itemPromo) {
            $promocion = Promocion::with([
                    'items.producto',
                    'items.productoPresentacion',
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
                ->findOrFail($itemPromo['promocion_id']);

            $cantidadPromo = (int) $itemPromo['cantidad'];

            if ($cantidadPromo < 1) {
                $cantidadPromo = 1;
            }

            foreach ($promocion->items as $promoItem) {
                if (
                    $promoItem->lote &&
                    $promoItem->lote->fecha_vencimiento &&
                    $promoItem->lote->fecha_vencimiento->lt($hoy)
                ) {
                    return back()
                        ->withErrors([
                            'promocion' => 'La promoción "' . $promocion->nombre . '" contiene un lote vencido.',
                        ])
                        ->withInput();
                }

                $unidadesNecesarias = $promoItem->unidades_necesarias * $cantidadPromo;

                $stockQuery = Inventario::where('producto_id', $promoItem->producto_id)
                    ->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'activo');

                if ($promoItem->lote_id) {
                    $stockQuery->where('lote_id', $promoItem->lote_id);
                }

                $stockDisponible = $stockQuery->sum('stock_actual');

                if ($stockDisponible < $unidadesNecesarias) {
                    return back()
                        ->withErrors([
                            'promocion' => 'No hay stock suficiente para la promoción: ' . $promocion->nombre,
                        ])
                        ->withInput();
                }
            }

            $subtotalPromo = round($cantidadPromo * (float) $promocion->precio_promocional, 2);

            $promocionesProcesadas[] = [
                'promocion' => $promocion,
                'cantidad' => $cantidadPromo,
                'subtotal' => $subtotalPromo,
            ];

            $totalVentaAntesDescuento += $subtotalPromo;
        }
        
        $subtotalVenta = round($totalVentaAntesDescuento, 2);

        $descuentoPorcentaje = (float) ($datos['descuento_porcentaje'] ?? 0);

        if (!$user->tienePermiso('aplicar_descuento')) {
            $descuentoPorcentaje = 0;
        }

        $descuentoTotal = round($subtotalVenta * ($descuentoPorcentaje / 100), 2);
        $totalVenta = round($subtotalVenta - $descuentoTotal, 2);

        $montoRecibido = round((float) $datos['monto_recibido'], 2);

        if ($montoRecibido < $totalVenta) {
            return back()
                ->withErrors(['monto_recibido' => 'El monto recibido no puede ser menor al total de la venta.'])
                ->withInput();
        }

        $cambio = round($montoRecibido - $totalVenta, 2);

        $venta = DB::transaction(function () use ($datos, $user, $sucursal, $cajaAbierta, $itemsProcesados, $promocionesProcesadas, $subtotalVenta, $descuentoTotal, $totalVenta, $montoRecibido, $cambio) {
            $numeroVenta = $this->generarNumeroVenta($sucursal->id);

            $venta = Venta::create([
                'numero_venta' => $numeroVenta,
                'usuario_id' => $user->id,
                'sucursal_id' => $sucursal->id,
                'cliente_id' => $datos['cliente_id'] ?? null,
                'caja_id' => $cajaAbierta->id,
                'fecha_hora' => now(),
                'subtotal' => $subtotalVenta,
                'descuento_total' => $descuentoTotal,
                'total' => $totalVenta,
                'monto_recibido' => $montoRecibido,
                'cambio' => $cambio,
                'estado' => 'completada',
                'observacion' => $datos['observacion'] ?? null,
            ]);

            foreach ($itemsProcesados as $item) {
                $productoPresentacion = $item['producto_presentacion'];
                $cantidadVendida = $item['cantidad'];
                $unidadesPendientes = $item['unidades_necesarias'];
                $subtotalItem = $item['subtotal'];

                $inventarios = Inventario::with('lote')
                    ->leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                    ->where('inventarios.producto_id', $productoPresentacion->producto_id)
                    ->where('inventarios.sucursal_id', $sucursal->id)
                    ->where('inventarios.estado', 'activo')
                    ->where('inventarios.stock_actual', '>', 0)
                    ->orderByRaw('lotes.fecha_vencimiento IS NULL')
                    ->orderBy('lotes.fecha_vencimiento')
                    ->select('inventarios.*')
                    ->get();

                $detalleVenta = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $productoPresentacion->producto_id,
                    'producto_presentacion_id' => $productoPresentacion->id,
                    'lote_id' => null,
                    'cantidad' => $cantidadVendida,
                    'unidades_descontadas' => $item['unidades_necesarias'],
                    'precio_unitario' => round((float) $productoPresentacion->precio_venta, 2),
                    'descuento' => 0,
                    'subtotal' => $subtotalItem,
                ]);

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

                    DetalleVentaLote::create([
                        'detalle_venta_id' => $detalleVenta->id,
                        'lote_id' => $inventario->lote_id,
                        'unidades_descontadas' => $descontar,
                    ]);

                    MovimientoInventario::create([
                        'producto_id' => $productoPresentacion->producto_id,
                        'sucursal_id' => $sucursal->id,
                        'lote_id' => $inventario->lote_id,
                        'usuario_id' => $user->id,
                        'tipo_movimiento' => 'salida',
                        'cantidad' => $descontar,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'Salida por venta ' . $venta->numero_venta,
                        'referencia_tipo' => 'venta',
                        'referencia_id' => $venta->id,
                    ]);

                    $unidadesPendientes -= $descontar;
                }
            }

            foreach ($promocionesProcesadas as $itemPromoProcesado) {
                $promocion = $itemPromoProcesado['promocion'];
                $cantidadPromo = $itemPromoProcesado['cantidad'];
                $subtotalPromo = $itemPromoProcesado['subtotal'];

                $detalleVentaPromocion = DetalleVentaPromocion::create([
                    'venta_id' => $venta->id,
                    'promocion_id' => $promocion->id,
                    'cantidad' => $cantidadPromo,
                    'precio_unitario' => round((float) $promocion->precio_promocional, 2),
                    'subtotal' => $subtotalPromo,
                ]);

                foreach ($promocion->items as $promoItem) {
                    $unidadesPendientes = $promoItem->unidades_necesarias * $cantidadPromo;

                    $inventariosQuery = Inventario::with('lote')
                        ->leftJoin('lotes', 'inventarios.lote_id', '=', 'lotes.id')
                        ->where('inventarios.producto_id', $promoItem->producto_id)
                        ->where('inventarios.sucursal_id', $sucursal->id)
                        ->where('inventarios.estado', 'activo')
                        ->where('inventarios.stock_actual', '>', 0);

                    if ($promoItem->lote_id) {
                        $inventariosQuery->where('inventarios.lote_id', $promoItem->lote_id);
                    }

                    $inventarios = $inventariosQuery
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

                        DetalleVentaPromocionItem::create([
                            'detalle_venta_promocion_id' => $detalleVentaPromocion->id,
                            'promocion_item_id' => $promoItem->id,
                            'producto_id' => $promoItem->producto_id,
                            'producto_presentacion_id' => $promoItem->producto_presentacion_id,
                            'lote_id' => $inventario->lote_id,
                            'unidades_descontadas' => $descontar,
                        ]);

                        MovimientoInventario::create([
                            'producto_id' => $promoItem->producto_id,
                            'sucursal_id' => $sucursal->id,
                            'lote_id' => $inventario->lote_id,
                            'usuario_id' => $user->id,
                            'tipo_movimiento' => 'salida',
                            'cantidad' => $descontar,
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo' => $stockNuevo,
                            'motivo' => 'Salida por promoción ' . $promocion->nombre . ' en venta ' . $venta->numero_venta,
                            'referencia_tipo' => 'venta_promocion',
                            'referencia_id' => $detalleVentaPromocion->id,
                        ]);

                        $unidadesPendientes -= $descontar;
                    }
                }
            }

            PagoVenta::create([
                'venta_id' => $venta->id,
                'metodo_pago_id' => $datos['metodo_pago_id'],
                'monto' => $totalVenta,
                'referencia' => null,
            ]);

            $metodoPago = MetodoPago::find($datos['metodo_pago_id']);

            if ($metodoPago?->tipo === 'efectivo') {
                $cajaAbierta->increment('total_efectivo', $totalVenta);
            } else {
                $cajaAbierta->increment('total_qr', $totalVenta);
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
                'venta_id' => $venta->id,
                'usuario_id' => $user->id,
                'sucursal_id' => $sucursal->id,
                'metodo_pago_id' => $datos['metodo_pago_id'],
                'tipo_movimiento' => 'ingreso',
                'monto' => $totalVenta,
                'descripcion' => 'Ingreso por venta ' . $venta->numero_venta,
                'autorizado_por' => null,
            ]);

            Recibo::create([
                'venta_id' => $venta->id,
                'numero_recibo' => 'R-' . $numeroVenta,
                'fecha_emision' => now(),
                'estado' => 'emitido',
            ]);

            return $venta;
        });

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta registrada correctamente.');
    }

    public function anularCreate(Venta $venta)
    {
        $venta->load([
            'usuario',
            'sucursal',
            'cliente',
            'detalles.producto',
            'detalles.productoPresentacion.presentacion',
            'detalles.lotesDescontados.lote',
            'pagos.metodoPago',
            'caja',
        ]);

        if ($venta->estado !== 'completada') {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden anular ventas completadas.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $venta->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $venta->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden anular ventas de la caja abierta actual.');
        }

        return view('ventas.anular', compact('venta'));
    }

    public function anularStore(Request $request, Venta $venta)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'min:5'],
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($venta->estado !== 'completada') {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden anular ventas completadas.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $venta->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $venta->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden anular ventas de la caja abierta actual.');
        }

        DB::transaction(function () use ($venta, $datos, $user, $cajaAbierta) {
            $venta->load([
                'detalles.lotesDescontados',
                'pagos.metodoPago',
            ]);

            foreach ($venta->detalles as $detalle) {
                foreach ($detalle->lotesDescontados as $loteDescontado) {
                    $inventario = Inventario::where('producto_id', $detalle->producto_id)
                        ->where('sucursal_id', $venta->sucursal_id)
                        ->where('lote_id', $loteDescontado->lote_id)
                        ->first();

                    if (!$inventario) {
                        $inventario = Inventario::create([
                            'producto_id' => $detalle->producto_id,
                            'sucursal_id' => $venta->sucursal_id,
                            'lote_id' => $loteDescontado->lote_id,
                            'stock_actual' => 0,
                            'stock_minimo' => 5,
                            'estado' => 'activo',
                        ]);
                    }

                    $stockAnterior = $inventario->stock_actual;
                    $stockNuevo = $stockAnterior + $loteDescontado->unidades_descontadas;

                    $inventario->update([
                        'stock_actual' => $stockNuevo,
                        'estado' => 'activo',
                    ]);

                    MovimientoInventario::create([
                        'producto_id' => $detalle->producto_id,
                        'sucursal_id' => $venta->sucursal_id,
                        'lote_id' => $loteDescontado->lote_id,
                        'usuario_id' => $user->id,
                        'tipo_movimiento' => 'entrada',
                        'cantidad' => $loteDescontado->unidades_descontadas,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'Devolución de stock por anulación de venta ' . $venta->numero_venta,
                        'referencia_tipo' => 'anulacion_venta',
                        'referencia_id' => $venta->id,
                    ]);
                }
            }

            foreach ($venta->pagos as $pago) {
                if ($pago->metodoPago?->tipo === 'efectivo') {
                    $cajaAbierta->decrement('total_efectivo', $pago->monto);
                } else {
                    $cajaAbierta->decrement('total_qr', $pago->monto);
                }

                MovimientoCaja::create([
                    'caja_id' => $cajaAbierta->id,
                    'venta_id' => $venta->id,
                    'usuario_id' => $user->id,
                    'sucursal_id' => $venta->sucursal_id,
                    'metodo_pago_id' => $pago->metodo_pago_id,
                    'tipo_movimiento' => 'anulacion',
                    'monto' => $pago->monto,
                    'descripcion' => 'Anulación de venta ' . $venta->numero_venta . '. Motivo: ' . $datos['motivo_anulacion'],
                    'autorizado_por' => null,
                ]);
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

            $venta->update([
                'estado' => 'anulada',
                'observacion' => trim(($venta->observacion ? $venta->observacion . "\n" : '') . 'ANULADA: ' . $datos['motivo_anulacion']),
            ]);
        });

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta anulada correctamente. El stock y la caja fueron actualizados.');
    }

    public function show(Venta $venta)
    {
        $venta->load([
            'usuario',
            'sucursal',
            'cliente',
            'caja',
            'detalles.producto',
            'detalles.producto.laboratorio',
            'detalles.productoPresentacion.presentacion',
            'detalles.lotesDescontados.lote',
            'detalles.reembolsos.reembolso',
            'detalles.cambiosProductoDevueltos.cambioProducto',
            'pagos.metodoPago',
            'recibo',

            'promociones.promocion',
            'promociones.items.producto.laboratorio',
            'promociones.items.productoPresentacion.presentacion',
            'promociones.items.lote',

            'reembolsos.usuario',
            'reembolsos.detalles.producto',
            'reembolsos.detalles.lote',

            'cambiosProducto.usuario',
            'cambiosProducto.detalles.productoDevuelto',
            'cambiosProducto.detalles.productoNuevo',
            'cambiosProducto.detalles.productoPresentacionDevuelta.presentacion',
            'cambiosProducto.detalles.productoPresentacionNueva.presentacion',
            'cambiosProducto.detalles.loteDevuelto',
            'cambiosProducto.detalles.loteNuevo',
        ]);

        return view('ventas.show', compact('venta'));
    }

    private function generarNumeroVenta(int $sucursalId): string
    {
        $correlativo = Venta::where('sucursal_id', $sucursalId)->count() + 1;

        return 'SR-' . str_pad($sucursalId, 2, '0', STR_PAD_LEFT) . '-' . str_pad($correlativo, 6, '0', STR_PAD_LEFT);
    }
}
