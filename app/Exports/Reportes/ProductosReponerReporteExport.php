<?php

namespace App\Exports\Reportes;

use App\Models\DetalleVenta;
use App\Models\Inventario;

class ProductosReponerReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private int $dias = 30, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $fechaInicio = now()->subDays($this->dias)->startOfDay(); $fechaFin = now()->endOfDay();
        $inventarios=Inventario::with(['producto.laboratorio','producto.presentacionPrincipal.presentacion','lote'])->where('sucursal_id',$this->sucursal->id)->where('estado','activo')->where(function($q){$q->where('stock_actual','<=',0)->orWhereColumn('stock_actual','<=','stock_minimo');})->when($this->buscar,function($q){$b=$this->buscar;$q->whereHas('producto',fn($p)=>$p->where('nombre_comercial','like',"%{$b}%")->orWhere('nombre_generico','like',"%{$b}%"));})->get();
        $items=$inventarios->map(function($inv) use($fechaInicio,$fechaFin){$vendidas=DetalleVenta::where('producto_id',$inv->producto_id)->whereHas('venta',fn($q)=>$q->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereBetween('fecha_hora',[$fechaInicio,$fechaFin]))->sum('cantidad');$sugerida=max(0,($inv->stock_minimo*2)-$inv->stock_actual);return ['inv'=>$inv,'vendidas'=>$vendidas,'sugerida'=>$sugerida];})->sortByDesc('sugerida')->values();
        $rows=$items->map(function($it){$i=$it['inv'];$estado=$i->stock_actual<=0?'Agotado':'Stock bajo';return [$i->producto->nombre_comercial ?? '-',$i->producto->nombre_generico ?? '-',$i->producto->laboratorio->nombre ?? '-',$i->producto->presentacionPrincipal->presentacion->nombre ?? '-',$i->stock_actual,$i->stock_minimo,$it['vendidas'],$it['sugerida'],$estado];})->all();
        return ['sheet_title'=>'Productos reponer','subtitle'=>'REPORTE DE PRODUCTOS A REPONER','description'=>'Productos agotados o con stock bajo y cantidad sugerida','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Días evaluados',$this->dias],['Búsqueda',$this->buscar ?: 'Sin búsqueda'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Productos a reponer',$items->count()],['Agotados',$items->filter(fn($it)=>$it['inv']->stock_actual<=0)->count()],['Stock bajo',$items->filter(fn($it)=>$it['inv']->stock_actual>0)->count()],['Cantidad sugerida',$items->sum('sugerida')]],'detail_title'=>'DETALLE DE PRODUCTOS A REPONER','headers'=>['Producto','Genérico','Laboratorio','Presentación','Stock','Stock mín.','Vendidas','Sugerida','Estado'],'rows'=>$rows,'status_column'=>'I','footer'=>'Reporte de productos a reponer','widths'=>['A'=>32,'B'=>22,'C'=>20,'D'=>18,'E'=>12,'F'=>12,'G'=>12,'H'=>12,'I'=>16]];
    }
}
