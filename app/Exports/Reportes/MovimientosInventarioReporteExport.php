<?php

namespace App\Exports\Reportes;

use App\Models\MovimientoInventario;

class MovimientosInventarioReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $tipo = null, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $movs = MovimientoInventario::with(['producto.laboratorio','productoPresentacion.presentacion','lote','usuario','sucursal'])
            ->where('sucursal_id',$this->sucursal->id)->whereDate('created_at','>=',$this->fechaInicio)->whereDate('created_at','<=',$this->fechaFin)
            ->when($this->tipo, fn ($q) => $q->where('tipo_movimiento',$this->tipo))
            ->when($this->buscar, function ($q) { $b=$this->buscar; $q->whereHas('producto', fn ($qq) => $qq->where('nombre_comercial','like',"%{$b}%")->orWhere('nombre_generico','like',"%{$b}%")->orWhere('concentracion','like',"%{$b}%")); })
            ->latest('created_at')->get();
        $rows=$movs->map(fn($m)=>[$m->created_at?->format('d/m/Y H:i') ?? '-',ucfirst($m->tipo_movimiento),$m->producto->nombre_comercial ?? '-',$m->productoPresentacion->presentacion->nombre ?? '-',$m->lote->numero_lote ?? 'Sin lote',$m->cantidad,$m->stock_anterior,$m->stock_nuevo,$m->motivo ?? '-',$m->usuario->nombre ?? '-'])->values()->all();
        return ['sheet_title'=>'Movimientos inventario','subtitle'=>'REPORTE DE MOVIMIENTOS DE INVENTARIO','description'=>'Entradas, salidas, ajustes y movimientos de stock','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Tipo',$this->tipo ?: 'Todos'],['Búsqueda',$this->buscar ?: 'Sin búsqueda'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Entradas',$movs->where('tipo_movimiento','entrada')->sum('cantidad')],['Salidas',$movs->where('tipo_movimiento','salida')->sum('cantidad')],['Ajustes',$movs->where('tipo_movimiento','ajuste')->sum('cantidad')],['Registros',$movs->count()]],'detail_title'=>'DETALLE DE MOVIMIENTOS','headers'=>['Fecha','Tipo','Producto','Presentación','Lote','Cantidad','Stock anterior','Stock nuevo','Motivo','Usuario'],'rows'=>$rows,'status_column'=>'B','footer'=>'Reporte de movimientos de inventario','widths'=>['A'=>18,'B'=>14,'C'=>34,'D'=>18,'E'=>16,'F'=>12,'G'=>14,'H'=>14,'I'=>30,'J'=>20]];
    }
}
