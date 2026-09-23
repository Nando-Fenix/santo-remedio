<?php

namespace App\Exports\Reportes;

use App\Models\DetalleVenta;

class ProductosVendidosReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $detalles = DetalleVenta::with(['venta','producto.laboratorio','productoPresentacion.presentacion'])
            ->whereHas('venta',fn($q)=>$q->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin))
            ->when($this->buscar, function($q){$b=$this->buscar; $q->whereHas('producto',fn($p)=>$p->where('nombre_comercial','like',"%{$b}%")->orWhere('nombre_generico','like',"%{$b}%")->orWhere('concentracion','like',"%{$b}%"));})
            ->get();
        $items=$detalles->groupBy(fn($d)=>$d->producto_id.'-'.$d->producto_presentacion_id)->map(function($g){$f=$g->first(); return ['producto'=>$f->producto,'presentacion'=>$f->productoPresentacion,'cantidad'=>$g->sum('cantidad'),'unidades'=>$g->sum('unidades_equivalentes'),'total'=>$g->sum('subtotal')];})->sortByDesc('total')->values();
        $rows=$items->map(fn($it,$idx)=>[$idx+1,$it['producto']->nombre_comercial ?? '-',$it['producto']->nombre_generico ?? '-',$it['producto']->laboratorio->nombre ?? '-',$it['presentacion']->presentacion->nombre ?? '-',$it['cantidad'],$it['unidades'],$it['total']])->all();
        return ['sheet_title'=>'Productos vendidos','subtitle'=>'REPORTE DE PRODUCTOS VENDIDOS','description'=>'Ranking de productos por cantidad e ingresos','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Búsqueda',$this->buscar ?: 'Sin búsqueda'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Productos distintos',$items->count()],['Cantidad vendida',$items->sum('cantidad')],['Unidades vendidas',$items->sum('unidades')],['Total generado Bs',$items->sum('total')]],'detail_title'=>'RANKING DE PRODUCTOS','headers'=>['#','Producto','Genérico','Laboratorio','Presentación','Cantidad','Unidades','Total Bs'],'rows'=>$rows,'money_columns'=>['H'],'footer'=>'Reporte de productos vendidos','widths'=>['A'=>8,'B'=>32,'C'=>22,'D'=>20,'E'=>18,'F'=>12,'G'=>12,'H'=>15]];
    }
}
