<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Venta;
use Carbon\Carbon;
use App\Models\DetalleVentaPromocion;
use App\Models\DetalleVentaPromocionItem;
use Illuminate\Http\Request;

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
}
