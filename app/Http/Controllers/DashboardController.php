<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $sucursal = $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

        if (!$sucursal) {
            return view('dashboard', [
                'sucursal' => null,
                'totalVentasDia' => 0,
                'cantidadVentasDia' => 0,
                'cajaAbierta' => null,
                'stockBajoCantidad' => 0,
                'productosAgotadosCantidad' => 0,
                'productosPorVencerCantidad' => 0,
                'ultimasVentas' => collect(),
            ])->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        /*
        |--------------------------------------------------------------------------
        | Caja abierta de la sucursal
        |--------------------------------------------------------------------------
        */

        $cajaAbierta = Caja::with(['turno', 'usuario'])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Ventas del día
        |--------------------------------------------------------------------------
        */
        $inicioDia = Carbon::now()->startOfDay();
        $finDia = Carbon::now()->endOfDay();

        $ventasQuery = Venta::where('estado', 'completada')
            ->where('sucursal_id', $sucursal->id)
            ->whereBetween('fecha_hora', [$inicioDia, $finDia]);

        /*
        | Si quieres que el dashboard muestre solo ventas de la caja abierta,
        | dejamos este filtro. Si prefieres todas las ventas del día de la sucursal,
        | puedes borrar este bloque.
        */

        $ventasDelDia = $ventasQuery->get();

        $totalVentasDia = round($ventasDelDia->sum('total'), 2);
        $cantidadVentasDia = $ventasDelDia->count();

        /*
        |--------------------------------------------------------------------------
        | Alertas de inventario
        |--------------------------------------------------------------------------
        */
        $stockBajoQuery = Inventario::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo')
            ->where('sucursal_id', $sucursal->id);

        $agotadosQuery = Inventario::where('stock_actual', '<=', 0)
            ->where('sucursal_id', $sucursal->id);

        $porVencerQuery = Inventario::whereHas('lote', function ($query) {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ]);
            })
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo')
            ->where('sucursal_id', $sucursal->id);

        $stockBajoQuery->where('sucursal_id', $sucursal->id);
        $agotadosQuery->where('sucursal_id', $sucursal->id);
        $porVencerQuery->where('sucursal_id', $sucursal->id);

        $stockBajoCantidad = $stockBajoQuery->count();
        $productosAgotadosCantidad = $agotadosQuery->count();
        $productosPorVencerCantidad = $porVencerQuery->count();

        /*
        |--------------------------------------------------------------------------
        | Últimas ventas
        |--------------------------------------------------------------------------
        */
        $ultimasVentas = Venta::with(['usuario', 'sucursal', 'pagos.metodoPago'])
            ->where('sucursal_id', $sucursal->id)
            ->latest('fecha_hora')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'sucursal',
            'totalVentasDia',
            'cantidadVentasDia',
            'cajaAbierta',
            'stockBajoCantidad',
            'productosAgotadosCantidad',
            'productosPorVencerCantidad',
            'ultimasVentas'
        ));
    }
}