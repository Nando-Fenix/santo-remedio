<?php

namespace App\Exports\Reportes;

use App\Models\AtencionServicio;
use App\Models\MetodoPago;
use App\Models\PagoVenta;

class MetodosPagoReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $metodoPagoId = null) {}

    protected function buildReport(): array
    {
        $metodos = MetodoPago::where('estado','activo')->orderBy('nombre')->get();
        $pagosVentas = PagoVenta::with(['metodoPago','venta'])->whereHas('venta', function ($q) { $q->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin); })->when($this->metodoPagoId, fn ($q) => $q->where('metodo_pago_id',$this->metodoPagoId))->get();
        $servicios = AtencionServicio::with(['metodoPago'])->where('sucursal_id',$this->sucursal->id)->where('estado','completada')->whereDate('fecha_hora','>=',$this->fechaInicio)->whereDate('fecha_hora','<=',$this->fechaFin)->when($this->metodoPagoId, fn ($q) => $q->where('metodo_pago_id',$this->metodoPagoId))->get();
        $rows=[]; foreach ($metodos as $m) { if ($this->metodoPagoId && (string)$this->metodoPagoId !== (string)$m->id) continue; $pv=$pagosVentas->where('metodo_pago_id',$m->id); $sv=$servicios->where('metodo_pago_id',$m->id); if($pv->count()==0 && $sv->count()==0) continue; $rows[] = [$m->nombre, ucfirst($m->tipo ?? '-'), $pv->count(), $pv->sum('monto'), $sv->count(), $sv->sum('total'), $pv->sum('monto')+$sv->sum('total')]; }
        return ['sheet_title'=>'Métodos de pago','subtitle'=>'REPORTE DE MÉTODOS DE PAGO','description'=>'Ingresos de ventas y servicios agrupados por forma de pago','meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Método',$this->metodoPagoId ?: 'Todos'],['Generado',now()->format('d/m/Y H:i')]],'summary'=>[['Total ventas Bs',$pagosVentas->sum('monto')],['Total servicios Bs',$servicios->sum('total')],['Total general Bs',$pagosVentas->sum('monto')+$servicios->sum('total')],['Cantidad ventas',$pagosVentas->count()],['Cantidad servicios',$servicios->count()]],'detail_title'=>'RESUMEN POR MÉTODO','headers'=>['Método','Tipo','Cant. ventas','Total ventas Bs','Cant. servicios','Total servicios Bs','Total general Bs'],'rows'=>$rows,'money_columns'=>['D','F','G'],'footer'=>'Reporte de métodos de pago','widths'=>['A'=>25,'B'=>16,'C'=>14,'D'=>16,'E'=>16,'F'=>18,'G'=>18]];
    }
}
