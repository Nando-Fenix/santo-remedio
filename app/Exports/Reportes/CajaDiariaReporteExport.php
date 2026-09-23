<?php

namespace App\Exports\Reportes;

use App\Models\AtencionServicio;
use App\Models\Caja;
use App\Models\MovimientoCaja;
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

class CajaDiariaReporteExport implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function __construct(
        private $sucursal,
        private string $fecha
    ) {}

    public function title(): string
    {
        return 'Caja diaria';
    }

    public function array(): array
    {
        $cajas = Caja::with([
                'sucursal',
                'turno',
            ])
            ->where('sucursal_id', $this->sucursal->id)
            ->whereDate('fecha_apertura', $this->fecha)
            ->orderByDesc('fecha_apertura')
            ->get();

        $resumenCajas = $cajas->map(function ($caja) {
            $ventasCompletadas = Venta::where('caja_id', $caja->id)
                ->where('estado', 'completada')
                ->sum('total');

            $ventasAnuladas = Venta::where('caja_id', $caja->id)
                ->where('estado', 'anulada')
                ->sum('total');

            $serviciosCompletados = AtencionServicio::where('caja_id', $caja->id)
                ->where('estado', 'completada')
                ->sum('total');

            $serviciosAnulados = AtencionServicio::where('caja_id', $caja->id)
                ->where('estado', 'anulada')
                ->sum('total');

            $egresos = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo_movimiento', 'egreso')
                ->sum('monto');

            $reembolsos = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo_movimiento', 'reembolso')
                ->sum('monto');

            $anulaciones = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo_movimiento', 'anulacion')
                ->sum('monto');

            return [
                'caja' => $caja,
                'ventas_completadas' => $ventasCompletadas,
                'ventas_anuladas' => $ventasAnuladas,
                'servicios_completados' => $serviciosCompletados,
                'servicios_anulados' => $serviciosAnulados,
                'egresos' => $egresos,
                'reembolsos' => $reembolsos,
                'anulaciones' => $anulaciones,
                'ingresos_validos' => $ventasCompletadas + $serviciosCompletados,
            ];
        });

        $totales = [
            'ventas_completadas' => $resumenCajas->sum('ventas_completadas'),
            'ventas_anuladas' => $resumenCajas->sum('ventas_anuladas'),
            'servicios_completados' => $resumenCajas->sum('servicios_completados'),
            'servicios_anulados' => $resumenCajas->sum('servicios_anulados'),
            'egresos' => $resumenCajas->sum('egresos'),
            'reembolsos' => $resumenCajas->sum('reembolsos'),
            'anulaciones' => $resumenCajas->sum('anulaciones'),
            'ingresos_validos' => $resumenCajas->sum('ingresos_validos'),
        ];

        $rows = [];

        $rows[] = ['SANTO REMEDIO'];
        $rows[] = ['REPORTE DE CAJA DIARIA'];
        $rows[] = ['Control administrativo de ingresos, egresos, anulaciones y cierre de caja'];
        $rows[] = array_fill(0, 12, '');

        $rows[] = ['DATOS DEL REPORTE', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = [
            'Sucursal',
            $this->sucursal->nombre ?? 'Sin sucursal',
            'Fecha',
            $this->fecha,
            'Generado',
            now()->format('d/m/Y H:i'),
            'Cajas encontradas',
            $cajas->count(),
            'Formato',
            'Excel .xlsx',
            '',
            '',
        ];

        $rows[] = array_fill(0, 12, '');

        $rows[] = ['RESUMEN EJECUTIVO', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Indicador', 'Valor', 'Indicador', 'Valor', 'Indicador', 'Valor', '', '', '', '', '', ''];
        $rows[] = [
            'Ventas completadas',
            $totales['ventas_completadas'],
            'Servicios completados',
            $totales['servicios_completados'],
            'Ingresos válidos',
            $totales['ingresos_validos'],
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $rows[] = [
            'Egresos',
            $totales['egresos'],
            'Reembolsos',
            $totales['reembolsos'],
            'Anulaciones',
            $totales['anulaciones'],
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $rows[] = [
            'Ventas anuladas',
            $totales['ventas_anuladas'],
            'Servicios anulados',
            $totales['servicios_anulados'],
            'Movimiento neto',
            $totales['ingresos_validos'] - $totales['egresos'] - $totales['reembolsos'] - $totales['anulaciones'],
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        $rows[] = array_fill(0, 12, '');

        $rows[] = ['DETALLE DE CAJAS', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = [
            'Caja ID',
            'Turno',
            'Estado',
            'Apertura',
            'Cierre',
            'Monto inicial Bs',
            'Ventas Bs',
            'Servicios Bs',
            'Ingresos válidos Bs',
            'Egresos Bs',
            'Anulaciones/Reembolsos Bs',
            'Total final Bs',
        ];

        foreach ($resumenCajas as $item) {
            $caja = $item['caja'];

            $anulacionesReembolsos = $item['ventas_anuladas']
                + $item['servicios_anulados']
                + $item['reembolsos']
                + $item['anulaciones'];

            $rows[] = [
                $caja->id,
                $caja->turno->nombre ?? 'Sin turno',
                ucfirst($caja->estado),
                $caja->fecha_apertura?->format('d/m/Y H:i') ?? '-',
                $caja->fecha_cierre?->format('d/m/Y H:i') ?? '-',
                $caja->monto_inicial,
                $item['ventas_completadas'],
                $item['servicios_completados'],
                $item['ingresos_validos'],
                $item['egresos'],
                $anulacionesReembolsos,
                $caja->total_final,
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 20,
            'C' => 14,
            'D' => 20,
            'E' => 20,
            'F' => 16,
            'G' => 15,
            'H' => 15,
            'I' => 18,
            'J' => 15,
            'K' => 23,
            'L' => 16,
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

                $sheet->getPageSetup()->setPrintArea("A1:L{$highestRow}");
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(14, 15);

                $sheet->getHeaderFooter()
                    ->setOddFooter('&L Santo Remedio&C Reporte de caja diaria&R Página &P de &N');

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
                    ],
                ]);

                $sheet->getStyle('A6:J6')->applyFromArray([
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
                ]);

                $sheet->getStyle('A6')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('C6')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('E6')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('G6')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('I6')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);

                $sheet->getStyle('B6')->getFont()->getColor()->setRGB($grisTexto);
                $sheet->getStyle('D6')->getFont()->getColor()->setRGB($grisTexto);
                $sheet->getStyle('F6')->getFont()->getColor()->setRGB($grisTexto);
                $sheet->getStyle('H6')->getFont()->getColor()->setRGB($grisTexto);
                $sheet->getStyle('J6')->getFont()->getColor()->setRGB($grisTexto);

                $sheet->mergeCells('A8:L8');
                $sheet->getStyle('A8:L8')->applyFromArray([
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
                    ],
                ]);

                $sheet->getStyle('A9:F12')->applyFromArray([
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

                $sheet->getStyle('A9:F9')->applyFromArray([
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

                $sheet->getStyle('A10:A12')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('C10:C12')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                $sheet->getStyle('E10:E12')->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);

                $sheet->getStyle('B10:B12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $verdeTexto],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $verdeFondo],
                    ],
                ]);

                $sheet->getStyle('D10:D12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $amarilloTexto],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $amarilloFondo],
                    ],
                ]);

                $sheet->getStyle('F10:F12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $rojoTexto],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $rojoFondo],
                    ],
                ]);

                $sheet->getStyle('B10:F12')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00 "Bs"');

                $sheet->mergeCells('A14:L14');
                $sheet->getStyle('A14:L14')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 13,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $moradoOscuro],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
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

                    $sheet->getStyle("F16:L{$highestRow}")
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

                        $estado = strtolower((string) $sheet->getCell("C{$row}")->getValue());

                        if ($estado === 'cerrada') {
                            $sheet->getStyle("C{$row}")->applyFromArray([
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

                        if ($estado === 'abierta') {
                            $sheet->getStyle("C{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $amarilloFondo],
                                ],
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => $amarilloTexto],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                ],
                            ]);
                        }

                        $sheet->getRowDimension($row)->setRowHeight(32);
                    }
                }

                $sheet->getStyle('A:L')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A:L')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('F:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->freezePane('A16');
                $sheet->setAutoFilter("A15:L{$highestRow}");
                $sheet->setSelectedCell('A1');
            },
        ];
    }
}