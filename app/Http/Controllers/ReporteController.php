<?php

namespace App\Http\Controllers;

use App\Models\AtencionServicio;
use App\Models\AtencionServicioInsumo;
use App\Models\Inventario;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use Carbon\Carbon;
use App\Models\DetalleVentaPromocion;
use App\Models\DetalleVentaPromocionItem;
use Illuminate\Http\Request;
use App\Models\ServicioFarmacia;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = Carbon::parse($fecha)->endOfDay();

        $ventasDelDia = Venta::with(['pagos.metodoPago', 'usuario', 'sucursal'])
            ->whereBetween('fecha_hora', [$inicioDia, $finDia])
            ->where('estado', 'completada')
            ->latest('fecha_hora')
            ->get();

        $totalVentas = $ventasDelDia->sum('total');
        $cantidadVentas = $ventasDelDia->count();

        $totalEfectivo = 0;
        $totalQr = 0;

        foreach ($ventasDelDia as $venta) {
            foreach ($venta->pagos as $pago) {
                if ($pago->metodoPago?->tipo === 'efectivo') {
                    $totalEfectivo += $pago->monto;
                } else {
                    $totalQr += $pago->monto;
                }
            }
        }

        $stockBajo = Inventario::with(['producto', 'sucursal', 'lote'])
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo')
            ->orderBy('stock_actual')
            ->limit(10)
            ->get();

        $productosAgotados = Inventario::with(['producto', 'sucursal', 'lote'])
            ->where('stock_actual', '<=', 0)
            ->limit(10)
            ->get();

        $productosPorVencer = Inventario::with(['producto', 'sucursal', 'lote'])
            ->whereHas('lote', function ($query) {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ]);
            })
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo')
            ->limit(10)
            ->get();

        $ultimasVentas = Venta::with(['usuario', 'sucursal', 'pagos.metodoPago'])
            ->latest('fecha_hora')
            ->limit(10)
            ->get();

        return view('reportes.index', compact(
            'fecha',
            'ventasDelDia',
            'totalVentas',
            'cantidadVentas',
            'totalEfectivo',
            'totalQr',
            'stockBajo',
            'productosAgotados',
            'productosPorVencer',
            'ultimasVentas'
        ));
    }

    public function promociones(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $promocionesVendidas = DetalleVentaPromocion::with([
                'venta.sucursal',
                'venta.usuario',
                'promocion',
                'items.producto.laboratorio',
                'items.productoPresentacion.presentacion',
                'items.lote',
            ])
            ->whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->latest()
            ->get();

        $resumenPromociones = DetalleVentaPromocion::selectRaw('
                promocion_id,
                SUM(cantidad) as cantidad_vendida,
                SUM(subtotal) as total_generado
            ')
            ->with('promocion')
            ->whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->groupBy('promocion_id')
            ->orderByDesc('total_generado')
            ->get();

        $productosDescontados = DetalleVentaPromocionItem::selectRaw('
                producto_id,
                producto_presentacion_id,
                SUM(unidades_descontadas) as total_unidades_descontadas
            ')
            ->with([
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->whereHas('detalleVentaPromocion.venta', function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->groupBy('producto_id', 'producto_presentacion_id')
            ->orderByDesc('total_unidades_descontadas')
            ->get();

        $totalGenerado = $promocionesVendidas->sum('subtotal');
        $totalPromocionesVendidas = $promocionesVendidas->sum('cantidad');

        return view('reportes.promociones', compact(
            'fechaInicio',
            'fechaFin',
            'promocionesVendidas',
            'resumenPromociones',
            'productosDescontados',
            'totalGenerado',
            'totalPromocionesVendidas'
        ));
    }

    public function servicios(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $estado = $request->get('estado');
        $servicioId = $request->get('servicio_farmacia_id');

        $query = AtencionServicio::with([
                'servicio',
                'cliente',
                'usuario',
                'metodoPago',
            ])
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($servicioId, function ($query, $servicioId) {
                $query->where('servicio_farmacia_id', $servicioId);
            });

        $atenciones = (clone $query)
            ->latest('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        $resumen = [
            'total_atenciones' => (clone $query)->count(),
            'atenciones_completadas' => (clone $query)->where('estado', 'completada')->count(),
            'atenciones_anuladas' => (clone $query)->where('estado', 'anulada')->count(),
            'ingresos_completados' => (clone $query)->where('estado', 'completada')->sum('total'),
            'ingresos_anulados' => (clone $query)->where('estado', 'anulada')->sum('total'),
        ];

        $serviciosMasVendidos = AtencionServicio::select(
                'servicio_farmacia_id',
                DB::raw('SUM(cantidad) as cantidad_total'),
                DB::raw('COUNT(*) as atenciones_total'),
                DB::raw('SUM(total) as ingresos_total')
            )
            ->with('servicio')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'completada')
            ->when($servicioId, function ($query, $servicioId) {
                $query->where('servicio_farmacia_id', $servicioId);
            })
            ->groupBy('servicio_farmacia_id')
            ->orderByDesc('ingresos_total')
            ->limit(10)
            ->get();

        $insumosConsumidos = AtencionServicioInsumo::select(
                'producto_id',
                'producto_presentacion_id',
                DB::raw('SUM(unidades_descontadas) as unidades_total')
            )
            ->with([
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->whereHas('atencionServicio', function ($query) use ($fechaInicio, $fechaFin, $servicioId) {
                $query->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin)
                    ->where('estado', 'completada')
                    ->when($servicioId, function ($q, $servicioId) {
                        $q->where('servicio_farmacia_id', $servicioId);
                    });
            })
            ->groupBy('producto_id', 'producto_presentacion_id')
            ->orderByDesc('unidades_total')
            ->limit(15)
            ->get();

        $servicios = ServicioFarmacia::orderBy('nombre')->get();

        return view('reportes.servicios', compact(
            'fechaInicio',
            'fechaFin',
            'estado',
            'servicioId',
            'atenciones',
            'resumen',
            'serviciosMasVendidos',
            'insumosConsumidos',
            'servicios'
        ));
    }

    public function serviciosExportarCsv(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $estado = $request->get('estado');
        $servicioId = $request->get('servicio_farmacia_id');

        $atenciones = AtencionServicio::with([
                'servicio',
                'cliente',
                'usuario',
                'metodoPago',
                'sucursal',
            ])
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($servicioId, function ($query, $servicioId) {
                $query->where('servicio_farmacia_id', $servicioId);
            })
            ->latest('fecha_hora')
            ->get();

        $nombreArchivo = 'reporte_servicios_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($atenciones) {
            $archivo = fopen('php://output', 'w');

            // BOM para que Excel abra acentos correctamente
            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, [
                'ID',
                'Fecha',
                'Servicio',
                'Cliente',
                'CI/NIT',
                'Sucursal',
                'Cantidad',
                'Precio unitario',
                'Subtotal',
                'Descuento',
                'Total',
                'Metodo de pago',
                'Usuario',
                'Estado',
                'Observacion',
                'Motivo anulacion',
            ], ';');

            foreach ($atenciones as $atencion) {
                fputcsv($archivo, [
                    $atencion->id,
                    $atencion->fecha_hora?->format('d/m/Y H:i'),
                    $atencion->servicio->nombre ?? '-',
                    $atencion->cliente->nombre ?? 'Consumidor final',
                    $atencion->cliente->ci_nit ?? '',
                    $atencion->sucursal->nombre ?? '-',
                    $atencion->cantidad,
                    number_format($atencion->precio_unitario, 2, '.', ''),
                    number_format($atencion->subtotal, 2, '.', ''),
                    number_format($atencion->descuento, 2, '.', ''),
                    number_format($atencion->total, 2, '.', ''),
                    $atencion->metodoPago->nombre ?? '-',
                    $atencion->usuario->nombre ?? '-',
                    $atencion->estado,
                    $atencion->observacion ?? '',
                    $atencion->motivo_anulacion ?? '',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function serviciosInsumosExportarCsv(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $servicioId = $request->get('servicio_farmacia_id');

        $insumos = AtencionServicioInsumo::with([
                'atencionServicio.servicio',
                'atencionServicio.cliente',
                'atencionServicio.usuario',
                'atencionServicio.sucursal',
                'producto.laboratorio',
                'productoPresentacion.presentacion',
                'lote',
            ])
            ->whereHas('atencionServicio', function ($query) use ($fechaInicio, $fechaFin, $servicioId) {
                $query->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin)
                    ->where('estado', 'completada')
                    ->when($servicioId, function ($q, $servicioId) {
                        $q->where('servicio_farmacia_id', $servicioId);
                    });
            })
            ->latest('created_at')
            ->get();

        $nombreArchivo = 'insumos_servicios_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($insumos) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, [
                'Atencion ID',
                'Fecha',
                'Servicio',
                'Cliente',
                'Sucursal',
                'Insumo',
                'Laboratorio',
                'Concentracion',
                'Presentacion',
                'Lote',
                'Fecha vencimiento',
                'Unidades descontadas',
                'Usuario',
            ], ';');

            foreach ($insumos as $insumo) {
                fputcsv($archivo, [
                    $insumo->atencion_servicio_id,
                    $insumo->atencionServicio->fecha_hora?->format('d/m/Y H:i'),
                    $insumo->atencionServicio->servicio->nombre ?? '-',
                    $insumo->atencionServicio->cliente->nombre ?? 'Consumidor final',
                    $insumo->atencionServicio->sucursal->nombre ?? '-',
                    $insumo->productoPresentacion->nombre_mostrado ?? $insumo->producto->nombre_comercial ?? '-',
                    $insumo->producto->laboratorio->nombre ?? '',
                    $insumo->producto->concentracion ?? '',
                    $insumo->productoPresentacion->presentacion->nombre ?? '',
                    $insumo->lote->numero_lote ?? 'Sin lote',
                    $insumo->lote?->fecha_vencimiento?->format('d/m/Y') ?? '',
                    $insumo->unidades_descontadas,
                    $insumo->atencionServicio->usuario->nombre ?? '-',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function cajaDiaria(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        $cajas = Caja::with([
            'sucursal',
            'turno',
        ])
        ->whereDate('fecha_apertura', $fecha)
        ->orderByDesc('fecha_apertura')
        ->get();

        $resumenCajas = $cajas->map(function ($caja) {
            $ventasCompletadas = Venta::where('caja_id', $caja->id)
                ->where('estado', 'completada')
                ->sum('total');

            $ventasAnuladas = Venta::where('caja_id', $caja->id)
                ->where('estado', 'anulada')
                ->sum('total');

            $serviciosCompletados = AtencionServicio::where('caja_id', $caja->id)
                ->where('estado', 'completada')
                ->sum('total');

            $serviciosAnulados = AtencionServicio::where('caja_id', $caja->id)
                ->where('estado', 'anulada')
                ->sum('total');

            $egresos = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo_movimiento', 'egreso')
                ->sum('monto');

            $reembolsos = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo_movimiento', 'reembolso')
                ->sum('monto');

            $anulaciones = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo_movimiento', 'anulacion')
                ->sum('monto');

            return [
                'caja' => $caja,
                'ventas_completadas' => $ventasCompletadas,
                'ventas_anuladas' => $ventasAnuladas,
                'servicios_completados' => $serviciosCompletados,
                'servicios_anulados' => $serviciosAnulados,
                'egresos' => $egresos,
                'reembolsos' => $reembolsos,
                'anulaciones' => $anulaciones,
                'ingresos_validos' => $ventasCompletadas + $serviciosCompletados,
            ];
        });

        $totales = [
            'ventas_completadas' => $resumenCajas->sum('ventas_completadas'),
            'ventas_anuladas' => $resumenCajas->sum('ventas_anuladas'),
            'servicios_completados' => $resumenCajas->sum('servicios_completados'),
            'servicios_anulados' => $resumenCajas->sum('servicios_anulados'),
            'egresos' => $resumenCajas->sum('egresos'),
            'reembolsos' => $resumenCajas->sum('reembolsos'),
            'anulaciones' => $resumenCajas->sum('anulaciones'),
            'ingresos_validos' => $resumenCajas->sum('ingresos_validos'),
        ];

        return view('reportes.caja-diaria', compact(
            'fecha',
            'resumenCajas',
            'totales'
        ));
    }

    public function cajaDiariaExportarCsv(Request $request)
    {
        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        $cajas = Caja::with([
                'sucursal',
                'turno',
            ])
            ->whereDate('fecha_apertura', $fecha)
            ->orderByDesc('fecha_apertura')
            ->get();

        $nombreArchivo = 'reporte_caja_diaria_' . $fecha . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($cajas) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, [
                'Caja ID',
                'Sucursal',
                'Turno',
                'Estado',
                'Fecha apertura',
                'Fecha cierre',
                'Monto inicial',
                'Ventas completadas',
                'Servicios completados',
                'Ingresos validos',
                'Ventas anuladas',
                'Servicios anulados',
                'Egresos',
                'Reembolsos',
                'Movimientos anulacion',
                'Total efectivo caja',
                'Total QR caja',
                'Total final caja',
            ], ';');

            foreach ($cajas as $caja) {
                $ventasCompletadas = Venta::where('caja_id', $caja->id)
                    ->where('estado', 'completada')
                    ->sum('total');

                $ventasAnuladas = Venta::where('caja_id', $caja->id)
                    ->where('estado', 'anulada')
                    ->sum('total');

                $serviciosCompletados = AtencionServicio::where('caja_id', $caja->id)
                    ->where('estado', 'completada')
                    ->sum('total');

                $serviciosAnulados = AtencionServicio::where('caja_id', $caja->id)
                    ->where('estado', 'anulada')
                    ->sum('total');

                $egresos = MovimientoCaja::where('caja_id', $caja->id)
                    ->where('tipo_movimiento', 'egreso')
                    ->sum('monto');

                $reembolsos = MovimientoCaja::where('caja_id', $caja->id)
                    ->where('tipo_movimiento', 'reembolso')
                    ->sum('monto');

                $anulaciones = MovimientoCaja::where('caja_id', $caja->id)
                    ->where('tipo_movimiento', 'anulacion')
                    ->sum('monto');

                $ingresosValidos = $ventasCompletadas + $serviciosCompletados;

                fputcsv($archivo, [
                    $caja->id,
                    $caja->sucursal->nombre ?? '-',
                    $caja->turno->nombre ?? 'Sin turno',
                    $caja->estado,
                    $caja->fecha_apertura?->format('d/m/Y H:i'),
                    $caja->fecha_cierre?->format('d/m/Y H:i') ?? '',
                    number_format($caja->monto_inicial, 2, '.', ''),
                    number_format($ventasCompletadas, 2, '.', ''),
                    number_format($serviciosCompletados, 2, '.', ''),
                    number_format($ingresosValidos, 2, '.', ''),
                    number_format($ventasAnuladas, 2, '.', ''),
                    number_format($serviciosAnulados, 2, '.', ''),
                    number_format($egresos, 2, '.', ''),
                    number_format($reembolsos, 2, '.', ''),
                    number_format($anulaciones, 2, '.', ''),
                    number_format($caja->total_efectivo, 2, '.', ''),
                    number_format($caja->total_qr, 2, '.', ''),
                    number_format($caja->total_final, 2, '.', ''),
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function ingresosDiarios(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $ventasCompletadas = Venta::whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'completada')
            ->get();

        $ventasAnuladas = Venta::whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'anulada')
            ->get();

        $serviciosCompletados = AtencionServicio::with(['servicio', 'cliente', 'usuario', 'metodoPago'])
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'completada')
            ->get();

        $serviciosAnulados = AtencionServicio::whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'anulada')
            ->get();

        $resumen = [
            'total_ventas' => $ventasCompletadas->sum('total'),
            'total_servicios' => $serviciosCompletados->sum('total'),
            'ingresos_totales' => $ventasCompletadas->sum('total') + $serviciosCompletados->sum('total'),

            'cantidad_ventas' => $ventasCompletadas->count(),
            'cantidad_servicios' => $serviciosCompletados->count(),

            'ventas_anuladas' => $ventasAnuladas->count(),
            'servicios_anulados' => $serviciosAnulados->count(),

            'monto_ventas_anuladas' => $ventasAnuladas->sum('total'),
            'monto_servicios_anulados' => $serviciosAnulados->sum('total'),
        ];

        $detalleServicios = AtencionServicio::with([
                'servicio',
                'cliente',
                'usuario',
                'metodoPago',
            ])
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->latest('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.ingresos-diarios', compact(
            'fechaInicio',
            'fechaFin',
            'resumen',
            'detalleServicios'
        ));
    }

    public function ingresosDiariosExportarCsv(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $ventasCompletadas = Venta::whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'completada')
            ->get();

        $ventasAnuladas = Venta::whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'anulada')
            ->get();

        $serviciosCompletados = AtencionServicio::with([
                'servicio',
                'cliente',
                'usuario',
                'metodoPago',
                'sucursal',
            ])
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'completada')
            ->get();

        $serviciosAnulados = AtencionServicio::with([
                'servicio',
                'cliente',
                'usuario',
                'metodoPago',
                'sucursal',
            ])
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'anulada')
            ->get();

        $nombreArchivo = 'ingresos_diarios_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($ventasCompletadas, $ventasAnuladas, $serviciosCompletados, $serviciosAnulados, $fechaInicio, $fechaFin) {
            $archivo = fopen('php://output', 'w');

            // BOM para que Excel abra acentos correctamente
            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE INGRESOS DIARIOS'], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN GENERAL'], ';');
            fputcsv($archivo, ['Concepto', 'Cantidad', 'Monto Bs'], ';');

            fputcsv($archivo, [
                'Ventas completadas',
                $ventasCompletadas->count(),
                number_format($ventasCompletadas->sum('total'), 2, '.', ''),
            ], ';');

            fputcsv($archivo, [
                'Servicios completados',
                $serviciosCompletados->count(),
                number_format($serviciosCompletados->sum('total'), 2, '.', ''),
            ], ';');

            fputcsv($archivo, [
                'Ingresos totales',
                $ventasCompletadas->count() + $serviciosCompletados->count(),
                number_format($ventasCompletadas->sum('total') + $serviciosCompletados->sum('total'), 2, '.', ''),
            ], ';');

            fputcsv($archivo, [
                'Ventas anuladas',
                $ventasAnuladas->count(),
                number_format($ventasAnuladas->sum('total'), 2, '.', ''),
            ], ';');

            fputcsv($archivo, [
                'Servicios anulados',
                $serviciosAnulados->count(),
                number_format($serviciosAnulados->sum('total'), 2, '.', ''),
            ], ';');

            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE DE SERVICIOS'], ';');
            fputcsv($archivo, [
                'ID',
                'Fecha',
                'Servicio',
                'Cliente',
                'Sucursal',
                'Cantidad',
                'Precio unitario',
                'Subtotal',
                'Descuento',
                'Total',
                'Método de pago',
                'Usuario',
                'Estado',
                'Observación',
            ], ';');

            $servicios = $serviciosCompletados
                ->concat($serviciosAnulados)
                ->sortByDesc('fecha_hora');

            foreach ($servicios as $atencion) {
                fputcsv($archivo, [
                    $atencion->id,
                    $atencion->fecha_hora?->format('d/m/Y H:i'),
                    $atencion->servicio->nombre ?? '-',
                    $atencion->cliente->nombre ?? 'Consumidor final',
                    $atencion->sucursal->nombre ?? '-',
                    $atencion->cantidad,
                    number_format($atencion->precio_unitario, 2, '.', ''),
                    number_format($atencion->subtotal, 2, '.', ''),
                    number_format($atencion->descuento, 2, '.', ''),
                    number_format($atencion->total, 2, '.', ''),
                    $atencion->metodoPago->nombre ?? '-',
                    $atencion->usuario->nombre ?? '-',
                    $atencion->estado,
                    $atencion->observacion ?? '',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }
}
