<?php

namespace App\Exports\Reportes;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class VentasReporteExport implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function __construct(
        private $sucursal,
        private string $fechaInicio,
        private string $fechaFin,
        private ?string $estado = null,
        private ?string $buscar = null
    ) {}

    public function title(): string
    {
        return 'Ventas';
    }

    public function array(): array
    {
        $ventas = Venta::with([
                'cliente',
                'usuario',
                'pagos.metodoPago',
                'detalles.producto',
                'detalles.productoPresentacion.presentacion',
                'promociones.promocion',
            ])
            ->where('sucursal_id', $this->sucursal->id)
            ->when($this->buscar, function ($query) {
                $buscar = $this->buscar;

                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_venta', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($buscar) {
                            $clienteQuery->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('ci_nit', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('pagos.metodoPago', function ($metodoQuery) use ($buscar) {
                            $metodoQuery->where('nombre', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('detalles.producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('nombre_comercial', 'like', "%{$buscar}%")
                                ->orWhere('nombre_generico', 'like', "%{$buscar}%")
                                ->orWhere('concentracion', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('detalles.productoPresentacion', function ($presentacionQuery) use ($buscar) {
                            $presentacionQuery->where('nombre_mostrado', 'like', "%{$buscar}%");
                        })
                        ->orWhereHas('promociones.promocion', function ($promocionQuery) use ($buscar) {
                            $promocionQuery->where('nombre', 'like', "%{$buscar}%");
                        });
                });
            })
            ->whereDate('fecha_hora', '>=', $this->fechaInicio)
            ->whereDate('fecha_hora', '<=', $this->fechaFin)
            ->when($this->estado, function ($query) {
                $query->where('estado', $this->estado);
            })
            ->latest('fecha_hora')
            ->get();

        $ventasCompletadas = $ventas->where('estado', 'completada');
        $ventasAnuladas = $ventas->where('estado', 'anulada');

        $rows = [];

        $rows[] = ['SANTO REMEDIO'];
        $rows[] = ['REPORTE DE VENTAS'];
        $rows[] = ['Reporte administrativo generado para control de ventas, pagos, productos y promociones'];
        $rows[] = array_fill(0, 12, '');

        $rows[] = ['DATOS DEL REPORTE'];
        $rows[] = ['Sucursal', $this->sucursal->nombre ?? 'Sin sucursal', 'Desde', $this->fechaInicio, 'Hasta', $this->fechaFin];
        $rows[] = ['Estado', $this->estado ?: 'Todos', 'Búsqueda', $this->buscar ?: 'Sin búsqueda', 'Generado', now()->format('d/m/Y H:i')];
        $rows[] = array_fill(0, 12, '');

        $rows[] = ['RESUMEN EJECUTIVO'];
        $rows[] = ['Indicador', 'Valor', 'Indicador', 'Valor', 'Indicador', 'Valor'];
        $rows[] = [
            'Ventas completadas',
            $ventasCompletadas->count(),
            'Ventas anuladas',
            $ventasAnuladas->count(),
            'Registros encontrados',
            $ventas->count(),
        ];
        $rows[] = [
            'Total válido Bs',
            $ventasCompletadas->sum('total'),
            'Descuentos Bs',
            $ventasCompletadas->sum('descuento_total'),
            'Total anulado Bs',
            $ventasAnuladas->sum('total'),
        ];
        $rows[] = array_fill(0, 12, '');

        $rows[] = ['DETALLE DE VENTAS', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = [
            'Nro venta',
            'Fecha',
            'Cliente',
            'CI/NIT',
            'Vendedor',
            'Método',
            'Productos vendidos',
            'Promociones',
            'Subtotal Bs',
            'Descuento Bs',
            'Total Bs',
            'Estado',
        ];

        foreach ($ventas as $venta) {
            $metodosPago = $venta->pagos
                ->map(fn ($pago) => $pago->metodoPago->nombre ?? '-')
                ->filter()
                ->unique()
                ->implode(', ');

            $productos = $venta->detalles
                ->map(function ($detalle) {
                    $producto = $detalle->producto->nombre_comercial ?? '-';
                    $presentacion = $detalle->productoPresentacion->presentacion->nombre ?? '';
                    $cantidad = $detalle->cantidad ?? 0;

                    return trim($producto . ' ' . $presentacion) . ' x' . $cantidad;
                })
                ->implode("\n");

            $promociones = $venta->promociones
                ->map(function ($detalle) {
                    $nombre = $detalle->promocion->nombre ?? '-';
                    $cantidad = $detalle->cantidad ?? 0;

                    return $nombre . ' x' . $cantidad;
                })
                ->implode("\n");

            $rows[] = [
                $venta->numero_venta ?? '-',
                $venta->fecha_hora?->format('d/m/Y H:i') ?? '-',
                $venta->cliente->nombre ?? 'Consumidor final',
                $venta->cliente->ci_nit ?? '-',
                $venta->usuario->nombre ?? '-',
                $metodosPago ?: '-',
                $productos ?: '-',
                $promociones ?: '-',
                $venta->subtotal,
                $venta->descuento_total,
                $venta->total,
                ucfirst($venta->estado),
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 17,
            'C' => 26,
            'D' => 13,
            'E' => 20,
            'F' => 18,
            'G' => 38,
            'H' => 30,
            'I' => 13,
            'J' => 13,
            'K' => 13,
            'L' => 13,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();

                $moradoOscuro = '3F2A66';
                $morado = '5B3F8C';
                $moradoSuave = 'EBE4F3';
                $borde = 'DED7EA';
                $verdeFondo = 'ECFDF5';
                $verdeTexto = '15803D';
                $rojoFondo = 'FEE2E2';
                $rojoTexto = '991B1B';
                $amarilloFondo = 'FFFBEB';
                $amarilloTexto = '92400E';
                $grisFondo = 'F8FAFC';
                $grisTexto = '64748B';

                $sheet->setShowGridlines(false);
                $sheet->getTabColor()->setRGB($morado);
                $sheet->getDefaultRowDimension()->setRowHeight(22);

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true);

                $sheet->getPageMargins()
                    ->setTop(0.25)
                    ->setRight(0.15)
                    ->setLeft(0.15)
                    ->setBottom(0.25)
                    ->setHeader(0.15)
                    ->setFooter(0.15);

                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(14, 15);

                $sheet->getHeaderFooter()
                    ->setOddFooter('&L Santo Remedio&C Reporte de ventas&R Página &P de &N');

                $sheet->getPageSetup()->setPrintArea("A1:L{$highestRow}");

                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');

                $sheet->getStyle('A1:L3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $moradoOscuro],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A1')->getFont()->setSize(22);
                $sheet->getStyle('A2')->getFont()->setSize(16);
                $sheet->getStyle('A3')->getFont()->setSize(10);
                $sheet->getStyle('A3')->getFont()->setBold(false);

                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getRowDimension(3)->setRowHeight(22);

                $sheet->mergeCells('A5:L5');
                $sheet->getStyle('A5:L5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $morado],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A6:F7')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $grisFondo],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => $borde],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A6:A7')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('C6:C7')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('E6:E7')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);

                $sheet->getStyle('B6:B7')->getFont()->getColor()->setRGB($grisTexto);
                $sheet->getStyle('D6:D7')->getFont()->getColor()->setRGB($grisTexto);
                $sheet->getStyle('F6:F7')->getFont()->getColor()->setRGB($grisTexto);

                $sheet->mergeCells('A9:L9');
                $sheet->getStyle('A9:L9')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $morado],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A10:F12')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => $borde],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle('A10:F10')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $moradoOscuro],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A11:A12')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('C11:C12')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('E11:E12')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);

                $sheet->getStyle('B11:B12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $verdeTexto],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $verdeFondo],
                    ],
                ]);

                $sheet->getStyle('D11:D12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $amarilloTexto],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $amarilloFondo],
                    ],
                ]);

                $sheet->getStyle('F11:F12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $rojoTexto],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $rojoFondo],
                    ],
                ]);


                $sheet->getStyle('B12')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00 "Bs"');

                $sheet->getStyle('D12')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00 "Bs"');

                $sheet->getStyle('F12')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00 "Bs"');

                $sheet->mergeCells('A14:L14');
                $sheet->getStyle('A14:L14')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 14,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $moradoOscuro],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A15:L15')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $moradoOscuro],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => $moradoOscuro],
                        ],
                    ],
                ]);

                $sheet->getRowDimension(15)->setRowHeight(36);

                if ($highestRow >= 16) {
                    $sheet->getStyle("A16:L{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => $borde],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                    ]);

                    $sheet->getStyle("I16:K{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00 "Bs"');

                    for ($row = 16; $row <= $highestRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FBFAFD'],
                                ],
                            ]);
                        }

                        $estado = strtolower((string) $sheet->getCell("L{$row}")->getValue());

                        if ($estado === 'anulada') {
                            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $rojoFondo],
                                ],
                                'font' => [
                                    'color' => ['rgb' => $rojoTexto],
                                ],
                            ]);
                        }

                        if ($estado === 'completada') {
                            $sheet->getStyle("L{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $verdeFondo],
                                ],
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => $verdeTexto],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                ],
                            ]);
                        }

                        if ($estado === 'anulada') {
                            $sheet->getStyle("L{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $rojoFondo],
                                ],
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => $rojoTexto],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                ],
                            ]);
                        }

                        $sheet->getRowDimension($row)->setRowHeight(34);
                    }
                }

                $sheet->getStyle('A:L')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A:L')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('I:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->freezePane('A16');
                $sheet->setAutoFilter("A15:L{$highestRow}");
                $sheet->setSelectedCell('A1');
            },
        ];
    }
}