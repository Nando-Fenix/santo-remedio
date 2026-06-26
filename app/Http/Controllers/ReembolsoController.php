<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\DetalleReembolso;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Reembolso;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReembolsoController extends Controller
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
            'pagos.metodoPago',
            'reembolsos.detalles',
        ]);

        if ($venta->estado !== 'completada') {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar reembolsos en ventas completadas.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $venta->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $venta->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar reembolsos de ventas de la caja abierta actual.');
        }

        return view('reembolsos.create', compact('venta'));
    }
/**
 * Registra un reembolso parcial de una venta.
 *
 * Reglas:
 * - Solo permite reembolsar ventas completadas de la caja abierta actual.
 * - Devuelve stock a los lotes usados originalmente en la venta.
 * - Registra movimientos de inventario para trazabilidad.
 * - Ajusta caja descontando el monto reembolsado.
 * - No modifica la venta original ni la elimina.
 */
    public function store(Request $request, Venta $venta)
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:5'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.detalle_venta_id' => ['required', 'exists:detalle_ventas,id'],
            'items.*.cantidad_devuelta' => ['nullable', 'integer', 'min:0'],
        ], [
            'motivo.required' => 'Debe ingresar el motivo del reembolso.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'items.required' => 'Debe seleccionar al menos un producto para reembolsar.',
        ]);

        if ($venta->estado !== 'completada') {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar reembolsos en ventas completadas.');
        }

        $user = auth()->user();

        $cajaAbierta = Caja::where('sucursal_id', $venta->sucursal_id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta || $venta->caja_id !== $cajaAbierta->id) {
            return redirect()
                ->route('ventas.show', $venta)
                ->with('error', 'Solo se pueden registrar reembolsos de ventas de la caja abierta actual.');
        }

        $itemsProcesados = [];
        $montoTotalReembolso = 0;

        foreach ($datos['items'] as $item) {
            $cantidadDevuelta = (int) ($item['cantidad_devuelta'] ?? 0);

            if ($cantidadDevuelta <= 0) {
                continue;
            }

            $detalleVenta = DetalleVenta::with([
                    'producto',
                    'productoPresentacion',
                    'lotesDescontados.lote',
                    'reembolsos',
                ])
                ->where('venta_id', $venta->id)
                ->findOrFail($item['detalle_venta_id']);

            $cantidadYaDevuelta = $detalleVenta->reembolsos()
                ->whereHas('reembolso', function ($query) {
                    $query->where('estado', 'registrado');
                })
                ->sum('cantidad_devuelta');

            $cantidadDisponible = $detalleVenta->cantidad - $cantidadYaDevuelta;

            if ($cantidadDevuelta > $cantidadDisponible) {
                return back()
                    ->withErrors([
                        'cantidad' => 'La cantidad devuelta supera lo disponible para: ' . ($detalleVenta->producto->nombre_comercial ?? 'Producto'),
                    ])
                    ->withInput();
            }

            $montoDevuelto = round($cantidadDevuelta * (float) $detalleVenta->precio_unitario, 2);
            $unidadesDevueltas = $cantidadDevuelta * $detalleVenta->productoPresentacion->unidades_equivalentes;

            $itemsProcesados[] = [
                'detalle_venta' => $detalleVenta,
                'cantidad_devuelta' => $cantidadDevuelta,
                'unidades_devueltas' => $unidadesDevueltas,
                'monto_devuelto' => $montoDevuelto,
            ];

            $montoTotalReembolso += $montoDevuelto;
        }

        $montoTotalReembolso = round($montoTotalReembolso, 2);

        if (count($itemsProcesados) === 0) {
            return back()
                ->withErrors([
                    'items' => 'Debe ingresar una cantidad mayor a cero en al menos un producto.',
                ])
                ->withInput();
        }

        if ($montoTotalReembolso <= 0) {
            return back()
                ->withErrors([
                    'monto' => 'El monto del reembolso debe ser mayor a cero.',
                ])
                ->withInput();
        }

        $reembolso = DB::transaction(function () use (
            $venta,
            $datos,
            $user,
            $cajaAbierta,
            $itemsProcesados,
            $montoTotalReembolso
        ) {
            $numeroReembolso = $this->generarNumeroReembolso();

            $reembolso = Reembolso::create([
                'venta_id' => $venta->id,
                'usuario_id' => $user->id,
                'sucursal_id' => $venta->sucursal_id,
                'caja_id' => $cajaAbierta->id,
                'numero_reembolso' => $numeroReembolso,
                'fecha_reembolso' => now(),
                'monto_total' => $montoTotalReembolso,
                'motivo' => $datos['motivo'],
                'estado' => 'registrado',
            ]);

            foreach ($itemsProcesados as $item) {
                $detalleVenta = $item['detalle_venta'];
                $unidadesPendientes = $item['unidades_devueltas'];

                foreach ($detalleVenta->lotesDescontados as $loteDescontado) {
                    if ($unidadesPendientes <= 0) {
                        break;
                    }

                    $unidadesADevolver = min($loteDescontado->unidades_descontadas, $unidadesPendientes);

                    $inventario = Inventario::firstOrCreate(
                        [
                            'producto_id' => $detalleVenta->producto_id,
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

                    DetalleReembolso::create([
                        'reembolso_id' => $reembolso->id,
                        'detalle_venta_id' => $detalleVenta->id,
                        'producto_id' => $detalleVenta->producto_id,
                        'producto_presentacion_id' => $detalleVenta->producto_presentacion_id,
                        'lote_id' => $loteDescontado->lote_id,
                        'cantidad_devuelta' => $item['cantidad_devuelta'],
                        'unidades_devueltas' => $unidadesADevolver,
                        'monto_devuelto' => $item['monto_devuelto'],
                    ]);

                    MovimientoInventario::create([
                        'producto_id' => $detalleVenta->producto_id,
                        'sucursal_id' => $venta->sucursal_id,
                        'lote_id' => $loteDescontado->lote_id,
                        'usuario_id' => $user->id,
                        'tipo_movimiento' => 'entrada',
                        'cantidad' => $unidadesADevolver,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'Entrada por reembolso ' . $reembolso->numero_reembolso . ' de venta ' . $venta->numero_venta,
                        'referencia_tipo' => 'reembolso',
                        'referencia_id' => $reembolso->id,
                    ]);

                    $unidadesPendientes -= $unidadesADevolver;
                }
            }

            $metodoPagoPrincipal = $venta->pagos()->with('metodoPago')->first();

            if ($metodoPagoPrincipal?->metodoPago?->tipo === 'efectivo') {
                $cajaAbierta->decrement('total_efectivo', $montoTotalReembolso);
            } else {
                $cajaAbierta->decrement('total_qr', $montoTotalReembolso);
            }

            $cajaAbierta->increment('total_reembolsos', $montoTotalReembolso);
            $cajaAbierta->refresh();

            $cajaAbierta->update([
                'total_final' => $cajaAbierta->monto_inicial
                    + $cajaAbierta->total_efectivo
                    - $cajaAbierta->total_egresos
                    - $cajaAbierta->total_reembolsos,
            ]);

            MovimientoCaja::create([
                'caja_id' => $cajaAbierta->id,
                'venta_id' => $venta->id,
                'usuario_id' => $user->id,
                'sucursal_id' => $venta->sucursal_id,
                'metodo_pago_id' => $metodoPagoPrincipal?->metodo_pago_id,
                'tipo_movimiento' => 'egreso',
                'monto' => $montoTotalReembolso,
                'descripcion' => 'Reembolso ' . $reembolso->numero_reembolso . ' de venta ' . $venta->numero_venta . '. Motivo: ' . $datos['motivo'],
                'autorizado_por' => null,
            ]);

            return $reembolso;
        });

        return redirect()
            ->route('reembolsos.show', $reembolso)
            ->with('success', 'Reembolso registrado correctamente. Stock y caja actualizados.');
    }

    public function show(Reembolso $reembolso)
    {
        $reembolso->load([
            'venta',
            'usuario',
            'sucursal',
            'caja',
            'detalles.producto',
            'detalles.productoPresentacion.presentacion',
            'detalles.lote',
        ]);

        return view('reembolsos.show', compact('reembolso'));
    }

    private function generarNumeroReembolso(): string
    {
        $ultimoReembolso = Reembolso::latest('id')->first();
        $numero = $ultimoReembolso ? $ultimoReembolso->id + 1 : 1;

        return 'RE-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}
