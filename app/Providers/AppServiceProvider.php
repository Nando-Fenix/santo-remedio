<?php

namespace App\Providers;

use App\Models\Caja;
use App\Models\Inventario;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $sucursalNotificacion = null;
            $stockBajoNotificacion = 0;
            $agotadosNotificacion = 0;
            $proximosVencerNotificacion = 0;
            $vencidosNotificacion = 0;
            $cajaCerradaNotificacion = false;

            if ($user) {
                $sucursalNotificacion = $user->sucursalPrincipal()->first()
                    ?? $user->sucursales()->wherePivot('estado', 'activo')->first();

                if ($sucursalNotificacion) {
                    if ($user->tienePermiso('ver_inventario')) {
                        $stockBajoNotificacion = Inventario::where('sucursal_id', $sucursalNotificacion->id)
                            ->where('estado', 'activo')
                            ->where('stock_actual', '>', 0)
                            ->whereColumn('stock_actual', '<=', 'stock_minimo')
                            ->count();

                        $agotadosNotificacion = Inventario::where('sucursal_id', $sucursalNotificacion->id)
                            ->where('stock_actual', '<=', 0)
                            ->count();

                        $proximosVencerNotificacion = Inventario::where('sucursal_id', $sucursalNotificacion->id)
                            ->where('estado', 'activo')
                            ->where('stock_actual', '>', 0)
                            ->whereHas('lote', function ($query) {
                                $query->whereNotNull('fecha_vencimiento')
                                    ->whereBetween('fecha_vencimiento', [
                                        now()->toDateString(),
                                        now()->addDays(30)->toDateString(),
                                    ]);
                            })
                            ->count();

                        $vencidosNotificacion = Inventario::where('sucursal_id', $sucursalNotificacion->id)
                            ->where('estado', 'activo')
                            ->where('stock_actual', '>', 0)
                            ->whereHas('lote', function ($query) {
                                $query->whereNotNull('fecha_vencimiento')
                                    ->whereDate('fecha_vencimiento', '<', now()->toDateString());
                            })
                            ->count();
                    }

                    if ($user->tienePermiso('ver_caja')) {
                        $cajaCerradaNotificacion = ! Caja::where('sucursal_id', $sucursalNotificacion->id)
                            ->where('estado', 'abierta')
                            ->exists();
                    }
                }
            }

            $totalNotificaciones = $stockBajoNotificacion
                + $agotadosNotificacion
                + $proximosVencerNotificacion
                + $vencidosNotificacion
                + ($cajaCerradaNotificacion ? 1 : 0);

            $view->with([
                'sucursalNotificacion' => $sucursalNotificacion,
                'stockBajoNotificacion' => $stockBajoNotificacion,
                'agotadosNotificacion' => $agotadosNotificacion,
                'proximosVencerNotificacion' => $proximosVencerNotificacion,
                'vencidosNotificacion' => $vencidosNotificacion,
                'cajaCerradaNotificacion' => $cajaCerradaNotificacion,
                'totalNotificaciones' => $totalNotificaciones,
            ]);
        });

        \DB::listen(function ($query) {
            if ($query->time > 500) {
                \Log::warning('Consulta lenta detectada', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms' => $query->time,
                ]);
            }
        });
    }
}