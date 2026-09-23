<?php

namespace App\Exports\Reportes;

use App\Models\Compra;

class ComprasReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $estado = null) {}

    protected function buildReport(): array
    {
        $compras = Compra::with(['proveedor','usuario','sucursal'])
            ->where('sucursal_id', $this->sucursal->id)
            ->whereDate('fecha_compra', '>=', $this->fechaInicio)
            ->whereDate('fecha_compra', '<=', $this->fechaFin)
            ->when($this->estado, fn ($query) => $query->where('estado', $this->estado))
            ->latest('fecha_compra')
            ->get();

        $validas = $compras->where('estado', '!=', 'anulada');
        $rows = $compras->map(fn ($compra) => [
            $compra->numero_compra ?? $compra->id,
            $compra->fecha_compra?->format('d/m/Y H:i') ?? '-',
            $compra->proveedor->nombre ?? '-',
            $compra->usuario->nombre ?? '-',
            $compra->subtotal,
            $compra->descuento,
            $compra->total,
            $compra->monto_pagado,
            $compra->saldo_pendiente,
            ucfirst($compra->estado),
            $compra->observacion ?? '-',
        ])->values()->all();

        return [
            'sheet_title'=>'Compras','subtitle'=>'REPORTE DE COMPRAS','description'=>'Compras, pagos, saldos pendientes y anulaciones',
            'meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Estado',$this->estado ?: 'Todos'],['Generado',now()->format('d/m/Y H:i')]],
            'summary'=>[['Cantidad de compras',$compras->count()],['Compras pagadas',$compras->where('estado','pagada')->count()],['Compras pendientes',$compras->where('estado','pendiente')->count()],['Total comprado Bs',$validas->sum('total')],['Total pagado Bs',$validas->sum('monto_pagado')],['Saldo pendiente Bs',$validas->sum('saldo_pendiente')],['Compras anuladas',$compras->where('estado','anulada')->count()],['Total anulado Bs',$compras->where('estado','anulada')->sum('total')]],
            'detail_title'=>'DETALLE DE COMPRAS','headers'=>['Nro compra','Fecha','Proveedor','Usuario','Subtotal Bs','Descuento Bs','Total Bs','Pagado Bs','Saldo Bs','Estado','Observación'],
            'rows'=>$rows,'money_columns'=>['E','F','G','H','I'],'status_column'=>'J','footer'=>'Reporte de compras',
            'widths'=>['A'=>16,'B'=>18,'C'=>28,'D'=>22,'E'=>14,'F'=>14,'G'=>14,'H'=>14,'I'=>14,'J'=>14,'K'=>32],
        ];
    }
}
