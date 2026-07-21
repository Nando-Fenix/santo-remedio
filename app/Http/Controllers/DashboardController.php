<?php

namespace App\Http\Controllers;

use App\Models\AtencionServicio;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Venta;
use Carbon\Carbon;

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
                'ingresosServiciosHoy' => 0,
                'atencionesServiciosHoy' => 0,
                'serviciosAnuladosHoy' => 0,
                'ingresosTotalesHoy' => 0,
                'ultimasAtencionesServicio' => collect(),
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

        $ventasDelDia = Venta::where('estado', 'completada')
            ->where('sucursal_id', $sucursal->id)
            ->whereBetween('fecha_hora', [$inicioDia, $finDia])
            ->get();

        $totalVentasDia = round($ventasDelDia->sum('total'), 2);
        $cantidadVentasDia = $ventasDelDia->count();

        /*
        |--------------------------------------------------------------------------
        | Servicios del día
        |--------------------------------------------------------------------------
        */

        $serviciosCompletadosHoyQuery = AtencionServicio::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_hora', now()->toDateString())
            ->where('estado', 'completada');

        $ingresosServiciosHoy = round((clone $serviciosCompletadosHoyQuery)->sum('total'), 2);
        $atencionesServiciosHoy = (clone $serviciosCompletadosHoyQuery)->count();

        $serviciosAnuladosHoy = AtencionServicio::where('sucursal_id', $sucursal->id)
            ->whereDate('fecha_hora', now()->toDateString())
            ->where('estado', 'anulada')
            ->count();

        $ingresosTotalesHoy = round($totalVentasDia + $ingresosServiciosHoy, 2);

        /*
        |--------------------------------------------------------------------------
        | Alertas de inventario
        |--------------------------------------------------------------------------
        */

        $stockBajoCantidad = Inventario::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo')
            ->where('sucursal_id', $sucursal->id)
            ->count();

        $productosAgotadosCantidad = Inventario::where('stock_actual', '<=', 0)
            ->where('sucursal_id', $sucursal->id)
            ->count();

        $productosPorVencerCantidad = Inventario::whereHas('lote', function ($query) {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ]);
            })
            ->where('stock_actual', '>', 0)
            ->where('estado', 'activo')
            ->where('sucursal_id', $sucursal->id)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Últimos movimientos operativos
        |--------------------------------------------------------------------------
        */

        $ultimasVentas = Venta::with(['usuario', 'sucursal', 'pagos.metodoPago'])
            ->where('sucursal_id', $sucursal->id)
            ->latest('fecha_hora')
            ->limit(5)
            ->get();

        $ultimasAtencionesServicio = AtencionServicio::with([
                'servicio',
                'cliente',
            ])
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
            'ultimasVentas',
            'ingresosServiciosHoy',
            'ultimasAtencionesServicio',
            'atencionesServiciosHoy',
            'serviciosAnuladosHoy',
            'ingresosTotalesHoy'
        ));
    }
}