<?php

namespace App\Exports\Reportes;

use App\Models\AtencionServicio;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Venta;

class ResumenAdministrativoReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin) {}

    protected function buildReport(): array
    {
        $ventasCompletadas=Venta::where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->get();
        $ventasAnuladas=Venta::where('sucursal_id',$this->sucursal->id)->where('estado','anulada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->get();
        $serviciosCompletados=AtencionServicio::where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->get();
        $serviciosAnulados=AtencionServicio::where('sucursal_id',$this->sucursal->id)->where('estado','anulada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->get();
        $compras=Compra::where('sucursal_id',$this->sucursal->id)->whereDate('fecha_compra','>=',$this->fechaInicio)->whereDate('fecha_compra','<=',$this->fechaFin)->get(); $comprasValidas=$compras->where('estado','!=','anulada');
        $detalles=DetalleVenta::with(['productoPresentacion'])->whereHas('venta',fn($q)=>$q->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin))->get(); $venta=$detalles->sum('subtotal'); $costo=$detalles->sum(fn($d)=>($d->productoPresentacion->precio_compra ?? 0)*($d->cantidad ?? 0)); $utilidad=$venta-$costo; $margen=$venta>0?($utilidad/$venta)*100:0;
        $cajas=Caja::where('sucursal_id',$this->sucursal->id)->whereDate('fecha_apertura','>=',$this->fechaInicio)->whereDate('fecha_apertura','<=',$this->fechaFin)->get();
        $criticos=Inventario::where('sucursal_id',$this->sucursal->id)->where('estado','activo')->where(function($q){$q->where('stock_actual','<=',0)->orWhereColumn('stock_actual','<=','stock_minimo');})->get();
        $rows=[['Ingresos por ventas',$ventasCompletadas->sum('total')],['Ingresos por servicios',$serviciosCompletados->sum('total')],['Ingresos totales',$ventasCompletadas->sum('total')+$serviciosCompletados->sum('total')],['Total compras',$comprasValidas->sum('total')],['Deuda proveedores',$compras->where('estado','pendiente')->sum('saldo_pendiente')],['Utilidad estimada',$utilidad],['Margen estimado',round($margen,2).'%'],['Productos críticos',$criticos->count()],['Productos agotados',$criticos->where('stock_actual','<=',0)->count()]];
        return ['sheet_title'=>'Resumen administrativo','subtitle'=>'RESUMEN ADMINISTRATIVO','description'=>'Indicadores generales de ventas, servicios, compras, caja e inventario','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Ingresos totales Bs',$ventasCompletadas->sum('total')+$serviciosCompletados->sum('total')],['Utilidad estimada Bs',$utilidad],['Deuda proveedores Bs',$compras->where('estado','pendiente')->sum('saldo_pendiente')],['Ventas completadas',$ventasCompletadas->count()],['Servicios completados',$serviciosCompletados->count()],['Productos críticos',$criticos->count()]],'detail_title'=>'LECTURA RÁPIDA ADMINISTRATIVA','headers'=>['Indicador','Valor'],'rows'=>$rows,'money_columns'=>['B'],'footer'=>'Resumen administrativo','widths'=>['A'=>35,'B'=>22]];
    }
}
