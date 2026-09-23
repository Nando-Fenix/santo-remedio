<?php

namespace App\Exports\Reportes;

use App\Models\Inventario;

class InventarioCriticoReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private ?string $buscar = null, private ?string $tipo = null) {}

    protected function buildReport(): array
    {
        $inventarios = Inventario::with(['producto.laboratorio','producto.presentacionPrincipal.presentacion','lote','sucursal'])
            ->where('sucursal_id',$this->sucursal->id)->where('estado','activo')
            ->where(function($q){$q->where('stock_actual','<=',0)->orWhereColumn('stock_actual','<=','stock_minimo')->orWhereHas('lote',fn($l)=>$l->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento','<=',now()->addDays(30)->toDateString()));})
            ->when($this->buscar, function($q){$b=$this->buscar; $q->whereHas('producto',fn($p)=>$p->where('nombre_comercial','like',"%{$b}%")->orWhere('nombre_generico','like',"%{$b}%")->orWhere('concentracion','like',"%{$b}%"));})
            ->get();
        if($this->tipo==='agotados') $inventarios=$inventarios->where('stock_actual','<=',0);
        if($this->tipo==='stock_bajo') $inventarios=$inventarios->filter(fn($i)=>$i->stock_actual>0 && $i->stock_actual <= $i->stock_minimo);
        if($this->tipo==='vencidos') $inventarios=$inventarios->filter(fn($i)=>$i->stock_actual>0 && $i->lote?->fecha_vencimiento && $i->lote->fecha_vencimiento->lt(now()->startOfDay()));
        if($this->tipo==='proximos_vencer') $inventarios=$inventarios->filter(fn($i)=>$i->stock_actual>0 && $i->lote?->fecha_vencimiento && $i->lote->fecha_vencimiento->between(now()->startOfDay(), now()->addDays(30)->endOfDay()));
        $rows=$inventarios->sortBy('stock_actual')->map(function($i){$estado='Stock bajo'; if($i->stock_actual<=0)$estado='Agotado'; if($i->lote?->fecha_vencimiento && $i->lote->fecha_vencimiento->lt(now()->startOfDay()))$estado='Vencido'; elseif($i->lote?->fecha_vencimiento && $i->lote->fecha_vencimiento->between(now()->startOfDay(),now()->addDays(30)->endOfDay()))$estado='Próximo vencer'; return [$i->producto->nombre_comercial ?? '-',$i->producto->nombre_generico ?? '-',$i->producto->laboratorio->nombre ?? '-',$i->producto->presentacionPrincipal->presentacion->nombre ?? '-',$i->lote->numero_lote ?? 'Sin lote',$i->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-',$i->stock_actual,$i->stock_minimo,$estado];})->values()->all();
        return ['sheet_title'=>'Inventario crítico','subtitle'=>'REPORTE DE INVENTARIO CRÍTICO','description'=>'Productos agotados, bajo stock, vencidos o próximos a vencer','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Tipo',$this->tipo ?: 'Todos'],['Búsqueda',$this->buscar ?: 'Sin búsqueda'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Agotados',$inventarios->where('stock_actual','<=',0)->count()],['Stock bajo',$inventarios->filter(fn($i)=>$i->stock_actual>0 && $i->stock_actual <= $i->stock_minimo)->count()],['Registros',$inventarios->count()]],'detail_title'=>'DETALLE DE INVENTARIO CRÍTICO','headers'=>['Producto','Genérico','Laboratorio','Presentación','Lote','Vencimiento','Stock','Stock mín.','Estado'],'rows'=>$rows,'status_column'=>'I','footer'=>'Reporte de inventario crítico','widths'=>['A'=>32,'B'=>22,'C'=>20,'D'=>18,'E'=>16,'F'=>14,'G'=>12,'H'=>12,'I'=>18]];
    }
}
