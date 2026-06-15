<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\PagoCompra;
use App\Models\ProductoPresentacion;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
     public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $estadoPago = $request->get('estado_pago');

        $compras = Compra::with(['proveedor', 'sucursal', 'usuario'])
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('numero_compra', 'like', "%{$busqueda}%")
                        ->orWhereHas('proveedor', function ($p) use ($busqueda) {
                            $p->where('nombre', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when($estadoPago, function ($query) use ($estadoPago) {
                $query->where('estado_pago', $estadoPago);
            })
            ->latest('fecha_compra')
            ->paginate(10)
            ->withQueryString();

        return view('compras.index', compact('compras', 'busqueda', 'estadoPago'));
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

        $proveedores = Proveedor::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $productoPresentaciones = ProductoPresentacion::with(['producto', 'presentacion'])
            ->where('estado', 'activo')
            ->whereHas('producto', function ($query) {
                $query->where('estado', 'activo');
            })
            ->orderBy('nombre_mostrado')
            ->get();

        return view('compras.create', compact(
            'sucursal',
            'proveedores',
            'productoPresentaciones'
        ));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'tipo_pago' => ['required', 'in:contado,credito'],
            'monto_pagado' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_presentacion_id' => ['required', 'exists:producto_presentaciones,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_compra' => ['required', 'numeric', 'min:0'],
            'items.*.numero_lote' => ['nullable', 'string', 'max:100'],
            'items.*.fecha_vencimiento' => ['nullable', 'date'],
        ], [
            'proveedor_id.required' => 'Seleccione un proveedor.',
            'items.required' => 'Debe agregar al menos un producto a la compra.',
            'items.min' => 'Debe agregar al menos un producto a la compra.',
        ]);

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return back()
                ->withErrors(['sucursal' => 'El usuario no tiene una sucursal asignada.'])
                ->withInput();
        }

        $itemsProcesados = [];
        $subtotalCompra = 0;

        foreach ($datos['items'] as $item) {
            $productoPresentacion = ProductoPresentacion::with('producto')
                ->findOrFail($item['producto_presentacion_id']);

            $cantidad = (int) $item['cantidad'];
            $precioCompra = (float) $item['precio_compra'];
            $unidadesIngresadas = $cantidad * $productoPresentacion->unidades_equivalentes;
            $subtotalItem = $cantidad * $precioCompra;

            $itemsProcesados[] = [
                'producto_presentacion' => $productoPresentacion,
                'cantidad' => $cantidad,
                'precio_compra' => $precioCompra,
                'unidades_ingresadas' => $unidadesIngresadas,
                'subtotal' => $subtotalItem,
                'numero_lote' => $item['numero_lote'] ?? null,
                'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
            ];

            $subtotalCompra += $subtotalItem;
        }

        $subtotalCompra = round($subtotalCompra, 2);
        $descuentoTotal = round(0, 2);
        $totalCompra = round($subtotalCompra - $descuentoTotal, 2);
        $montoPagado = round(min((float) $datos['monto_pagado'], $totalCompra), 2);
        $saldoPendiente = round($totalCompra - $montoPagado, 2);

        if ($saldoPendiente <= 0) {
            $estadoPago = 'pagado';
        } elseif ($montoPagado > 0) {
            $estadoPago = 'parcial';
        } else {
            $estadoPago = 'pendiente';
        }

        $compra = DB::transaction(function () use (
            $datos,
            $user,
            $sucursal,
            $itemsProcesados,
            $subtotalCompra,
            $descuentoTotal,
            $totalCompra,
            $montoPagado,
            $saldoPendiente,
            $estadoPago
        ) {
            $numeroCompra = $this->generarNumeroCompra();

            $compra = Compra::create([
                'proveedor_id' => $datos['proveedor_id'],
                'sucursal_id' => $sucursal->id,
                'usuario_id' => $user->id,
                'numero_compra' => $numeroCompra,
                'fecha_compra' => now(),
                'subtotal' => $subtotalCompra,
                'descuento_total' => $descuentoTotal,
                'total' => $totalCompra,
                'monto_pagado' => $montoPagado,
                'saldo_pendiente' => $saldoPendiente,
                'tipo_pago' => $datos['tipo_pago'],
                'estado_pago' => $estadoPago,
                'estado' => 'registrada',
                'observacion' => $datos['observacion'] ?? null,
            ]);

            foreach ($itemsProcesados as $item) {
                $productoPresentacion = $item['producto_presentacion'];

                $numeroLote = $item['numero_lote'];

                if (!$numeroLote) {
                    $numeroLote = $this->generarNumeroLoteInterno();
                }

                $lote = Lote::firstOrCreate(
                    [
                        'producto_id' => $productoPresentacion->producto_id,
                        'numero_lote' => $numeroLote,
                        'fecha_vencimiento' => $item['fecha_vencimiento'],
                    ],
                    [
                        'estado' => 'activo',
                    ]
                );

                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $productoPresentacion->producto_id,
                    'producto_presentacion_id' => $productoPresentacion->id,
                    'lote_id' => $lote->id,
                    'cantidad' => $item['cantidad'],
                    'unidades_ingresadas' => $item['unidades_ingresadas'],
                    'precio_compra' => $item['precio_compra'],
                    'subtotal' => $item['subtotal'],
                ]);

                $inventario = Inventario::firstOrCreate(
                    [
                        'producto_id' => $productoPresentacion->producto_id,
                        'sucursal_id' => $sucursal->id,
                        'lote_id' => $lote->id,
                    ],
                    [
                        'stock_actual' => 0,
                        'stock_minimo' => 5,
                        'estado' => 'activo',
                    ]
                );

                $stockAnterior = $inventario->stock_actual;
                $stockNuevo = $stockAnterior + $item['unidades_ingresadas'];

                $inventario->update([
                    'stock_actual' => $stockNuevo,
                    'estado' => 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $productoPresentacion->producto_id,
                    'sucursal_id' => $sucursal->id,
                    'lote_id' => $lote->id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $item['unidades_ingresadas'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => 'Entrada por compra ' . $compra->numero_compra,
                    'referencia_tipo' => 'compra',
                    'referencia_id' => $compra->id,
                ]);
            }

            if ($montoPagado > 0) {
                PagoCompra::create([
                    'compra_id' => $compra->id,
                    'usuario_id' => $user->id,
                    'fecha_pago' => now(),
                    'monto' => $montoPagado,
                    'metodo_pago' => 'efectivo',
                    'referencia' => null,
                    'observacion' => 'Pago registrado al momento de la compra.',
                ]);
            }

            return $compra;
        });

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Compra registrada correctamente e inventario actualizado.');
    }

    public function buscarProductos(Request $request)
    {
        $termino = trim($request->get('busqueda', ''));

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $productos = ProductoPresentacion::with(['producto', 'presentacion', 'codigosBarras'])
            ->where('estado', 'activo')
            ->whereHas('producto', function ($query) {
                $query->where('estado', 'activo');
            })
            ->where(function ($query) use ($termino) {
                $query->where('nombre_mostrado', 'like', "%{$termino}%")
                    ->orWhereHas('producto', function ($q) use ($termino) {
                        $q->where('nombre_comercial', 'like', "%{$termino}%")
                            ->orWhere('nombre_generico', 'like', "%{$termino}%")
                            ->orWhere('concentracion', 'like', "%{$termino}%");
                    })
                    ->orWhereHas('codigosBarras', function ($q) use ($termino) {
                        $q->where('codigo', $termino)
                            ->where('estado', 'activo');
                    });
            })
            ->limit(10)
            ->get()
            ->map(function ($presentacion) {
                return [
                    'id' => $presentacion->id,
                    'producto_id' => $presentacion->producto_id,
                    'nombre' => $presentacion->producto->nombre_comercial ?? '-',
                    'generico' => $presentacion->producto->nombre_generico ?? '',
                    'concentracion' => $presentacion->producto->concentracion ?? '',
                    'presentacion' => $presentacion->nombre_mostrado,
                    'unidades_equivalentes' => $presentacion->unidades_equivalentes,
                    'precio_compra' => (float) $presentacion->precio_compra,
                    'precio_venta' => (float) $presentacion->precio_venta,
                    'codigo_barras' => $presentacion->codigosBarras->first()->codigo ?? null,
                ];
            })
            ->values();

        return response()->json($productos);
    }

    public function pagoCreate(Compra $compra)
    {
        if ($compra->estado !== 'registrada') {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'No se puede registrar pagos en una compra anulada.');
        }

        if ($compra->saldo_pendiente <= 0) {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'Esta compra ya se encuentra pagada.');
        }

        $compra->load(['proveedor', 'sucursal', 'usuario', 'pagos.usuario']);

        return view('compras.pago', compact('compra'));
    }

    public function pagoStore(Request $request, Compra $compra)
    {
        if ($compra->estado !== 'registrada') {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'No se puede registrar pagos en una compra anulada.');
        }

        if ($compra->saldo_pendiente <= 0) {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'Esta compra ya se encuentra pagada.');
        }

        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'in:efectivo,qr,transferencia,otro'],
            'referencia' => ['nullable', 'string', 'max:150'],
            'observacion' => ['nullable', 'string'],
        ], [
            'monto.required' => 'Ingrese el monto del pago.',
            'monto.min' => 'El monto del pago debe ser mayor a cero.',
            'metodo_pago.required' => 'Seleccione el método de pago.',
        ]);

        $datos['monto'] = round((float) $datos['monto'], 2);
        $saldoPendienteActual = round((float) $compra->saldo_pendiente, 2);

        if ($datos['monto'] > $saldoPendienteActual) {
            return back()
                ->withErrors([
                    'monto' => 'El monto no puede ser mayor al saldo pendiente.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($datos, $compra) {
            $user = auth()->user();

            PagoCompra::create([
                'compra_id' => $compra->id,
                'usuario_id' => $user->id,
                'fecha_pago' => now(),
                'monto' => $datos['monto'],
                'metodo_pago' => $datos['metodo_pago'],
                'referencia' => $datos['referencia'] ?? null,
                'observacion' => $datos['observacion'] ?? null,
            ]);

            $nuevoMontoPagado = round((float) $compra->monto_pagado + (float) $datos['monto'], 2);
            $nuevoSaldo = round((float) $compra->total - $nuevoMontoPagado, 2);

            if ($nuevoSaldo <= 0) {
                $estadoPago = 'pagado';
                $nuevoSaldo = 0;
            } elseif ($nuevoMontoPagado > 0) {
                $estadoPago = 'parcial';
            } else {
                $estadoPago = 'pendiente';
            }

            $compra->update([
                'monto_pagado' => $nuevoMontoPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado_pago' => $estadoPago,
            ]);
        });

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Pago registrado correctamente.');
    }

    public function deudas(Request $request)
    {
        $busqueda = $request->get('busqueda');

        $compras = Compra::with(['proveedor', 'sucursal', 'usuario'])
            ->where('estado', 'registrada')
            ->where('saldo_pendiente', '>', 0)
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('numero_compra', 'like', "%{$busqueda}%")
                        ->orWhereHas('proveedor', function ($p) use ($busqueda) {
                            $p->where('nombre', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->orderByDesc('saldo_pendiente')
            ->paginate(10)
            ->withQueryString();

        $totalDeuda = Compra::where('estado', 'registrada')
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $cantidadDeudas = Compra::where('estado', 'registrada')
            ->where('saldo_pendiente', '>', 0)
            ->count();

        $proveedoresConDeuda = Compra::where('estado', 'registrada')
            ->where('saldo_pendiente', '>', 0)
            ->distinct('proveedor_id')
            ->count('proveedor_id');

        return view('compras.deudas', compact(
            'compras',
            'busqueda',
            'totalDeuda',
            'cantidadDeudas',
            'proveedoresConDeuda'
        ));
    }

    public function anularCreate(Compra $compra)
    {
        $compra->load([
            'proveedor',
            'sucursal',
            'usuario',
            'detalles.producto',
            'detalles.productoPresentacion.presentacion',
            'detalles.lote',
            'pagos.usuario',
        ]);

        if ($compra->estado !== 'registrada') {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'Solo se pueden anular compras registradas.');
        }

        return view('compras.anular', compact('compra'));
    }

    public function anularStore(Request $request, Compra $compra)
    {
        $datos = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'min:5'],
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($compra->estado !== 'registrada') {
            return redirect()
                ->route('compras.show', $compra)
                ->with('error', 'Solo se pueden anular compras registradas.');
        }

        $compra->load([
            'detalles.producto',
            'detalles.lote',
        ]);

        foreach ($compra->detalles as $detalle) {
            $inventario = Inventario::where('producto_id', $detalle->producto_id)
                ->where('sucursal_id', $compra->sucursal_id)
                ->where('lote_id', $detalle->lote_id)
                ->first();

            if (!$inventario || $inventario->stock_actual < $detalle->unidades_ingresadas) {
                return redirect()
                    ->route('compras.show', $compra)
                    ->with('error', 'No se puede anular la compra porque parte del stock ingresado ya fue vendido o movido. Producto: ' . ($detalle->producto->nombre_comercial ?? 'Sin nombre'));
            }
        }

        DB::transaction(function () use ($compra, $datos) {
            $user = auth()->user();

            $compra->load([
                'detalles.producto',
                'detalles.lote',
            ]);

            foreach ($compra->detalles as $detalle) {
                $inventario = Inventario::where('producto_id', $detalle->producto_id)
                    ->where('sucursal_id', $compra->sucursal_id)
                    ->where('lote_id', $detalle->lote_id)
                    ->first();

                $stockAnterior = $inventario->stock_actual;
                $stockNuevo = $stockAnterior - $detalle->unidades_ingresadas;

                $inventario->update([
                    'stock_actual' => $stockNuevo,
                    'estado' => $stockNuevo <= 0 ? 'agotado' : 'activo',
                ]);

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'sucursal_id' => $compra->sucursal_id,
                    'lote_id' => $detalle->lote_id,
                    'usuario_id' => $user->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $detalle->unidades_ingresadas,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => 'Salida por anulación de compra ' . $compra->numero_compra . '. Motivo: ' . $datos['motivo_anulacion'],
                    'referencia_tipo' => 'anulacion_compra',
                    'referencia_id' => $compra->id,
                ]);
            }

            $compra->update([
                'estado' => 'anulada',
                'saldo_pendiente' => 0,
                'observacion' => trim(($compra->observacion ? $compra->observacion . "\n" : '') . 'ANULADA: ' . $datos['motivo_anulacion']),
            ]);
        });

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Compra anulada correctamente. El inventario fue ajustado.');
    }

    public function show(Compra $compra)
    {
        $compra->load([
            'proveedor',
            'sucursal',
            'usuario',
            'detalles.producto',
            'detalles.productoPresentacion.presentacion',
            'detalles.lote',
            'pagos.usuario',
        ]);

        return view('compras.show', compact('compra'));
    }

    private function generarNumeroCompra(): string
    {
        $ultimaCompra = Compra::latest('id')->first();
        $numero = $ultimaCompra ? $ultimaCompra->id + 1 : 1;

        return 'C-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    private function generarNumeroLoteInterno(): string
    {
        $fecha = now()->format('Ymd');
        $hora = now()->format('His');
        $aleatorio = random_int(100, 999);

        return 'LC-' . $fecha . '-' . $hora . '-' . $aleatorio;
    }
}
