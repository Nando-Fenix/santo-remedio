<?php

namespace App\Exports\Reportes;

use App\Models\DetalleVenta;

class UtilidadEstimadaReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $detalles=DetalleVenta::with(['venta','producto.laboratorio','productoPresentacion.presentacion'])->whereHas('venta',fn($q)=>$q->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin))->when($this->buscar,function($q){$b=$this->buscar;$q->whereHas('producto',fn($p)=>$p->where('nombre_comercial','like',"%{$b}%")->orWhere('nombre_generico','like',"%{$b}%")->orWhere('concentracion','like',"%{$b}%"));})->get();
        $items=$detalles->groupBy(fn($d)=>$d->producto_id.'-'.$d->producto_presentacion_id)->map(function($g){$f=$g->first();$venta=$g->sum('subtotal');$costo=$g->sum(fn($d)=>($d->productoPresentacion->precio_compra ?? 0)*($d->cantidad ?? 0));$util=$venta-$costo;$margen=$venta>0?($util/$venta)*100:0;return ['producto'=>$f->producto,'presentacion'=>$f->productoPresentacion,'cant'=>$g->sum('cantidad'),'venta'=>$venta,'costo'=>$costo,'util'=>$util,'margen'=>$margen];})->sortByDesc('util')->values();
        $rows=$items->map(fn($it)=>[$it['producto']->nombre_comercial ?? '-',$it['producto']->nombre_generico ?? '-',$it['producto']->laboratorio->nombre ?? '-',$it['presentacion']->presentacion->nombre ?? '-',$it['cant'],$it['venta'],$it['costo'],$it['util'],round($it['margen'],2).'%'])->all();
        return ['sheet_title'=>'Utilidad estimada','subtitle'=>'REPORTE DE UTILIDAD ESTIMADA','description'=>'Ganancia aproximada según precio de venta y precio de compra','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Búsqueda',$this->buscar ?: 'Sin búsqueda'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Productos vendidos',$items->count()],['Total venta Bs',$items->sum('venta')],['Costo estimado Bs',$items->sum('costo')],['Utilidad estimada Bs',$items->sum('util')],['Margen general',($items->sum('venta')>0?round(($items->sum('util')/$items->sum('venta'))*100,2):0).'%']],'detail_title'=>'DETALLE DE UTILIDAD POR PRODUCTO','headers'=>['Producto','Genérico','Laboratorio','Presentación','Cantidad','Venta Bs','Costo Bs','Utilidad Bs','Margen'],'rows'=>$rows,'money_columns'=>['F','G','H'],'footer'=>'Reporte de utilidad estimada','widths'=>['A'=>32,'B'=>22,'C'=>20,'D'=>18,'E'=>12,'F'=>14,'G'=>14,'H'=>14,'I'=>12]];
    }
}
