<?php

namespace App\Exports\Reportes;

use App\Models\Compra;

class DeudasProveedoresReporteExport extends BaseDesignedReportExport
{
    public function __construct(private $sucursal, private string $fechaInicio, private string $fechaFin, private ?string $buscar = null) {}

    protected function buildReport(): array
    {
        $compras = Compra::with(['proveedor','usuario','sucursal'])
            ->where('sucursal_id', $this->sucursal->id)
            ->where('estado', 'pendiente')
            ->where('saldo_pendiente', '>', 0)
            ->whereDate('fecha_compra', '>=', $this->fechaInicio)
            ->whereDate('fecha_compra', '<=', $this->fechaFin)
            ->when($this->buscar, function ($query) {
                $buscar = $this->buscar;
                $query->whereHas('proveedor', fn ($q) => $q->where('nombre','like',"%{$buscar}%")->orWhere('nit','like',"%{$buscar}%")->orWhere('telefono','like',"%{$buscar}%"));
            })
            ->latest('fecha_compra')
            ->get();

        $rows = $compras->map(fn ($compra) => [
            $compra->numero_compra ?? $compra->id,
            $compra->fecha_compra?->format('d/m/Y H:i') ?? '-',
            $compra->proveedor->nombre ?? '-',
            $compra->proveedor->nit ?? '-',
            $compra->proveedor->telefono ?? '-',
            $compra->usuario->nombre ?? '-',
            $compra->total,
            $compra->monto_pagado,
            $compra->saldo_pendiente,
            ucfirst($compra->estado),
            $compra->observacion ?? '-',
        ])->values()->all();

        return [
            'sheet_title'=>'Deudas proveedores','subtitle'=>'REPORTE DE DEUDAS A PROVEEDORES','description'=>'Compras pendientes agrupadas por proveedor y saldo pendiente',
            'meta'=>[['Sucursal',$this->sucursal->nombre ?? 'Sin sucursal'],['Desde',$this->fechaInicio],['Hasta',$this->fechaFin],['Búsqueda',$this->buscar ?: 'Todos'],['Generado',now()->format('d/m/Y H:i')]],
            'summary'=>[['Compras pendientes',$compras->count()],['Total comprado Bs',$compras->sum('total')],['Total pagado Bs',$compras->sum('monto_pagado')],['Saldo pendiente Bs',$compras->sum('saldo_pendiente')],['Proveedores con deuda',$compras->pluck('proveedor_id')->unique()->count()]],
            'detail_title'=>'DETALLE DE DEUDAS','headers'=>['Nro compra','Fecha','Proveedor','NIT','Teléfono','Usuario','Total Bs','Pagado Bs','Saldo Bs','Estado','Observación'],
            'rows'=>$rows,'money_columns'=>['G','H','I'],'status_column'=>'J','footer'=>'Reporte de deudas a proveedores',
            'widths'=>['A'=>16,'B'=>18,'C'=>30,'D'=>16,'E'=>16,'F'=>20,'G'=>14,'H'=>14,'I'=>14,'J'=>14,'K'=>32],
        ];
    }
}
