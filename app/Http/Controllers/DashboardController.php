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

        /*
        |--------------------------------------------------------------------------
        | Caja abierta del usuario
        |--------------------------------------------------------------------------
        */
        $cajaAbierta = null;

        if ($sucursal) {
            $cajaAbierta = Caja::with('turno')
                ->where('usuario_id', $user->id)
                ->where('sucursal_id', $sucursal->id)
                ->where('estado', 'abierta')
                ->latest('fecha_apertura')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Ventas del día
        |--------------------------------------------------------------------------
        */
        $inicioDia = Carbon::now()->startOfDay();
        $finDia = Carbon::now()->endOfDay();

        $ventasQuery = Venta::where('estado', 'completada')
            ->whereBetween('fecha_hora', [$inicioDia, $finDia]);

        if ($sucursal) {
            $ventasQuery->where('sucursal_id', $sucursal->id);
        }

        /*
        | Si quieres que el dashboard muestre solo ventas de la caja abierta,
        | dejamos este filtro. Si prefieres todas las ventas del día de la sucursal,
        | puedes borrar este bloque.
        */
        if ($cajaAbierta) {
            $ventasQuery->where('caja_id', $cajaAbierta->id);
        }

        $ventasDelDia = $ventasQuery->get();

        $totalVentasDia = $ventasDelDia->sum('total');
        $cantidadVentasDia = $ventasDelDia->count();

        /*
        |--------------------------------------------------------------------------
        | Alertas de inventario
        |--------------------------------------------------------------------------
        */
        $stockBajoQuery = Inventario::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo');

        $agotadosQuery = Inventario::where('stock_actual', '<=', 0);

        $porVencerQuery = Inventario::whereHas('lote', function ($query) {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ]);
            })
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo');

        if ($sucursal) {
            $stockBajoQuery->where('sucursal_id', $sucursal->id);
            $agotadosQuery->where('sucursal_id', $sucursal->id);
            $porVencerQuery->where('sucursal_id', $sucursal->id);
        }

        $stockBajoCantidad = $stockBajoQuery->count();
        $productosAgotadosCantidad = $agotadosQuery->count();
        $productosPorVencerCantidad = $porVencerQuery->count();

        /*
        |--------------------------------------------------------------------------
        | Últimas ventas
        |--------------------------------------------------------------------------
        */
        $ultimasVentas = Venta::with(['usuario', 'sucursal', 'pagos.metodoPago'])
            ->when($sucursal, function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->when($cajaAbierta, function ($query) use ($cajaAbierta) {
                $query->where('caja_id', $cajaAbierta->id);
            })
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