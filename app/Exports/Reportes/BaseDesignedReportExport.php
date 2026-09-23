<?php

namespace App\Exports\Reportes;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

abstract class BaseDesignedReportExport implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    protected array $reportData = [];
    protected int $detailTitleRow = 14;
    protected int $detailHeaderRow = 15;
    protected int $detailStartRow = 16;
    protected int $columnCount = 12;

    abstract protected function buildReport(): array;

    public function title(): string
    {
        $data = $this->getReportData();
        return substr($data['sheet_title'] ?? $data['subtitle'] ?? 'Reporte', 0, 31);
    }

    public function array(): array
    {
        $data = $this->getReportData();

        $headers = $data['headers'] ?? [];
        $this->columnCount = max(6, count($headers));

        $blank = fn () => array_fill(0, $this->columnCount, '');
        $pad = function (array $row) {
            return array_pad($row, $this->columnCount, '');
        };

        $rows = [];
        $rows[] = $pad([$data['brand'] ?? 'SANTO REMEDIO']);
        $rows[] = $pad([$data['subtitle'] ?? 'REPORTE']);
        $rows[] = $pad([$data['description'] ?? 'Reporte administrativo generado por el sistema']);
        $rows[] = $blank();

        $rows[] = $pad(['DATOS DEL REPORTE']);

        $meta = $data['meta'] ?? [];
        $metaChunks = array_chunk($meta, 3);
        if (empty($metaChunks)) {
            $metaChunks = [[]];
        }

        foreach ($metaChunks as $chunk) {
            $row = [];
            foreach ($chunk as $item) {
                $row[] = $item[0] ?? '';
                $row[] = $item[1] ?? '';
            }
            $rows[] = $pad($row);
        }

        $rows[] = $blank();

        $rows[] = $pad(['RESUMEN EJECUTIVO']);

        $summary = $data['summary'] ?? [];
        $summaryChunks = array_chunk($summary, 3);
        $summaryHeader = [];
        for ($i = 0; $i < 3; $i++) {
            $summaryHeader[] = 'Indicador';
            $summaryHeader[] = 'Valor';
        }
        $rows[] = $pad($summaryHeader);

        foreach ($summaryChunks as $chunk) {
            $row = [];
            foreach ($chunk as $item) {
                $row[] = $item[0] ?? '';
                $row[] = $item[1] ?? '';
            }
            $rows[] = $pad($row);
        }

        if (empty($summaryChunks)) {
            $rows[] = $pad(['Sin datos', '-']);
        }

        $rows[] = $blank();

        $this->detailTitleRow = count($rows) + 1;
        $rows[] = $pad([$data['detail_title'] ?? 'DETALLE']);
        $this->detailHeaderRow = count($rows) + 1;
        $rows[] = $pad($headers);
        $this->detailStartRow = count($rows) + 1;

        foreach (($data['rows'] ?? []) as $row) {
            $rows[] = $pad($row);
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        $data = $this->getReportData();

        if (!empty($data['widths'])) {
            return $data['widths'];
        }

        $widths = [];
        for ($i = 1; $i <= $this->columnCount; $i++) {
            $widths[Coordinate::stringFromColumnIndex($i)] = 18;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data = $this->getReportData();

                $highestRow = $sheet->getHighestRow();
                $lastColumn = Coordinate::stringFromColumnIndex($this->columnCount);

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

                $sheet->getPageSetup()->setPrintArea("A1:{$lastColumn}{$highestRow}");
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($this->detailTitleRow, $this->detailHeaderRow);

                $footerTitle = $data['footer'] ?? ($data['subtitle'] ?? 'Reporte');
                $sheet->getHeaderFooter()
                    ->setOddFooter('&L Santo Remedio&C ' . $footerTitle . '&R Página &P de &N');

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->mergeCells("A3:{$lastColumn}3");

                $sheet->getStyle("A1:{$lastColumn}3")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $moradoOscuro]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A1')->getFont()->setSize(22);
                $sheet->getStyle('A2')->getFont()->setSize(16);
                $sheet->getStyle('A3')->getFont()->setSize(10)->setBold(false);
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getRowDimension(3)->setRowHeight(22);

                $sheet->mergeCells("A5:{$lastColumn}5");
                $this->sectionStyle($sheet, "A5:{$lastColumn}5", $morado);

                $summaryTitleRow = 7 + count(array_chunk($data['meta'] ?? [], 3));
                if (($data['meta'] ?? []) === []) {
                    $summaryTitleRow = 8;
                }

                // Datos del reporte: filas 6 hasta antes de la línea vacía previa al resumen.
                for ($row = 6; $row <= $summaryTitleRow - 2; $row++) {
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $grisFondo]],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borde]]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    for ($col = 1; $col <= $this->columnCount; $col += 2) {
                        $letter = Coordinate::stringFromColumnIndex($col);
                        $sheet->getStyle("{$letter}{$row}")->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                    }
                    for ($col = 2; $col <= $this->columnCount; $col += 2) {
                        $letter = Coordinate::stringFromColumnIndex($col);
                        $sheet->getStyle("{$letter}{$row}")->getFont()->getColor()->setRGB($grisTexto);
                    }
                }

                $summaryTitleRow = $this->findRowByValue($sheet, 'RESUMEN EJECUTIVO') ?: $summaryTitleRow;
                $summaryHeaderRow = $summaryTitleRow + 1;
                $detailTitleRow = $this->detailTitleRow;
                $detailHeaderRow = $this->detailHeaderRow;
                $detailStartRow = $this->detailStartRow;

                $sheet->mergeCells("A{$summaryTitleRow}:{$lastColumn}{$summaryTitleRow}");
                $this->sectionStyle($sheet, "A{$summaryTitleRow}:{$lastColumn}{$summaryTitleRow}", $morado);

                $summaryEndRow = $detailTitleRow - 2;
                if ($summaryEndRow >= $summaryHeaderRow) {
                    $sheet->getStyle("A{$summaryHeaderRow}:{$lastColumn}{$summaryEndRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borde]]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);

                    $sheet->getStyle("A{$summaryHeaderRow}:{$lastColumn}{$summaryHeaderRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $moradoOscuro]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    for ($row = $summaryHeaderRow + 1; $row <= $summaryEndRow; $row++) {
                        for ($col = 1; $col <= $this->columnCount; $col += 2) {
                            $letter = Coordinate::stringFromColumnIndex($col);
                            $sheet->getStyle("{$letter}{$row}")->getFont()->setBold(true)->getColor()->setRGB($moradoOscuro);
                        }
                        for ($col = 2; $col <= $this->columnCount; $col += 2) {
                            $letter = Coordinate::stringFromColumnIndex($col);
                            $sheet->getStyle("{$letter}{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $verdeTexto]],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $verdeFondo]],
                            ]);
                        }
                    }
                }

                $sheet->mergeCells("A{$detailTitleRow}:{$lastColumn}{$detailTitleRow}");
                $this->sectionStyle($sheet, "A{$detailTitleRow}:{$lastColumn}{$detailTitleRow}", $moradoOscuro, 13);

                $sheet->getStyle("A{$detailHeaderRow}:{$lastColumn}{$detailHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $moradoOscuro]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $moradoOscuro]]],
                ]);
                $sheet->getRowDimension($detailHeaderRow)->setRowHeight(36);

                if ($highestRow >= $detailStartRow) {
                    $sheet->getStyle("A{$detailStartRow}:{$lastColumn}{$highestRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borde]]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    foreach (($data['money_columns'] ?? []) as $column) {
                        $sheet->getStyle("{$column}{$detailStartRow}:{$column}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00 "Bs"');
                        $sheet->getStyle("{$column}{$detailStartRow}:{$column}{$highestRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    foreach (($data['summary_money_cells'] ?? []) as $cell) {
                        $sheet->getStyle($cell)
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00 "Bs"');
                    }

                    $statusColumn = $data['status_column'] ?? null;

                    for ($row = $detailStartRow; $row <= $highestRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FBFAFD']],
                            ]);
                        }

                        if ($statusColumn) {
                            $estado = strtolower((string) $sheet->getCell("{$statusColumn}{$row}")->getValue());

                            if (in_array($estado, ['anulada', 'anulado', 'vencido', 'agotado'], true)) {
                                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rojoFondo]],
                                    'font' => ['color' => ['rgb' => $rojoTexto]],
                                ]);
                            }

                            if (in_array($estado, ['completada', 'pagada', 'cerrada', 'activo', 'correcto'], true)) {
                                $sheet->getStyle("{$statusColumn}{$row}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $verdeFondo]],
                                    'font' => ['bold' => true, 'color' => ['rgb' => $verdeTexto]],
                                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                                ]);
                            }

                            if (in_array($estado, ['pendiente', 'abierta', 'stock bajo', 'proximo vencer', 'próximo vencer'], true)) {
                                $sheet->getStyle("{$statusColumn}{$row}")->applyFromArray([
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $amarilloFondo]],
                                    'font' => ['bold' => true, 'color' => ['rgb' => $amarilloTexto]],
                                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                                ]);
                            }
                        }

                        $sheet->getRowDimension($row)->setRowHeight(32);
                    }
                }

                $sheet->getStyle("A:{$lastColumn}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A:{$lastColumn}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->freezePane("A{$detailStartRow}");
                if ($highestRow >= $detailHeaderRow) {
                    $sheet->setAutoFilter("A{$detailHeaderRow}:{$lastColumn}{$highestRow}");
                }
                $sheet->setSelectedCell('A1');
            },
        ];
    }

    protected function getReportData(): array
    {
        if (!$this->reportData) {
            $this->reportData = $this->buildReport();
        }

        return $this->reportData;
    }

    protected function sectionStyle($sheet, string $range, string $color, int $size = 12): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => $size],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    protected function findRowByValue($sheet, string $value): ?int
    {
        $highestRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) {
            if ((string) $sheet->getCell("A{$row}")->getValue() === $value) {
                return $row;
            }
        }

        return null;
    }
}
