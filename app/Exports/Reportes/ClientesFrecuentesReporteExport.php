<?php

namespace App\Exports\Reportes;

use App\Models\Venta;

class ClientesFrecuentesReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $ventas = Venta::with(['cliente'])->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereNotNull('cliente_id')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->when($this->buscar,function($q){$b=$this->buscar;$q->whereHas('cliente',fn($c)=>$c->where('nombre','like',"%{$b}%")->orWhere('ci_nit','like',"%{$b}%")->orWhere('telefono','like',"%{$b}%"));})->get();
        $items=$ventas->groupBy('cliente_id')->map(function($g){$c=$g->first()->cliente; return ['cliente'=>$c,'compras'=>$g->count(),'total'=>$g->sum('total'),'ticket'=>$g->avg('total'),'ultima'=>$g->max('fecha_hora')];})->sortByDesc('total')->values();
        $rows=$items->map(fn($it,$idx)=>[$idx+1,$it['cliente']->nombre ?? '-',$it['cliente']->ci_nit ?? '-',$it['cliente']->telefono ?? '-',$it['compras'],$it['total'],$it['ticket'],$it['ultima']?->format('d/m/Y H:i') ?? '-'])->all();
        return ['sheet_title'=>'Clientes frecuentes','subtitle'=>'REPORTE DE CLIENTES FRECUENTES','description'=>'Ranking de clientes por compras y monto generado','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Búsqueda',$this->buscar ?: 'Sin búsqueda'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Clientes distintos',$items->count()],['Ventas con cliente',$ventas->count()],['Total comprado Bs',$ventas->sum('total')],['Ticket promedio Bs',$ventas->count() ? $ventas->avg('total') : 0]],'detail_title'=>'RANKING DE CLIENTES','headers'=>['#','Cliente','CI/NIT','Teléfono','Compras','Total comprado Bs','Ticket prom. Bs','Última compra'],'rows'=>$rows,'money_columns'=>['F','G'],'footer'=>'Reporte de clientes frecuentes','widths'=>['A'=>8,'B'=>30,'C'=>16,'D'=>16,'E'=>12,'F'=>18,'G'=>18,'H'=>18]];
    }
}
