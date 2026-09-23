<?php

namespace App\Exports\Reportes;

use App\Models\AtencionServicio;
use App\Models\Venta;

class IngresosDiariosReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin) {}

    protected function buildReport(): array
    {
        $ventasCompletadas = Venta::where('sucursal_id',$this->sucursal->id)->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->where('estado','completada')->get();
        $ventasAnuladas = Venta::where('sucursal_id',$this->sucursal->id)->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->where('estado','anulada')->get();
        $servicios = AtencionServicio::with(['servicio','cliente','usuario','metodoPago'])->where('sucursal_id',$this->sucursal->id)->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->latest('fecha_hora')->get();
        $serviciosCompletados = $servicios->where('estado','completada');
        $serviciosAnulados = $servicios->where('estado','anulada');
        $rows = $servicios->map(fn ($a) => [$a->fecha_hora?->format('d/m/Y H:i') ?? '-', $a->servicio->nombre ?? '-', $a->cliente->nombre ?? 'Consumidor final', $a->metodoPago->nombre ?? '-', $a->cantidad, $a->total, ucfirst($a->estado), $a->usuario->nombre ?? '-'])->values()->all();
        return ['sheet_title'=>'Ingresos diarios','subtitle'=>'REPORTE DE INGRESOS DIARIOS','description'=>'Resumen de ventas y servicios de farmacia','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Ventas completadas Bs',$ventasCompletadas->sum('total')],['Servicios completados Bs',$serviciosCompletados->sum('total')],['Ingresos totales Bs',$ventasCompletadas->sum('total')+$serviciosCompletados->sum('total')],['Cantidad ventas',$ventasCompletadas->count()],['Atenciones servicio',$serviciosCompletados->count()],['Servicios anulados',$serviciosAnulados->count()],['Ventas anuladas',$ventasAnuladas->count()],['Monto ventas anuladas Bs',$ventasAnuladas->sum('total')],['Monto servicios anulados Bs',$serviciosAnulados->sum('total')]],'detail_title'=>'DETALLE DE SERVICIOS REGISTRADOS','headers'=>['Fecha','Servicio','Cliente','Método','Cantidad','Total Bs','Estado','Usuario'],'rows'=>$rows,'money_columns'=>['F'],'status_column'=>'G','footer'=>'Reporte de ingresos diarios','widths'=>['A'=>18,'B'=>28,'C'=>28,'D'=>18,'E'=>12,'F'=>14,'G'=>14,'H'=>22]];
    }
}
