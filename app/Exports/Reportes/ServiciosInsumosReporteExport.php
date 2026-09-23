<?php

namespace App\Exports\Reportes;

use App\Models\AtencionServicioInsumo;
use App\Models\ServicioFarmacia;

class ServiciosInsumosReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $servicioId = null) {}

    protected function buildReport(): array
    {
        $insumos = AtencionServicioInsumo::with(['atencionServicio.servicio','atencionServicio.cliente','atencionServicio.usuario','atencionServicio.sucursal','producto.laboratorio','productoPresentacion.presentacion','lote'])
            ->whereHas('atencionServicio', function ($q) {
                $q->where('sucursal_id', $this->sucursal->id)
                    ->whereDate('fecha_hora','>=',$this->fechaInicio)
                    ->whereDate('fecha_hora','<=',$this->fechaFin)
                    ->where('estado','completada')
                    ->when($this->servicioId, fn ($qq) => $qq->where('servicio_farmacia_id', $this->servicioId));
            })
            ->latest('created_at')
            ->get();
        $servicioNombre = $this->servicioId ? (ServicioFarmacia::find($this->servicioId)?->nombre ?? 'Filtrado') : 'Todos';
        $rows = $insumos->map(fn ($insumo) => [
            $insumo->atencion_servicio_id,
            $insumo->atencionServicio->fecha_hora?->format('d/m/Y H:i') ?? '-',
            $insumo->atencionServicio->servicio->nombre ?? '-',
            $insumo->atencionServicio->cliente->nombre ?? 'Consumidor final',
            $insumo->productoPresentacion->nombre_mostrado ?? $insumo->producto->nombre_comercial ?? '-',
            $insumo->producto->laboratorio->nombre ?? '-',
            $insumo->producto->concentracion ?? '-',
            $insumo->productoPresentacion->presentacion->nombre ?? '-',
            $insumo->lote->numero_lote ?? 'Sin lote',
            $insumo->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-',
            $insumo->unidades_descontadas,
            $insumo->atencionServicio->usuario->nombre ?? '-',
        ])->values()->all();
        return [
            'sheet_title'=>'Insumos servicios','subtitle'=>'REPORTE DE INSUMOS DE SERVICIOS','description'=>'Insumos consumidos en atenciones de servicios de farmacia',
            'meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Servicio',$servicioNombre],['Generado',now()->format('d/m/Y H:i')]],
            'summary'=>[['Registros',$insumos->count()],['Unidades descontadas',$insumos->sum('unidades_descontadas')],['Atenciones distintas',$insumos->pluck('atencion_servicio_id')->unique()->count()]],
            'detail_title'=>'DETALLE DE INSUMOS USADOS','headers'=>['Atención ID','Fecha','Servicio','Cliente','Insumo','Laboratorio','Concentración','Presentación','Lote','Vencimiento','Unidades','Usuario'],
            'rows'=>$rows,'money_columns'=>[],'footer'=>'Reporte de insumos de servicios',
            'widths'=>['A'=>14,'B'=>18,'C'=>24,'D'=>24,'E'=>34,'F'=>20,'G'=>16,'H'=>18,'I'=>16,'J'=>14,'K'=>12,'L'=>20],
        ];
    }
}
