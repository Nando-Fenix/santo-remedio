<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Venta;
use Carbon\Carbon;
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
}
