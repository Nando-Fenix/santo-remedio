<?php

namespace App\Http\Controllers;

use App\Models\AtencionServicio;
use App\Models\AtencionServicioInsumo;
use App\Models\BajaInventario;
use App\Models\Inventario;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use App\Models\Compra;
use Carbon\Carbon;
use App\Models\DetalleVentaPromocion;
use App\Models\DetalleVentaPromocionItem;
use Illuminate\Http\Request;
use App\Models\DetalleVenta;
use App\Models\ServicioFarmacia;
use App\Models\MovimientoInventario;
use App\Models\MetodoPago;
use App\Models\PagoVenta;
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
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

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
            ->whereHas('venta', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
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
            'sucursal',
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

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

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
            ->where('sucursal_id', $sucursal->id)
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
            'sucursal',
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
                'N° atencion',
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
                    $atencion->numero_atencion ?? 'SER-' . str_pad($atencion->id, 6, '0', STR_PAD_LEFT),
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

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        $cajas = Caja::with([
            'sucursal',
            'turno',
        ])
        ->where('sucursal_id', $sucursal->id)
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
            'sucursal',
            'fecha',
            'resumenCajas',
            'totales'
        ));
    }

    public function cajaDiariaExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        $cajas = Caja::with([
                'sucursal',
                'turno',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_apertura', $fecha)
            ->orderByDesc('fecha_apertura')
            ->get();

        $nombreArchivo = 'reporte_caja_diaria_' . $sucursal->id . '_' . $fecha . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($cajas, $sucursal, $fecha) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE CAJA DIARIA'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Fecha', $fecha], ';');
            fputcsv($archivo, [], ';');

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

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

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
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'resumen',
            'detalleServicios'
        ));
    }

    public function ingresosDiariosExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $ventasCompletadas = Venta::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'completada')
            ->get();

        $ventasAnuladas = Venta::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_hora', '>=', $fechaInicio)
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
            ->where('sucursal_id', $sucursal->id)
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
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->where('estado', 'anulada')
            ->get();

        $nombreArchivo = 'ingresos_diarios_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($ventasCompletadas, $ventasAnuladas, $serviciosCompletados, $serviciosAnulados, $fechaInicio, $fechaFin, $sucursal) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE INGRESOS DIARIOS'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
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

    public function promocionesExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $promocionesVendidas = DetalleVentaPromocion::with([
                'venta.usuario',
                'venta.cliente',
                'promocion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->latest()
            ->get();

        $productosDescontados = DetalleVentaPromocionItem::with([
                'detalleVentaPromocion.venta',
                'detalleVentaPromocion.promocion',
                'producto.laboratorio',
                'productoPresentacion.presentacion',
                'lote',
            ])
            ->whereHas('detalleVentaPromocion.venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->get();

        $nombreArchivo = 'promociones_vendidas_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($promocionesVendidas, $productosDescontados, $sucursal, $fechaInicio, $fechaFin) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE PROMOCIONES VENDIDAS'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN GENERAL'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Promociones vendidas', $promocionesVendidas->sum('cantidad')], ';');
            fputcsv($archivo, ['Total generado', number_format($promocionesVendidas->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, ['Productos descontados', $productosDescontados->sum('unidades_descontadas')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE DE PROMOCIONES VENDIDAS'], ';');
            fputcsv($archivo, [
                'Fecha',
                'Nro venta',
                'Promoción',
                'Cliente',
                'Vendedor',
                'Cantidad',
                'Precio unitario',
                'Subtotal',
                'Descuento',
                'Total',
                'Estado venta',
            ], ';');

            foreach ($promocionesVendidas as $detalle) {
                fputcsv($archivo, [
                    $detalle->venta->fecha_hora?->format('d/m/Y H:i'),
                    $detalle->venta->numero_venta ?? '-',
                    $detalle->promocion->nombre ?? '-',
                    $detalle->venta->cliente->nombre ?? 'Consumidor final',
                    $detalle->venta->usuario->nombre ?? '-',
                    $detalle->cantidad,
                    number_format($detalle->precio_unitario, 2, '.', ''),
                    number_format($detalle->subtotal, 2, '.', ''),
                    number_format($detalle->descuento, 2, '.', ''),
                    number_format($detalle->total, 2, '.', ''),
                    $detalle->venta->estado ?? '-',
                ], ';');
            }

            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['PRODUCTOS DESCONTADOS POR PROMOCIONES'], ';');
            fputcsv($archivo, [
                'Fecha venta',
                'Nro venta',
                'Promoción',
                'Producto',
                'Laboratorio',
                'Presentación',
                'Lote',
                'Fecha vencimiento',
                'Unidades descontadas',
            ], ';');

            foreach ($productosDescontados as $item) {
                fputcsv($archivo, [
                    $item->detalleVentaPromocion->venta->fecha_hora?->format('d/m/Y H:i'),
                    $item->detalleVentaPromocion->venta->numero_venta ?? '-',
                    $item->detalleVentaPromocion->promocion->nombre ?? '-',
                    $item->productoPresentacion->nombre_mostrado
                        ?? $item->producto->nombre_comercial
                        ?? '-',
                    $item->producto->laboratorio->nombre ?? '-',
                    $item->productoPresentacion->presentacion->nombre ?? '-',
                    $item->lote->numero_lote ?? '-',
                    $item->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-',
                    $item->unidades_descontadas,
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function ventas(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $estado = $request->get('estado');
        $buscar = $request->get('buscar');

        $ventasQuery = Venta::with([
                'cliente',
                'usuario',
                'pagos.metodoPago',

                'detalles.producto',
                'detalles.productoPresentacion.presentacion',

                'promociones.promocion',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_venta', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($buscar) {
                            $clienteQuery->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('ci_nit', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('pagos.metodoPago', function ($metodoQuery) use ($buscar) {
                            $metodoQuery->where('nombre', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('detalles.producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('nombre_comercial', 'like', "%{$buscar}%")
                                ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                                ->orWhere('concentracion', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('detalles.productoPresentacion', function ($presentacionQuery) use ($buscar) {
                            $presentacionQuery->where('nombre_mostrado', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('promociones.promocion', function ($promocionQuery) use ($buscar) {
                            $promocionQuery->where('nombre', 'like', "%{$buscar}%");
                        });
                });
            })
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin);

        if ($estado) {
            $ventasQuery->where('estado', $estado);
        }

        $ventasResumen = (clone $ventasQuery)->get();

        $resumen = [
            'ventas_completadas' => $ventasResumen->where('estado', 'completada')->count(),
            'ventas_anuladas' => $ventasResumen->where('estado', 'anulada')->count(),
            'total_completadas' => $ventasResumen->where('estado', 'completada')->sum('total'),
            'total_anuladas' => $ventasResumen->where('estado', 'anulada')->sum('total'),
            'descuentos' => $ventasResumen->where('estado', 'completada')->sum('descuento_total'),
            'total_general' => $ventasResumen->where('estado', 'completada')->sum('total'),
        ];

        $ventas = $ventasQuery
            ->latest('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.ventas', compact(
            'buscar',
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'estado',
            'resumen',
            'ventas'
        ));
    }

    public function ventasExportarCsv(Request $request)
    {
        $buscar = $request->get('buscar');  
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $estado = $request->get('estado');

        $ventasQuery = Venta::with([
                'cliente',
                'usuario',
                'pagos.metodoPago',

                'detalles.producto',
                'detalles.productoPresentacion.presentacion',

                'promociones.promocion',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_venta', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($buscar) {
                            $clienteQuery->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('ci_nit', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('pagos.metodoPago', function ($metodoQuery) use ($buscar) {
                            $metodoQuery->where('nombre', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('detalles.producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('nombre_comercial', 'like', "%{$buscar}%")
                                ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                                ->orWhere('concentracion', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('detalles.productoPresentacion', function ($presentacionQuery) use ($buscar) {
                            $presentacionQuery->where('nombre_mostrado', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('promociones.promocion', function ($promocionQuery) use ($buscar) {
                            $promocionQuery->where('nombre', 'like', "%{$buscar}%");
                        });
                });
            })
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin);

        if ($estado) {
            $ventasQuery->where('estado', $estado);
        }

        $ventas = $ventasQuery
            ->latest('fecha_hora')
            ->get();

        $nombreArchivo = 'reporte_ventas_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($ventas, $sucursal, $fechaInicio, $fechaFin, $estado) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE VENTAS'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Estado', $estado ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Ventas completadas', $ventas->where('estado', 'completada')->count()], ';');
            fputcsv($archivo, ['Ventas anuladas', $ventas->where('estado', 'anulada')->count()], ';');
            fputcsv($archivo, ['Total completadas Bs', number_format($ventas->where('estado', 'completada')->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, ['Total anuladas Bs', number_format($ventas->where('estado', 'anulada')->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, ['Descuentos Bs', number_format($ventas->where('estado', 'completada')->sum('descuento_total'), 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE DE VENTAS'], ';');
            fputcsv($archivo, [
                'Nro venta',
                'Fecha',
                'Cliente',
                'Vendedor',
                'Métodos de pago',
                'Subtotal',
                'Descuento',
                'Total',
                'Estado',
                'Promociones',
            ], ';');

            foreach ($ventas as $venta) {
                $metodosPago = $venta->pagos
                    ->map(fn ($pago) => $pago->metodoPago->nombre ?? '-')
                    ->implode(', ');

                $promociones = $venta->promociones
                    ->map(fn ($detalle) => $detalle->promocion->nombre ?? '-')
                    ->implode(', ');

                fputcsv($archivo, [
                    $venta->numero_venta,
                    $venta->fecha_hora?->format('d/m/Y H:i'),
                    $venta->cliente->nombre ?? 'Consumidor final',
                    $venta->usuario->nombre ?? '-',
                    $metodosPago,
                    number_format($venta->subtotal, 2, '.', ''),
                    number_format($venta->descuento_total, 2, '.', ''), 
                    number_format($venta->total, 2, '.', ''),
                    $venta->estado,
                    $promociones ?: '-',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function compras(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $estado = $request->get('estado');

        $comprasQuery = Compra::with([
                'proveedor',
                'usuario',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_compra', '>=', $fechaInicio)
            ->whereDate('fecha_compra', '<=', $fechaFin);

        if ($estado) {
            $comprasQuery->where('estado', $estado);
        }

        $comprasResumen = (clone $comprasQuery)->get();

        $comprasValidas = $comprasResumen->where('estado', '!=', 'anulada');

        $resumen = [
            'cantidad_compras' => $comprasResumen->count(),
            'compras_pagadas' => $comprasResumen->where('estado', 'pagada')->count(),
            'compras_pendientes' => $comprasResumen->where('estado', 'pendiente')->count(),
            'compras_anuladas' => $comprasResumen->where('estado', 'anulada')->count(),

            'total_comprado' => $comprasValidas->sum('total'),
            'total_pagado' => $comprasValidas->sum('monto_pagado'),
            'saldo_pendiente' => $comprasValidas->sum('saldo_pendiente'),
            'total_anulado' => $comprasResumen->where('estado', 'anulada')->sum('total'),
        ];

        $compras = $comprasQuery
            ->latest('fecha_compra')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.compras', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'estado',
            'resumen',
            'compras'
        ));
    }

    public function comprasExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $estado = $request->get('estado');

        $comprasQuery = Compra::with([
                'proveedor',
                'usuario',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_compra', '>=', $fechaInicio)
            ->whereDate('fecha_compra', '<=', $fechaFin);

        if ($estado) {
            $comprasQuery->where('estado', $estado);
        }

        $compras = $comprasQuery
            ->latest('fecha_compra')
            ->get();

        $comprasValidas = $compras->where('estado', '!=', 'anulada');

        $nombreArchivo = 'reporte_compras_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($compras, $comprasValidas, $sucursal, $fechaInicio, $fechaFin, $estado) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE COMPRAS'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Estado', $estado ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Cantidad de compras', $compras->count()], ';');
            fputcsv($archivo, ['Compras pagadas', $compras->where('estado', 'pagada')->count()], ';');
            fputcsv($archivo, ['Compras pendientes', $compras->where('estado', 'pendiente')->count()], ';');
            fputcsv($archivo, ['Compras anuladas', $compras->where('estado', 'anulada')->count()], ';');
            fputcsv($archivo, ['Total comprado Bs', number_format($comprasValidas->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, ['Total pagado Bs', number_format($comprasValidas->sum('monto_pagado'), 2, '.', '')], ';');
            fputcsv($archivo, ['Saldo pendiente Bs', number_format($comprasValidas->sum('saldo_pendiente'), 2, '.', '')], ';');
            fputcsv($archivo, ['Total anulado Bs', number_format($compras->where('estado', 'anulada')->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE DE COMPRAS'], ';');
            fputcsv($archivo, [
                'Nro compra',
                'Fecha',
                'Proveedor',
                'Usuario',
                'Subtotal',
                'Descuento',
                'Total',
                'Monto pagado',
                'Saldo pendiente',
                'Estado',
                'Observación',
            ], ';');

            foreach ($compras as $compra) {
                fputcsv($archivo, [
                    $compra->numero_compra ?? $compra->id,
                    $compra->fecha_compra?->format('d/m/Y H:i'),
                    $compra->proveedor->nombre ?? '-',
                    $compra->usuario->nombre ?? '-',
                    number_format($compra->subtotal, 2, '.', ''),
                    number_format($compra->descuento, 2, '.', ''),
                    number_format($compra->total, 2, '.', ''),
                    number_format($compra->monto_pagado, 2, '.', ''),
                    number_format($compra->saldo_pendiente, 2, '.', ''),
                    $compra->estado,
                    $compra->observacion ?? '',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function deudasProveedores(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $comprasQuery = Compra::with([
                'proveedor',
                'usuario',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'pendiente')
            ->where('saldo_pendiente', '>', 0)
            ->whereDate('fecha_compra', '>=', $fechaInicio)
            ->whereDate('fecha_compra', '<=', $fechaFin);

        if ($buscar) {
            $comprasQuery->whereHas('proveedor', function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('nit', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $comprasResumen = (clone $comprasQuery)->get();

        $resumen = [
            'cantidad_deudas' => $comprasResumen->count(),
            'total_deuda' => $comprasResumen->sum('saldo_pendiente'),
            'total_comprado' => $comprasResumen->sum('total'),
            'total_pagado' => $comprasResumen->sum('monto_pagado'),
        ];

        $deudasPorProveedor = $comprasResumen
            ->groupBy('proveedor_id')
            ->map(function ($comprasProveedor) {
                $proveedor = $comprasProveedor->first()->proveedor;

                return [
                    'proveedor' => $proveedor,
                    'cantidad_compras' => $comprasProveedor->count(),
                    'total_comprado' => $comprasProveedor->sum('total'),
                    'total_pagado' => $comprasProveedor->sum('monto_pagado'),
                    'saldo_pendiente' => $comprasProveedor->sum('saldo_pendiente'),
                ];
            })
            ->sortByDesc('saldo_pendiente');

        $compras = $comprasQuery
            ->latest('fecha_compra')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.deudas-proveedores', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'buscar',
            'resumen',
            'deudasPorProveedor',
            'compras'
        ));
    }

    public function deudasProveedoresExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $comprasQuery = Compra::with([
                'proveedor',
                'usuario',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'pendiente')
            ->where('saldo_pendiente', '>', 0)
            ->whereDate('fecha_compra', '>=', $fechaInicio)
            ->whereDate('fecha_compra', '<=', $fechaFin);

        if ($buscar) {
            $comprasQuery->whereHas('proveedor', function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('nit', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $compras = $comprasQuery
            ->latest('fecha_compra')
            ->get();

        $nombreArchivo = 'deudas_proveedores_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($compras, $sucursal, $fechaInicio, $fechaFin, $buscar) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE DEUDAS A PROVEEDORES'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Compras pendientes', $compras->count()], ';');
            fputcsv($archivo, ['Total comprado Bs', number_format($compras->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, ['Total pagado Bs', number_format($compras->sum('monto_pagado'), 2, '.', '')], ';');
            fputcsv($archivo, ['Saldo pendiente Bs', number_format($compras->sum('saldo_pendiente'), 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN POR PROVEEDOR'], ';');
            fputcsv($archivo, [
                'Proveedor',
                'NIT',
                'Telefono',
                'Cantidad compras',
                'Total comprado',
                'Total pagado',
                'Saldo pendiente',
            ], ';');

            $deudasPorProveedor = $compras
                ->groupBy('proveedor_id')
                ->map(function ($comprasProveedor) {
                    return [
                        'proveedor' => $comprasProveedor->first()->proveedor,
                        'cantidad_compras' => $comprasProveedor->count(),
                        'total_comprado' => $comprasProveedor->sum('total'),
                        'total_pagado' => $comprasProveedor->sum('monto_pagado'),
                        'saldo_pendiente' => $comprasProveedor->sum('saldo_pendiente'),
                    ];
                })
                ->sortByDesc('saldo_pendiente');

            foreach ($deudasPorProveedor as $deuda) {
                fputcsv($archivo, [
                    $deuda['proveedor']->nombre ?? '-',
                    $deuda['proveedor']->nit ?? '-',
                    $deuda['proveedor']->telefono ?? '-',
                    $deuda['cantidad_compras'],
                    number_format($deuda['total_comprado'], 2, '.', ''),
                    number_format($deuda['total_pagado'], 2, '.', ''),
                    number_format($deuda['saldo_pendiente'], 2, '.', ''),
                ], ';');
            }

            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE DE COMPRAS PENDIENTES'], ';');
            fputcsv($archivo, [
                'Nro compra',
                'Fecha',
                'Proveedor',
                'Usuario',
                'Total',
                'Pagado',
                'Saldo pendiente',
                'Estado',
                'Observacion',
            ], ';');

            foreach ($compras as $compra) {
                fputcsv($archivo, [
                    $compra->numero_compra ?? $compra->id,
                    $compra->fecha_compra?->format('d/m/Y H:i'),
                    $compra->proveedor->nombre ?? '-',
                    $compra->usuario->nombre ?? '-',
                    number_format($compra->total, 2, '.', ''),
                    number_format($compra->monto_pagado, 2, '.', ''),
                    number_format($compra->saldo_pendiente, 2, '.', ''),
                    $compra->estado,
                    $compra->observacion ?? '',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function inventarioCritico(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $buscar = $request->get('buscar');
        $tipo = $request->get('tipo');

        $inventariosQuery = Inventario::with([
                'producto.laboratorio',
                'producto.presentacionPrincipal.presentacion',
                'lote',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'activo')
            ->where(function ($query) {
                $query->where('stock_actual', '<=', 0)
                    ->orWhereColumn('stock_actual', '<=', 'stock_minimo')
                    ->orWhereHas('lote', function ($loteQuery) {
                        $loteQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString());
                    });
            });

        if ($buscar) {
            $inventariosQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $inventariosResumen = (clone $inventariosQuery)->get();

        $resumen = [
            'agotados' => $inventariosResumen->where('stock_actual', '<=', 0)->count(),

            'stock_bajo' => $inventariosResumen
                ->where('stock_actual', '>', 0)
                ->filter(fn ($inventario) => $inventario->stock_actual <= $inventario->stock_minimo)
                ->count(),

            'proximos_vencer' => $inventariosResumen
                ->filter(function ($inventario) {
                    return $inventario->stock_actual > 0
                        && $inventario->lote
                        && $inventario->lote->fecha_vencimiento
                        && $inventario->lote->fecha_vencimiento->between(
                            now()->startOfDay(),
                            now()->addDays(30)->endOfDay()
                        );
                })
                ->count(),

            'vencidos' => $inventariosResumen
                ->filter(function ($inventario) {
                    return $inventario->stock_actual > 0
                        && $inventario->lote
                        && $inventario->lote->fecha_vencimiento
                        && $inventario->lote->fecha_vencimiento->lt(now()->startOfDay());
                })
                ->count(),
        ];

        if ($tipo === 'agotados') {
            $inventariosQuery->where('stock_actual', '<=', 0);
        }

        if ($tipo === 'stock_bajo') {
            $inventariosQuery->where('stock_actual', '>', 0)
                ->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        if ($tipo === 'proximos_vencer') {
            $inventariosQuery->where('stock_actual', '>', 0)
                ->whereHas('lote', function ($query) {
                    $query->whereNotNull('fecha_vencimiento')
                        ->whereBetween('fecha_vencimiento', [
                            now()->toDateString(),
                            now()->addDays(30)->toDateString(),
                        ]);
                });
        }

        if ($tipo === 'vencidos') {
            $inventariosQuery->where('stock_actual', '>', 0)
                ->whereHas('lote', function ($query) {
                    $query->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '<', now()->toDateString());
                });
        }

        $inventarios = $inventariosQuery
            ->orderBy('stock_actual')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.inventario-critico', compact(
            'sucursal',
            'buscar',
            'tipo',
            'resumen',
            'inventarios'
        ));
    }

    public function inventarioCriticoExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $buscar = $request->get('buscar');
        $tipo = $request->get('tipo');

        $inventariosQuery = Inventario::with([
                'producto.laboratorio',
                'producto.presentacionPrincipal.presentacion',
                'lote',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'activo')
            ->where(function ($query) {
                $query->where('stock_actual', '<=', 0)
                    ->orWhereColumn('stock_actual', '<=', 'stock_minimo')
                    ->orWhereHas('lote', function ($loteQuery) {
                        $loteQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString());
                    });
            });

        if ($buscar) {
            $inventariosQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        if ($tipo === 'agotados') {
            $inventariosQuery->where('stock_actual', '<=', 0);
        }

        if ($tipo === 'stock_bajo') {
            $inventariosQuery->where('stock_actual', '>', 0)
                ->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        if ($tipo === 'proximos_vencer') {
            $inventariosQuery->where('stock_actual', '>', 0)
                ->whereHas('lote', function ($query) {
                    $query->whereNotNull('fecha_vencimiento')
                        ->whereBetween('fecha_vencimiento', [
                            now()->toDateString(),
                            now()->addDays(30)->toDateString(),
                        ]);
                });
        }

        if ($tipo === 'vencidos') {
            $inventariosQuery->where('stock_actual', '>', 0)
                ->whereHas('lote', function ($query) {
                    $query->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '<', now()->toDateString());
                });
        }

        $inventarios = $inventariosQuery
            ->orderBy('stock_actual')
            ->get();

        $nombreArchivo = 'inventario_critico_' . $sucursal->id . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($inventarios, $sucursal, $buscar, $tipo) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE INVENTARIO CRITICO'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, ['Tipo', $tipo ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, [
                'Producto',
                'Generico',
                'Concentracion',
                'Laboratorio',
                'Presentacion',
                'Stock actual',
                'Stock minimo',
                'Lote',
                'Fecha vencimiento',
                'Estado alerta',
            ], ';');

            foreach ($inventarios as $inventario) {
                $fechaVencimiento = $inventario->lote?->fecha_vencimiento;

                $estadoAlerta = 'Normal';

                if ($inventario->stock_actual <= 0) {
                    $estadoAlerta = 'Agotado';
                } elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                    $estadoAlerta = 'Stock bajo';
                }

                if ($fechaVencimiento && $inventario->stock_actual > 0) {
                    if ($fechaVencimiento->lt(now()->startOfDay())) {
                        $estadoAlerta = 'Vencido';
                    } elseif ($fechaVencimiento->between(now()->startOfDay(), now()->addDays(30)->endOfDay())) {
                        $estadoAlerta = 'Proximo a vencer';
                    }
                }

                fputcsv($archivo, [
                    $inventario->producto->nombre_comercial ?? '-',
                    $inventario->producto->nombre_generico ?? '-',
                    $inventario->producto->concentracion ?? '-',
                    $inventario->producto->laboratorio->nombre ?? '-',
                    $inventario->producto->presentacionPrincipal->presentacion->nombre ?? '-',
                    $inventario->stock_actual,
                    $inventario->stock_minimo,
                    $inventario->lote->numero_lote ?? '-',
                    $fechaVencimiento?->format('d/m/Y') ?? '-',
                    $estadoAlerta,
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function movimientosInventario(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $tipo = $request->get('tipo');
        $buscar = $request->get('buscar');

        $movimientosQuery = MovimientoInventario::with([
                'producto.laboratorio',
                'sucursal',
                'usuario',
                'lote',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin);

        if ($tipo) {
            $movimientosQuery->where('tipo_movimiento', $tipo);
        }

        if ($buscar) {
            $movimientosQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $movimientosResumen = (clone $movimientosQuery)->get();

        $resumen = [
            'total_movimientos' => $movimientosResumen->count(),
            'entradas' => $movimientosResumen->where('tipo_movimiento', 'entrada')->sum('cantidad'),
            'salidas' => $movimientosResumen->where('tipo_movimiento', 'salida')->sum('cantidad'),
            'ajustes' => $movimientosResumen->where('tipo_movimiento', 'ajuste')->sum('cantidad'),
        ];

        $movimientos = $movimientosQuery
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reportes.movimientos-inventario', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'tipo',
            'buscar',
            'resumen',
            'movimientos'
        ));
    }

    public function movimientosInventarioExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $tipo = $request->get('tipo');
        $buscar = $request->get('buscar');

        $movimientosQuery = MovimientoInventario::with([
                'producto.laboratorio',
                'sucursal',
                'usuario',
                'lote',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin);

        if ($tipo) {
            $movimientosQuery->where('tipo_movimiento', $tipo);
        }

        if ($buscar) {
            $movimientosQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $movimientos = $movimientosQuery
            ->latest()
            ->get();

        $nombreArchivo = 'movimientos_inventario_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($movimientos, $sucursal, $fechaInicio, $fechaFin, $tipo, $buscar) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE MOVIMIENTOS DE INVENTARIO'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Tipo', $tipo ?: 'Todos'], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Total movimientos', $movimientos->count()], ';');
            fputcsv($archivo, ['Entradas', $movimientos->where('tipo_movimiento', 'entrada')->sum('cantidad')], ';');
            fputcsv($archivo, ['Salidas', $movimientos->where('tipo_movimiento', 'salida')->sum('cantidad')], ';');
            fputcsv($archivo, ['Ajustes', $movimientos->where('tipo_movimiento', 'ajuste')->sum('cantidad')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE'], ';');
            fputcsv($archivo, [
                'Fecha',
                'Producto',
                'Generico',
                'Concentracion',
                'Laboratorio',
                'Lote',
                'Tipo movimiento',
                'Cantidad',
                'Stock anterior',
                'Stock nuevo',
                'Usuario',
                'Motivo',
                'Referencia',
            ], ';');

            foreach ($movimientos as $movimiento) {
                fputcsv($archivo, [
                    $movimiento->created_at?->format('d/m/Y H:i'),
                    $movimiento->producto->nombre_comercial ?? '-',
                    $movimiento->producto->nombre_generico ?? '-',
                    $movimiento->producto->concentracion ?? '-',
                    $movimiento->producto->laboratorio->nombre ?? '-',
                    $movimiento->lote->numero_lote ?? '-',
                    $movimiento->tipo_movimiento,
                    $movimiento->cantidad,
                    $movimiento->stock_anterior,
                    $movimiento->stock_nuevo,
                    $movimiento->usuario->nombre ?? '-',
                    $movimiento->motivo ?? '',
                    $movimiento->referencia ?? '',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function productosVendidos(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $detallesQuery = DetalleVenta::with([
                'venta',
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            });

        if ($buscar) {
            $detallesQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $detalles = $detallesQuery->get();

        $productosVendidos = $detalles
            ->groupBy(function ($detalle) {
                return $detalle->producto_id . '-' . $detalle->producto_presentacion_id;
            })
            ->map(function ($items) {
                $primerItem = $items->first();

                return [
                    'producto' => $primerItem->producto,
                    'presentacion' => $primerItem->productoPresentacion,
                    'cantidad_vendida' => $items->sum('cantidad'),
                    'unidades_vendidas' => $items->sum('unidades_descontadas'),
                    'subtotal' => $items->sum('subtotal'),
                    'descuento' => $items->sum('descuento'),
                    'total' => $items->sum('total'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $resumen = [
            'productos_distintos' => $productosVendidos->count(),
            'cantidad_vendida' => $productosVendidos->sum('cantidad_vendida'),
            'unidades_vendidas' => $productosVendidos->sum('unidades_vendidas'),
            'total_generado' => $productosVendidos->sum('total'),
        ];

        $productosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $productosVendidos->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 15),
            $productosVendidos->count(),
            15,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('reportes.productos-vendidos', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'buscar',
            'resumen',
            'productosPaginados'
        ));
    }

    public function productosVendidosExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $detallesQuery = DetalleVenta::with([
                'venta',
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            });

        if ($buscar) {
            $detallesQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $detalles = $detallesQuery->get();

        $productosVendidos = $detalles
            ->groupBy(function ($detalle) {
                return $detalle->producto_id . '-' . $detalle->producto_presentacion_id;
            })
            ->map(function ($items) {
                $primerItem = $items->first();

                return [
                    'producto' => $primerItem->producto,
                    'presentacion' => $primerItem->productoPresentacion,
                    'cantidad_vendida' => $items->sum('cantidad'),
                    'unidades_vendidas' => $items->sum('unidades_descontadas'),
                    'subtotal' => $items->sum('subtotal'),
                    'descuento' => $items->sum('descuento'),
                    'total' => $items->sum('total'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $nombreArchivo = 'productos_vendidos_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($productosVendidos, $sucursal, $fechaInicio, $fechaFin, $buscar) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE PRODUCTOS VENDIDOS'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Productos distintos', $productosVendidos->count()], ';');
            fputcsv($archivo, ['Cantidad vendida', $productosVendidos->sum('cantidad_vendida')], ';');
            fputcsv($archivo, ['Unidades reales descontadas', $productosVendidos->sum('unidades_vendidas')], ';');
            fputcsv($archivo, ['Total generado Bs', number_format($productosVendidos->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE'], ';');
            fputcsv($archivo, [
                'Producto',
                'Generico',
                'Concentracion',
                'Laboratorio',
                'Presentacion vendida',
                'Cantidad vendida',
                'Unidades reales descontadas',
                'Subtotal',
                'Descuento',
                'Total',
            ], ';');

            foreach ($productosVendidos as $item) {
                fputcsv($archivo, [
                    $item['producto']->nombre_comercial ?? '-',
                    $item['producto']->nombre_generico ?? '-',
                    $item['producto']->concentracion ?? '-',
                    $item['producto']->laboratorio->nombre ?? '-',
                    $item['presentacion']->presentacion->nombre ?? '-',
                    $item['cantidad_vendida'],
                    $item['unidades_vendidas'],
                    number_format($item['subtotal'], 2, '.', ''),
                    number_format($item['descuento'], 2, '.', ''),
                    number_format($item['total'], 2, '.', ''),
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function clientesFrecuentes(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $ventasQuery = Venta::with([
                'cliente',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereNotNull('cliente_id')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin);

        if ($buscar) {
            $ventasQuery->whereHas('cliente', function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('ci_nit', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $ventas = $ventasQuery->get();

        $clientesFrecuentes = $ventas
            ->groupBy('cliente_id')
            ->map(function ($ventasCliente) {
                $cliente = $ventasCliente->first()->cliente;
                $totalComprado = $ventasCliente->sum('total');
                $cantidadCompras = $ventasCliente->count();

                return [
                    'cliente' => $cliente,
                    'cantidad_compras' => $cantidadCompras,
                    'total_comprado' => $totalComprado,
                    'ticket_promedio' => $cantidadCompras > 0 ? $totalComprado / $cantidadCompras : 0,
                    'ultima_compra' => $ventasCliente->sortByDesc('fecha_hora')->first()->fecha_hora,
                ];
            })
            ->sortByDesc('total_comprado')
            ->values();

        $resumen = [
            'clientes_distintos' => $clientesFrecuentes->count(),
            'ventas_con_cliente' => $ventas->count(),
            'total_comprado' => $clientesFrecuentes->sum('total_comprado'),
            'ticket_promedio_general' => $ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0,
        ];

        $clientesPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $clientesFrecuentes->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 15),
            $clientesFrecuentes->count(),
            15,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('reportes.clientes-frecuentes', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'buscar',
            'resumen',
            'clientesPaginados'
        ));
    }

    public function clientesFrecuentesExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $ventasQuery = Venta::with([
                'cliente',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereNotNull('cliente_id')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin);

        if ($buscar) {
            $ventasQuery->whereHas('cliente', function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('ci_nit', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $ventas = $ventasQuery->get();

        $clientesFrecuentes = $ventas
            ->groupBy('cliente_id')
            ->map(function ($ventasCliente) {
                $cliente = $ventasCliente->first()->cliente;
                $totalComprado = $ventasCliente->sum('total');
                $cantidadCompras = $ventasCliente->count();

                return [
                    'cliente' => $cliente,
                    'cantidad_compras' => $cantidadCompras,
                    'total_comprado' => $totalComprado,
                    'ticket_promedio' => $cantidadCompras > 0 ? $totalComprado / $cantidadCompras : 0,
                    'ultima_compra' => $ventasCliente->sortByDesc('fecha_hora')->first()->fecha_hora,
                ];
            })
            ->sortByDesc('total_comprado')
            ->values();

        $nombreArchivo = 'clientes_frecuentes_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($clientesFrecuentes, $ventas, $sucursal, $fechaInicio, $fechaFin, $buscar) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE CLIENTES FRECUENTES'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Clientes distintos', $clientesFrecuentes->count()], ';');
            fputcsv($archivo, ['Ventas con cliente', $ventas->count()], ';');
            fputcsv($archivo, ['Total comprado Bs', number_format($clientesFrecuentes->sum('total_comprado'), 2, '.', '')], ';');
            fputcsv($archivo, ['Ticket promedio general Bs', number_format($ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0, 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE DE CLIENTES'], ';');
            fputcsv($archivo, [
                'Cliente',
                'CI/NIT',
                'Telefono',
                'Cantidad compras',
                'Total comprado',
                'Ticket promedio',
                'Ultima compra',
            ], ';');

            foreach ($clientesFrecuentes as $item) {
                fputcsv($archivo, [
                    $item['cliente']->nombre ?? '-',
                    $item['cliente']->ci_nit ?? '-',
                    $item['cliente']->telefono ?? '-',
                    $item['cantidad_compras'],
                    number_format($item['total_comprado'], 2, '.', ''),
                    number_format($item['ticket_promedio'], 2, '.', ''),
                    $item['ultima_compra']?->format('d/m/Y H:i') ?? '-',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function productosReponer(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $dias = (int) $request->get('dias', 30);
        $buscar = $request->get('buscar');

        if ($dias <= 0) {
            $dias = 30;
        }

        $fechaInicio = now()->subDays($dias)->startOfDay();
        $fechaFin = now()->endOfDay();

        $inventariosQuery = Inventario::with([
                'producto.laboratorio',
                'producto.presentacionPrincipal.presentacion',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'activo')
            ->where(function ($query) {
                $query->where('stock_actual', '<=', 0)
                    ->orWhereColumn('stock_actual', '<=', 'stock_minimo');
            });

        if ($buscar) {
            $inventariosQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $inventarios = $inventariosQuery->get();

        $productosReponer = $inventarios->map(function ($inventario) use ($sucursal, $fechaInicio, $fechaFin, $dias) {
            $unidadesVendidas = DetalleVenta::where('producto_id', $inventario->producto_id)
                ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                    $query->where('sucursal_id', $sucursal->id)
                        ->where('estado', 'completada')
                        ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);
                })
                ->sum('unidades_descontadas');

            $promedioDiario = $dias > 0 ? $unidadesVendidas / $dias : 0;

            $cantidadSugerida = max(
                0,
                (int) ceil(($inventario->stock_minimo * 2) - $inventario->stock_actual)
            );

            $prioridad = 'Media';

            if ($inventario->stock_actual <= 0 && $unidadesVendidas > 0) {
                $prioridad = 'Alta';
            } elseif ($inventario->stock_actual <= 0) {
                $prioridad = 'Alta';
            } elseif ($inventario->stock_actual <= $inventario->stock_minimo && $unidadesVendidas > 0) {
                $prioridad = 'Media';
            } else {
                $prioridad = 'Baja';
            }

            return [
                'inventario' => $inventario,
                'unidades_vendidas' => $unidadesVendidas,
                'promedio_diario' => $promedioDiario,
                'cantidad_sugerida' => $cantidadSugerida,
                'prioridad' => $prioridad,
            ];
        })
        ->sortByDesc(function ($item) {
            return match ($item['prioridad']) {
                'Alta' => 3,
                'Media' => 2,
                default => 1,
            };
        })
        ->values();

        $resumen = [
            'productos_reponer' => $productosReponer->count(),
            'agotados' => $productosReponer->filter(fn ($item) => $item['inventario']->stock_actual <= 0)->count(),
            'stock_bajo' => $productosReponer->filter(fn ($item) => $item['inventario']->stock_actual > 0)->count(),
            'cantidad_sugerida_total' => $productosReponer->sum('cantidad_sugerida'),
        ];

        $productosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $productosReponer->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 15),
            $productosReponer->count(),
            15,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('reportes.productos-reponer', compact(
            'sucursal',
            'dias',
            'buscar',
            'resumen',
            'productosPaginados'
        ));
    }

    public function productosReponerExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $dias = (int) $request->get('dias', 30);
        $buscar = $request->get('buscar');

        if ($dias <= 0) {
            $dias = 30;
        }

        $fechaInicio = now()->subDays($dias)->startOfDay();
        $fechaFin = now()->endOfDay();

        $inventariosQuery = Inventario::with([
                'producto.laboratorio',
                'producto.presentacionPrincipal.presentacion',
                'sucursal',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'activo')
            ->where(function ($query) {
                $query->where('stock_actual', '<=', 0)
                    ->orWhereColumn('stock_actual', '<=', 'stock_minimo');
            });

        if ($buscar) {
            $inventariosQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $inventarios = $inventariosQuery->get();

        $productosReponer = $inventarios->map(function ($inventario) use ($sucursal, $fechaInicio, $fechaFin, $dias) {
            $unidadesVendidas = DetalleVenta::where('producto_id', $inventario->producto_id)
                ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                    $query->where('sucursal_id', $sucursal->id)
                        ->where('estado', 'completada')
                        ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);
                })
                ->sum('unidades_descontadas');

            $promedioDiario = $dias > 0 ? $unidadesVendidas / $dias : 0;

            $cantidadSugerida = max(
                0,
                (int) ceil(($inventario->stock_minimo * 2) - $inventario->stock_actual)
            );

            $prioridad = 'Media';

            if ($inventario->stock_actual <= 0) {
                $prioridad = 'Alta';
            } elseif ($inventario->stock_actual <= $inventario->stock_minimo && $unidadesVendidas > 0) {
                $prioridad = 'Media';
            } else {
                $prioridad = 'Baja';
            }

            return [
                'inventario' => $inventario,
                'unidades_vendidas' => $unidadesVendidas,
                'promedio_diario' => $promedioDiario,
                'cantidad_sugerida' => $cantidadSugerida,
                'prioridad' => $prioridad,
            ];
        })
        ->sortByDesc(function ($item) {
            return match ($item['prioridad']) {
                'Alta' => 3,
                'Media' => 2,
                default => 1,
            };
        })
        ->values();

        $nombreArchivo = 'productos_reponer_' . $sucursal->id . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($productosReponer, $sucursal, $dias, $buscar) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE PRODUCTOS PROXIMOS A REPONER'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Periodo analizado', $dias . ' dias'], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE'], ';');
            fputcsv($archivo, [
                'Producto',
                'Generico',
                'Concentracion',
                'Laboratorio',
                'Presentacion base',
                'Stock actual',
                'Stock minimo',
                'Unidades vendidas periodo',
                'Promedio diario',
                'Cantidad sugerida',
                'Prioridad',
            ], ';');

            foreach ($productosReponer as $item) {
                $inventario = $item['inventario'];

                fputcsv($archivo, [
                    $inventario->producto->nombre_comercial ?? '-',
                    $inventario->producto->nombre_generico ?? '-',
                    $inventario->producto->concentracion ?? '-',
                    $inventario->producto->laboratorio->nombre ?? '-',
                    $inventario->producto->presentacionPrincipal->presentacion->nombre ?? '-',
                    $inventario->stock_actual,
                    $inventario->stock_minimo,
                    $item['unidades_vendidas'],
                    number_format($item['promedio_diario'], 2, '.', ''),
                    $item['cantidad_sugerida'],
                    $item['prioridad'],
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function utilidadEstimada(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $detallesQuery = DetalleVenta::with([
                'venta',
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            });

        if ($buscar) {
            $detallesQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $detalles = $detallesQuery->get();

        $utilidades = $detalles
            ->groupBy(function ($detalle) {
                return $detalle->producto_id . '-' . $detalle->producto_presentacion_id;
            })
            ->map(function ($items) {
                $primerItem = $items->first();

                $cantidadVendida = $items->sum('cantidad');
                $unidadesVendidas = $items->sum('unidades_descontadas');
                $totalVenta = $items->sum('total');
                $descuento = $items->sum('descuento');

                $precioCompraPresentacion = (float) ($primerItem->productoPresentacion->precio_compra ?? 0);
                $unidadesEquivalentes = max((int) ($primerItem->productoPresentacion->unidades_equivalentes ?? 1), 1);

                $costoUnitarioReal = $precioCompraPresentacion / $unidadesEquivalentes;
                $costoEstimado = $costoUnitarioReal * $unidadesVendidas;

                $utilidadEstimada = $totalVenta - $costoEstimado;
                $margen = $totalVenta > 0 ? ($utilidadEstimada / $totalVenta) * 100 : 0;

                return [
                    'producto' => $primerItem->producto,
                    'presentacion' => $primerItem->productoPresentacion,
                    'cantidad_vendida' => $cantidadVendida,
                    'unidades_vendidas' => $unidadesVendidas,
                    'total_venta' => $totalVenta,
                    'descuento' => $descuento,
                    'costo_estimado' => $costoEstimado,
                    'utilidad_estimada' => $utilidadEstimada,
                    'margen' => $margen,
                    'precio_compra_presentacion' => $precioCompraPresentacion,
                ];
            })
            ->sortByDesc('utilidad_estimada')
            ->values();

        $resumen = [
            'productos_distintos' => $utilidades->count(),
            'total_venta' => $utilidades->sum('total_venta'),
            'costo_estimado' => $utilidades->sum('costo_estimado'),
            'utilidad_estimada' => $utilidades->sum('utilidad_estimada'),
            'margen_general' => $utilidades->sum('total_venta') > 0
                ? ($utilidades->sum('utilidad_estimada') / $utilidades->sum('total_venta')) * 100
                : 0,
        ];

        $utilidadesPaginadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $utilidades->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 15),
            $utilidades->count(),
            15,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('reportes.utilidad-estimada', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'buscar',
            'resumen',
            'utilidadesPaginadas'
        ));
    }

    public function utilidadEstimadaExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $buscar = $request->get('buscar');

        $detallesQuery = DetalleVenta::with([
                'venta',
                'producto.laboratorio',
                'productoPresentacion.presentacion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            });

        if ($buscar) {
            $detallesQuery->whereHas('producto', function ($query) use ($buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                    ->orWhere('concentracion', 'like', "%{$buscar}%")
                    ->orWhereHas('laboratorio', function ($labQuery) use ($buscar) {
                        $labQuery->where('nombre', 'like', "%{$buscar}%");
                    });
            });
        }

        $detalles = $detallesQuery->get();

        $utilidades = $detalles
            ->groupBy(function ($detalle) {
                return $detalle->producto_id . '-' . $detalle->producto_presentacion_id;
            })
            ->map(function ($items) {
                $primerItem = $items->first();

                $cantidadVendida = $items->sum('cantidad');
                $unidadesVendidas = $items->sum('unidades_descontadas');
                $totalVenta = $items->sum('total');
                $descuento = $items->sum('descuento');

                $precioCompraPresentacion = (float) ($primerItem->productoPresentacion->precio_compra ?? 0);
                $unidadesEquivalentes = max((int) ($primerItem->productoPresentacion->unidades_equivalentes ?? 1), 1);

                $costoUnitarioReal = $precioCompraPresentacion / $unidadesEquivalentes;
                $costoEstimado = $costoUnitarioReal * $unidadesVendidas;

                $utilidadEstimada = $totalVenta - $costoEstimado;
                $margen = $totalVenta > 0 ? ($utilidadEstimada / $totalVenta) * 100 : 0;

                return [
                    'producto' => $primerItem->producto,
                    'presentacion' => $primerItem->productoPresentacion,
                    'cantidad_vendida' => $cantidadVendida,
                    'unidades_vendidas' => $unidadesVendidas,
                    'total_venta' => $totalVenta,
                    'descuento' => $descuento,
                    'costo_estimado' => $costoEstimado,
                    'utilidad_estimada' => $utilidadEstimada,
                    'margen' => $margen,
                    'precio_compra_presentacion' => $precioCompraPresentacion,
                ];
            })
            ->sortByDesc('utilidad_estimada')
            ->values();

        $nombreArchivo = 'utilidad_estimada_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($utilidades, $sucursal, $fechaInicio, $fechaFin, $buscar) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE UTILIDAD ESTIMADA'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, ['Busqueda', $buscar ?: 'Todos'], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Productos distintos', $utilidades->count()], ';');
            fputcsv($archivo, ['Total venta Bs', number_format($utilidades->sum('total_venta'), 2, '.', '')], ';');
            fputcsv($archivo, ['Costo estimado Bs', number_format($utilidades->sum('costo_estimado'), 2, '.', '')], ';');
            fputcsv($archivo, ['Utilidad estimada Bs', number_format($utilidades->sum('utilidad_estimada'), 2, '.', '')], ';');
            fputcsv($archivo, ['Margen general %', number_format($utilidades->sum('total_venta') > 0 ? ($utilidades->sum('utilidad_estimada') / $utilidades->sum('total_venta')) * 100 : 0, 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['DETALLE'], ';');
            fputcsv($archivo, [
                'Producto',
                'Generico',
                'Concentracion',
                'Laboratorio',
                'Presentacion',
                'Cantidad vendida',
                'Unidades reales vendidas',
                'Precio compra presentacion',
                'Total venta',
                'Descuento',
                'Costo estimado',
                'Utilidad estimada',
                'Margen %',
            ], ';');

            foreach ($utilidades as $item) {
                fputcsv($archivo, [
                    $item['producto']->nombre_comercial ?? '-',
                    $item['producto']->nombre_generico ?? '-',
                    $item['producto']->concentracion ?? '-',
                    $item['producto']->laboratorio->nombre ?? '-',
                    $item['presentacion']->presentacion->nombre ?? '-',
                    $item['cantidad_vendida'],
                    $item['unidades_vendidas'],
                    number_format($item['precio_compra_presentacion'], 2, '.', ''),
                    number_format($item['total_venta'], 2, '.', ''),
                    number_format($item['descuento'], 2, '.', ''),
                    number_format($item['costo_estimado'], 2, '.', ''),
                    number_format($item['utilidad_estimada'], 2, '.', ''),
                    number_format($item['margen'], 2, '.', ''),
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function metodosPago(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $metodoPagoId = $request->get('metodo_pago_id');

        $metodosPago = MetodoPago::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $pagosVentasQuery = PagoVenta::with([
                'venta',
                'metodoPago',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            });

        if ($metodoPagoId) {
            $pagosVentasQuery->where('metodo_pago_id', $metodoPagoId);
        }

        $pagosVentas = $pagosVentasQuery->get();

        $serviciosQuery = AtencionServicio::with([
                'servicio',
                'metodoPago',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin);

        if ($metodoPagoId) {
            $serviciosQuery->where('metodo_pago_id', $metodoPagoId);
        }

        $atencionesServicios = $serviciosQuery->get();

        $resumenVentas = $pagosVentas
            ->groupBy('metodo_pago_id')
            ->map(function ($pagos) {
                $metodo = $pagos->first()->metodoPago;

                return [
                    'metodo' => $metodo,
                    'cantidad_ventas' => $pagos->count(),
                    'total_ventas' => $pagos->sum('monto'),
                    'cantidad_servicios' => 0,
                    'total_servicios' => 0,
                ];
            });

        $resumenServicios = $atencionesServicios
            ->groupBy('metodo_pago_id')
            ->map(function ($servicios) {
                $metodo = $servicios->first()->metodoPago;

                return [
                    'metodo' => $metodo,
                    'cantidad_ventas' => 0,
                    'total_ventas' => 0,
                    'cantidad_servicios' => $servicios->count(),
                    'total_servicios' => $servicios->sum('total'),
                ];
            });

        $resumenPorMetodo = collect();

        foreach ($resumenVentas as $metodoId => $item) {
            $resumenPorMetodo[$metodoId] = $item;
        }

        foreach ($resumenServicios as $metodoId => $item) {
            if (isset($resumenPorMetodo[$metodoId])) {
                $resumenPorMetodo[$metodoId]['cantidad_servicios'] = $item['cantidad_servicios'];
                $resumenPorMetodo[$metodoId]['total_servicios'] = $item['total_servicios'];
            } else {
                $resumenPorMetodo[$metodoId] = $item;
            }
        }

        $resumenPorMetodo = $resumenPorMetodo
            ->map(function ($item) {
                $item['total_general'] = $item['total_ventas'] + $item['total_servicios'];
                $item['cantidad_general'] = $item['cantidad_ventas'] + $item['cantidad_servicios'];

                return $item;
            })
            ->sortByDesc('total_general')
            ->values();

        $resumen = [
            'total_ventas' => $pagosVentas->sum('monto'),
            'total_servicios' => $atencionesServicios->sum('total'),
            'total_general' => $pagosVentas->sum('monto') + $atencionesServicios->sum('total'),
            'cantidad_ventas' => $pagosVentas->count(),
            'cantidad_servicios' => $atencionesServicios->count(),
        ];

        return view('reportes.metodos-pago', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'metodoPagoId',
            'metodosPago',
            'resumen',
            'resumenPorMetodo'
        ));
    }

    public function metodosPagoExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $metodoPagoId = $request->get('metodo_pago_id');

        $pagosVentasQuery = PagoVenta::with([
                'venta',
                'metodoPago',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            });

        if ($metodoPagoId) {
            $pagosVentasQuery->where('metodo_pago_id', $metodoPagoId);
        }

        $pagosVentas = $pagosVentasQuery->get();

        $serviciosQuery = AtencionServicio::with([
                'servicio',
                'metodoPago',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin);

        if ($metodoPagoId) {
            $serviciosQuery->where('metodo_pago_id', $metodoPagoId);
        }

        $atencionesServicios = $serviciosQuery->get();

        $resumenVentas = $pagosVentas
            ->groupBy('metodo_pago_id')
            ->map(function ($pagos) {
                $metodo = $pagos->first()->metodoPago;

                return [
                    'metodo' => $metodo,
                    'cantidad_ventas' => $pagos->count(),
                    'total_ventas' => $pagos->sum('monto'),
                    'cantidad_servicios' => 0,
                    'total_servicios' => 0,
                ];
            });

        $resumenServicios = $atencionesServicios
            ->groupBy('metodo_pago_id')
            ->map(function ($servicios) {
                $metodo = $servicios->first()->metodoPago;

                return [
                    'metodo' => $metodo,
                    'cantidad_ventas' => 0,
                    'total_ventas' => 0,
                    'cantidad_servicios' => $servicios->count(),
                    'total_servicios' => $servicios->sum('total'),
                ];
            });

        $resumenPorMetodo = collect();

        foreach ($resumenVentas as $metodoId => $item) {
            $resumenPorMetodo[$metodoId] = $item;
        }

        foreach ($resumenServicios as $metodoId => $item) {
            if (isset($resumenPorMetodo[$metodoId])) {
                $resumenPorMetodo[$metodoId]['cantidad_servicios'] = $item['cantidad_servicios'];
                $resumenPorMetodo[$metodoId]['total_servicios'] = $item['total_servicios'];
            } else {
                $resumenPorMetodo[$metodoId] = $item;
            }
        }

        $resumenPorMetodo = $resumenPorMetodo
            ->map(function ($item) {
                $item['total_general'] = $item['total_ventas'] + $item['total_servicios'];
                $item['cantidad_general'] = $item['cantidad_ventas'] + $item['cantidad_servicios'];

                return $item;
            })
            ->sortByDesc('total_general')
            ->values();

        $nombreArchivo = 'metodos_pago_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($resumenPorMetodo, $pagosVentas, $atencionesServicios, $sucursal, $fechaInicio, $fechaFin) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE METODOS DE PAGO'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN GENERAL'], ';');
            fputcsv($archivo, ['Concepto', 'Valor'], ';');
            fputcsv($archivo, ['Ventas Bs', number_format($pagosVentas->sum('monto'), 2, '.', '')], ';');
            fputcsv($archivo, ['Servicios Bs', number_format($atencionesServicios->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, ['Total Bs', number_format($pagosVentas->sum('monto') + $atencionesServicios->sum('total'), 2, '.', '')], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['RESUMEN POR METODO'], ';');
            fputcsv($archivo, [
                'Metodo',
                'Tipo',
                'Cantidad ventas',
                'Total ventas',
                'Cantidad servicios',
                'Total servicios',
                'Cantidad total',
                'Total general',
            ], ';');

            foreach ($resumenPorMetodo as $item) {
                fputcsv($archivo, [
                    $item['metodo']->nombre ?? '-',
                    $item['metodo']->tipo ?? '-',
                    $item['cantidad_ventas'],
                    number_format($item['total_ventas'], 2, '.', ''),
                    $item['cantidad_servicios'],
                    number_format($item['total_servicios'], 2, '.', ''),
                    $item['cantidad_general'],
                    number_format($item['total_general'], 2, '.', ''),
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function resumenAdministrativo(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        /*
        |--------------------------------------------------------------------------
        | Ventas
        |--------------------------------------------------------------------------
        */

        $ventasCompletadas = Venta::where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        $ventasAnuladas = Venta::where('sucursal_id', $sucursal->id)
            ->where('estado', 'anulada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Servicios
        |--------------------------------------------------------------------------
        */

        $serviciosCompletados = AtencionServicio::where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        $serviciosAnulados = AtencionServicio::where('sucursal_id', $sucursal->id)
            ->where('estado', 'anulada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Compras
        |--------------------------------------------------------------------------
        */

        $compras = Compra::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_compra', '>=', $fechaInicio)
            ->whereDate('fecha_compra', '<=', $fechaFin)
            ->get();

        $comprasValidas = $compras->where('estado', '!=', 'anulada');

        /*
        |--------------------------------------------------------------------------
        | Utilidad estimada
        |--------------------------------------------------------------------------
        */

        $detallesVenta = DetalleVenta::with([
                'venta',
                'productoPresentacion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->get();

        $costoEstimado = $detallesVenta->sum(function ($detalle) {
            $precioCompraPresentacion = (float) ($detalle->productoPresentacion->precio_compra ?? 0);
            $unidadesEquivalentes = max((int) ($detalle->productoPresentacion->unidades_equivalentes ?? 1), 1);
            $costoUnitarioReal = $precioCompraPresentacion / $unidadesEquivalentes;

            return $costoUnitarioReal * (float) ($detalle->unidades_descontadas ?? 0);
        });

        $totalVenta = $ventasCompletadas->sum('total');
        $utilidadEstimada = $totalVenta - $costoEstimado;

        /*
        |--------------------------------------------------------------------------
        | Caja
        |--------------------------------------------------------------------------
        */

        $cajas = Caja::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_apertura', '>=', $fechaInicio)
            ->whereDate('fecha_apertura', '<=', $fechaFin)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Inventario crítico
        |--------------------------------------------------------------------------
        */

        $inventariosCriticos = Inventario::with(['lote'])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'activo')
            ->where(function ($query) {
                $query->where('stock_actual', '<=', 0)
                    ->orWhereColumn('stock_actual', '<=', 'stock_minimo')
                    ->orWhereHas('lote', function ($loteQuery) {
                        $loteQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString());
                    });
            })
            ->get();

        $resumen = [
            'ventas_completadas' => $ventasCompletadas->count(),
            'ventas_anuladas' => $ventasAnuladas->count(),
            'ingresos_ventas' => $ventasCompletadas->sum('total'),

            'servicios_completados' => $serviciosCompletados->count(),
            'servicios_anulados' => $serviciosAnulados->count(),
            'ingresos_servicios' => $serviciosCompletados->sum('total'),

            'ingresos_totales' => $ventasCompletadas->sum('total') + $serviciosCompletados->sum('total'),

            'compras_registradas' => $compras->count(),
            'compras_anuladas' => $compras->where('estado', 'anulada')->count(),
            'total_compras' => $comprasValidas->sum('total'),
            'deuda_proveedores' => $comprasValidas->sum('saldo_pendiente'),

            'costo_estimado' => $costoEstimado,
            'utilidad_estimada' => $utilidadEstimada,
            'margen_estimado' => $totalVenta > 0 ? ($utilidadEstimada / $totalVenta) * 100 : 0,

            'cajas_abiertas' => $cajas->where('estado', 'abierta')->count(),
            'cajas_cerradas' => $cajas->where('estado', 'cerrada')->count(),
            'total_caja' => $cajas->sum('total_final'),

            'productos_criticos' => $inventariosCriticos->count(),
            'productos_agotados' => $inventariosCriticos->where('stock_actual', '<=', 0)->count(),
            'stock_bajo' => $inventariosCriticos
                ->where('stock_actual', '>', 0)
                ->filter(fn ($inventario) => $inventario->stock_actual <= $inventario->stock_minimo)
                ->count(),
        ];

        return view('reportes.resumen-administrativo', compact(
            'sucursal',
            'fechaInicio',
            'fechaFin',
            'resumen'
        ));
    }

    public function resumenAdministrativoExportarCsv(Request $request)
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $ventasCompletadas = Venta::where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        $ventasAnuladas = Venta::where('sucursal_id', $sucursal->id)
            ->where('estado', 'anulada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        $serviciosCompletados = AtencionServicio::where('sucursal_id', $sucursal->id)
            ->where('estado', 'completada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        $serviciosAnulados = AtencionServicio::where('sucursal_id', $sucursal->id)
            ->where('estado', 'anulada')
            ->whereDate('fecha_hora', '>=', $fechaInicio)
            ->whereDate('fecha_hora', '<=', $fechaFin)
            ->get();

        $compras = Compra::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_compra', '>=', $fechaInicio)
            ->whereDate('fecha_compra', '<=', $fechaFin)
            ->get();

        $comprasValidas = $compras->where('estado', '!=', 'anulada');

        $detallesVenta = DetalleVenta::with([
                'venta',
                'productoPresentacion',
            ])
            ->whereHas('venta', function ($query) use ($sucursal, $fechaInicio, $fechaFin) {
                $query->where('sucursal_id', $sucursal->id)
                    ->where('estado', 'completada')
                    ->whereDate('fecha_hora', '>=', $fechaInicio)
                    ->whereDate('fecha_hora', '<=', $fechaFin);
            })
            ->get();

        $costoEstimado = $detallesVenta->sum(function ($detalle) {
            $precioCompraPresentacion = (float) ($detalle->productoPresentacion->precio_compra ?? 0);
            $unidadesEquivalentes = max((int) ($detalle->productoPresentacion->unidades_equivalentes ?? 1), 1);
            $costoUnitarioReal = $precioCompraPresentacion / $unidadesEquivalentes;

            return $costoUnitarioReal * (float) ($detalle->unidades_descontadas ?? 0);
        });

        $totalVenta = $ventasCompletadas->sum('total');
        $utilidadEstimada = $totalVenta - $costoEstimado;

        $cajas = Caja::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_apertura', '>=', $fechaInicio)
            ->whereDate('fecha_apertura', '<=', $fechaFin)
            ->get();

        $inventariosCriticos = Inventario::with(['lote'])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'activo')
            ->where(function ($query) {
                $query->where('stock_actual', '<=', 0)
                    ->orWhereColumn('stock_actual', '<=', 'stock_minimo')
                    ->orWhereHas('lote', function ($loteQuery) {
                        $loteQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString());
                    });
            })
            ->get();

        $resumen = [
            'Ventas completadas' => $ventasCompletadas->count(),
            'Ventas anuladas' => $ventasAnuladas->count(),
            'Ingresos por ventas Bs' => $ventasCompletadas->sum('total'),

            'Servicios completados' => $serviciosCompletados->count(),
            'Servicios anulados' => $serviciosAnulados->count(),
            'Ingresos por servicios Bs' => $serviciosCompletados->sum('total'),

            'Ingresos totales Bs' => $ventasCompletadas->sum('total') + $serviciosCompletados->sum('total'),

            'Compras registradas' => $compras->count(),
            'Compras anuladas' => $compras->where('estado', 'anulada')->count(),
            'Total compras Bs' => $comprasValidas->sum('total'),
            'Deuda proveedores Bs' => $comprasValidas->sum('saldo_pendiente'),

            'Costo estimado Bs' => $costoEstimado,
            'Utilidad estimada Bs' => $utilidadEstimada,
            'Margen estimado %' => $totalVenta > 0 ? ($utilidadEstimada / $totalVenta) * 100 : 0,

            'Cajas abiertas' => $cajas->where('estado', 'abierta')->count(),
            'Cajas cerradas' => $cajas->where('estado', 'cerrada')->count(),
            'Total caja Bs' => $cajas->sum('total_final'),

            'Productos criticos' => $inventariosCriticos->count(),
            'Productos agotados' => $inventariosCriticos->where('stock_actual', '<=', 0)->count(),
            'Stock bajo' => $inventariosCriticos
                ->where('stock_actual', '>', 0)
                ->filter(fn ($inventario) => $inventario->stock_actual <= $inventario->stock_minimo)
                ->count(),
        ];

        $nombreArchivo = 'resumen_administrativo_' . $sucursal->id . '_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($resumen, $sucursal, $fechaInicio, $fechaFin) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['RESUMEN ADMINISTRATIVO GENERAL'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, ['Indicador', 'Valor'], ';');

            foreach ($resumen as $indicador => $valor) {
                if (is_numeric($valor)) {
                    $valor = number_format($valor, 2, '.', '');
                }

                fputcsv($archivo, [$indicador, $valor], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }

    public function bajasInventario(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $motivo = $request->get('motivo');
        $estado = $request->get('estado');
        $buscar = $request->get('buscar');

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return view('reportes.bajas-inventario', [
                'bajas' => collect(),
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'motivo' => $motivo,
                'estado' => $estado,
                'buscar' => $buscar,
                'sucursal' => null,
                'totalBajas' => 0,
                'totalUnidades' => 0,
            ]);
        }

        $query = BajaInventario::with([
                'producto.laboratorio',
                'sucursal',
                'lote',
                'usuario',
                'usuarioAnulacion',
            ])
            ->where('sucursal_id', $sucursal->id)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->when($motivo, function ($query, $motivo) {
                $query->where('motivo', $motivo);
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_baja', 'like', "%{$buscar}%")
                        ->orWhereHas('producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('nombre_comercial', 'like', "%{$buscar}%")
                                ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                                ->orWhere('concentracion', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('lote', function ($loteQuery) use ($buscar) {
                            $loteQuery->where('numero_lote', 'like', "%{$buscar}%");
                        });
                });
            });

        $bajasParaTotales = (clone $query)->get();

        $totalBajas = $bajasParaTotales->count();
        $totalUnidades = $bajasParaTotales
            ->where('estado', 'registrado')
            ->sum('cantidad');

        $bajas = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('reportes.bajas-inventario', compact(
            'bajas',
            'fechaInicio',
            'fechaFin',
            'motivo',
            'estado',
            'buscar',
            'sucursal',
            'totalBajas',
            'totalUnidades'
        ));
    }

    public function bajasInventarioExportarCsv(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $motivo = $request->get('motivo');
        $estado = $request->get('estado');
        $buscar = $request->get('buscar');

        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        $query = BajaInventario::with([
                'producto.laboratorio',
                'sucursal',
                'lote',
                'usuario',
                'usuarioAnulacion',
            ])
            ->when($sucursal, function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->when($motivo, function ($query, $motivo) {
                $query->where('motivo', $motivo);
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_baja', 'like', "%{$buscar}%")
                        ->orWhereHas('producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('nombre_comercial', 'like', "%{$buscar}%")
                                ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                                ->orWhere('concentracion', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('lote', function ($loteQuery) use ($buscar) {
                            $loteQuery->where('numero_lote', 'like', "%{$buscar}%");
                        });
                });
            })
            ->latest();

        $bajas = $query->get();

        $nombreArchivo = 'reporte_bajas_inventario_' . $fechaInicio . '_al_' . $fechaFin . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ];

        return response()->stream(function () use ($bajas, $sucursal, $fechaInicio, $fechaFin) {
            $archivo = fopen('php://output', 'w');

            fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($archivo, ['REPORTE DE BAJAS DE INVENTARIO'], ';');
            fputcsv($archivo, ['Sucursal', $sucursal->nombre ?? 'Sin sucursal'], ';');
            fputcsv($archivo, ['Desde', $fechaInicio], ';');
            fputcsv($archivo, ['Hasta', $fechaFin], ';');
            fputcsv($archivo, [], ';');

            fputcsv($archivo, [
                'N° baja',
                'ID',
                'Fecha',
                'Producto',
                'Generico',
                'Concentracion',
                'Laboratorio',
                'Lote',
                'Fecha vencimiento',
                'Sucursal',
                'Motivo',
                'Cantidad',
                'Stock anterior',
                'Stock nuevo',
                'Usuario',
                'Estado',
                'Usuario anulacion',
                'Fecha anulacion',
                'Motivo anulacion',
                'Observacion',
            ], ';');

            foreach ($bajas as $baja) {
                fputcsv($archivo, [
                    $baja->numero_baja ?? 'BAJ-' . str_pad($baja->id, 6, '0', STR_PAD_LEFT),
                    $baja->id,
                    $baja->created_at?->format('d/m/Y H:i'),
                    $baja->producto->nombre_comercial ?? '-',
                    $baja->producto->nombre_generico ?? '',
                    $baja->producto->concentracion ?? '',
                    $baja->producto->laboratorio->nombre ?? '',
                    $baja->lote->numero_lote ?? 'Sin lote',
                    $baja->lote?->fecha_vencimiento?->format('d/m/Y') ?? '',
                    $baja->sucursal->nombre ?? '-',
                    $baja->motivo,
                    $baja->cantidad,
                    $baja->stock_anterior,
                    $baja->stock_nuevo,
                    $baja->usuario->nombre ?? '-',
                    $baja->estado,
                    $baja->usuarioAnulacion->nombre ?? '',
                    $baja->fecha_anulacion?->format('d/m/Y H:i') ?? '',
                    $baja->motivo_anulacion ?? '',
                    $baja->observacion ?? '',
                ], ';');
            }

            fclose($archivo);
        }, 200, $headers);
    }
}
