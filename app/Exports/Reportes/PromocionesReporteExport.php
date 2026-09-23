<?php

namespace App\Exports\Reportes;

use App\Models\DetalleVentaPromocion;

class PromocionesReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin) {}

    protected function buildReport(): array
    {
        $detalles = DetalleVentaPromocion::with(['venta.usuario','venta.cliente','promocion'])
            ->whereHas('venta', function ($q) {
                $q->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin);
            })->latest()->get();
        $rows = $detalles->map(fn ($detalle) => [
            $detalle->venta->fecha_hora?->format('d/m/Y H:i') ?? '-',
            $detalle->venta->numero_venta ?? '-',
            $detalle->promocion->nombre ?? 'Promoción eliminada',
            $detalle->venta->cliente->nombre ?? 'Consumidor final',
            $detalle->venta->usuario->nombre ?? '-',
            $detalle->cantidad,
            $detalle->precio_unitario,
            $detalle->subtotal,
            ucfirst($detalle->venta->estado ?? '-'),
        ])->values()->all();
        return [
            'sheet_title'=>'Promociones','subtitle'=>'REPORTE DE PROMOCIONES','description'=>'Promociones vendidas e ingresos generados',
            'meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Generado',now()->format('d/m/Y H:i')]],
            'summary'=>[['Promociones vendidas',$detalles->sum('cantidad')],['Total generado Bs',$detalles->sum('subtotal')],['Registros',$detalles->count()]],
            'detail_title'=>'DETALLE DE PROMOCIONES VENDIDAS','headers'=>['Fecha','Nro venta','Promoción','Cliente','Vendedor','Cantidad','Precio Bs','Subtotal Bs','Estado'],
            'rows'=>$rows,'money_columns'=>['G','H'],'status_column'=>'I','footer'=>'Reporte de promociones',
            'widths'=>['A'=>18,'B'=>16,'C'=>32,'D'=>25,'E'=>22,'F'=>12,'G'=>14,'H'=>14,'I'=>14],
        ];
    }
}
