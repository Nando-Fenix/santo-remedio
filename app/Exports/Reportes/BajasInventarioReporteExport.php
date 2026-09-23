<?php

namespace App\Exports\Reportes;

use App\Models\BajaInventario;

class BajasInventarioReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $motivo = null, private ?string $estado = null, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $bajas = BajaInventario::with(['producto.laboratorio','productoPresentacion.presentacion','lote','usuario','usuarioAnulacion'])
            ->where('sucursal_id',$this->sucursal->id)->whereDate('created_at','>=',$this->fechaInicio)->whereDate('created_at','<=',$this->fechaFin)
            ->when($this->motivo, fn($q)=>$q->where('motivo',$this->motivo))->when($this->estado, fn($q)=>$q->where('estado',$this->estado))
            ->when($this->buscar, function($q){$b=$this->buscar; $q->where(function($qq) use ($b){$qq->where('numero_baja','like',"%{$b}%")->orWhereHas('producto', fn($p)=>$p->where('nombre_comercial','like',"%{$b}%"))->orWhereHas('lote', fn($l)=>$l->where('numero_lote','like',"%{$b}%"));});})
            ->latest()->get();
        $validas=$bajas->where('estado','registrado');
        $rows=$bajas->map(fn($b)=>[$b->numero_baja ?? 'BAJ-'.str_pad($b->id,6,'0',STR_PAD_LEFT),$b->created_at?->format('d/m/Y H:i') ?? '-',$b->producto->nombre_comercial ?? '-',$b->productoPresentacion->presentacion->nombre ?? '-',$b->lote->numero_lote ?? 'Sin lote',ucfirst(str_replace('_',' ',$b->motivo)),$b->cantidad,$b->stock_anterior,$b->stock_nuevo,$b->usuario->nombre ?? '-',ucfirst($b->estado),$b->observacion ?? '-'])->values()->all();
        return ['sheet_title'=>'Bajas inventario','subtitle'=>'REPORTE DE BAJAS DE INVENTARIO','description'=>'Productos retirados de stock por vencimiento, daño, pérdida u otro motivo','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Motivo',$this->motivo ?: 'Todos'],['Estado',$this->estado ?: 'Todos'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Bajas registradas',$bajas->count()],['Unidades retiradas',$validas->sum('cantidad')],['Anuladas',$bajas->where('estado','anulado')->count()]],'detail_title'=>'DETALLE DE BAJAS','headers'=>['Nro baja','Fecha','Producto','Presentación','Lote','Motivo','Cantidad','Stock anterior','Stock nuevo','Usuario','Estado','Observación'],'rows'=>$rows,'status_column'=>'K','footer'=>'Reporte de bajas de inventario','widths'=>['A'=>18,'B'=>18,'C'=>32,'D'=>18,'E'=>16,'F'=>18,'G'=>12,'H'=>14,'I'=>14,'J'=>20,'K'=>14,'L'=>30]];
    }
}
