<?php

namespace App\Exports\Reportes;

use App\Models\AtencionServicio;
use App\Models\ServicioFarmacia;

class ServiciosReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $estado = null, private ?string $servicioId = null) {}

    protected function buildReport(): array
    {
        $atenciones = AtencionServicio::with(['servicio','cliente','usuario','metodoPago','sucursal'])
            ->where('sucursal_id', $this->sucursal->id)
            ->whereDate('fecha_hora','>=',$this->fechaInicio)
            ->whereDate('fecha_hora','<=',$this->fechaFin)
            ->when($this->estado, fn ($q) => $q->where('estado', $this->estado))
            ->when($this->servicioId, fn ($q) => $q->where('servicio_farmacia_id', $this->servicioId))
            ->latest('fecha_hora')
            ->get();
        $completadas = $atenciones->where('estado','completada');
        $anuladas = $atenciones->where('estado','anulada');
        $servicioNombre = $this->servicioId ? (ServicioFarmacia::find($this->servicioId)?->nombre ?? 'Filtrado') : 'Todos';
        $rows = $atenciones->map(fn ($atencion) => [
            $atencion->numero_atencion ?? 'SER-' . str_pad($atencion->id, 6, '0', STR_PAD_LEFT),
            $atencion->fecha_hora?->format('d/m/Y H:i') ?? '-',
            $atencion->servicio->nombre ?? '-',
            $atencion->cliente->nombre ?? 'Consumidor final',
            $atencion->cantidad,
            $atencion->precio_unitario,
            $atencion->subtotal,
            $atencion->descuento,
            $atencion->total,
            $atencion->metodoPago->nombre ?? '-',
            $atencion->usuario->nombre ?? '-',
            ucfirst($atencion->estado),
        ])->values()->all();
        return [
            'sheet_title'=>'Servicios','subtitle'=>'REPORTE DE SERVICIOS','description'=>'Atenciones de servicios de farmacia, ingresos y estados',
            'meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Servicio',$servicioNombre],['Estado',$this->estado ?: 'Todos'],['Generado',now()->format('d/m/Y H:i')]],
            'summary'=>[['Total atenciones',$atenciones->count()],['Completadas',$completadas->count()],['Anuladas',$anuladas->count()],['Ingresos válidos Bs',$completadas->sum('total')],['Monto anulado Bs',$anuladas->sum('total')]],
            'detail_title'=>'DETALLE DE ATENCIONES','headers'=>['Nro atención','Fecha','Servicio','Cliente','Cant.','Precio Bs','Subtotal Bs','Descuento Bs','Total Bs','Método','Usuario','Estado'],
            'rows'=>$rows,'money_columns'=>['F','G','H','I'],'status_column'=>'L','footer'=>'Reporte de servicios',
            'widths'=>['A'=>18,'B'=>18,'C'=>28,'D'=>26,'E'=>10,'F'=>13,'G'=>13,'H'=>13,'I'=>13,'J'=>18,'K'=>20,'L'=>14],
        ];
    }
}
