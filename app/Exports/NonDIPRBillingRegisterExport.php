<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class NonDIPRBillingRegisterExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    protected $bills;
    protected $grandTotalAmount;
    protected $rowCount;

    public function __construct($bills, $grandTotalAmount)
    {
        $this->bills = $bills;
        $this->grandTotalAmount = $grandTotalAmount;
        $this->rowCount = 0;
    }

    public function collection()
    {
        // Add the Grand Total row, but exclude "Slno" and "Department" for the Grand Total
        $this->bills->push((object) [
            'created_at' => '',
            'dept_name' => '',
            'news_name' => '',
            'mipr_no' => '',
            'mipr_date' => '',
            'bill_no' => '',
            'bill_date' => '',
            'sizes' => '',
            'total_amount' => '₹ ' . number_format($this->grandTotalAmount, 2) // Add ₹ symbol and formatted amount
        ]);

        return $this->bills;
    }

    public function headings(): array
    {
        return [
            'Sl. No',
            'Entering Date',
            'Branch of Department',
            'Organizations Issued',
            'MIPR No',
            'MIPR Date',
            'Bill No',
            'Bill Date',
            'Size/Sec',
            'Amount',
        ];
    }

    public function map($bill): array
    {
        $this->rowCount++; // Increment row count for each bill

        // If this is the last row (Grand Total), exclude the serial number
        if ($this->rowCount == $this->bills->count()) {
            return [
                '',  // Empty serial number for the Grand Total row
                '',  // Empty entering date for the Grand Total row
                '',  // Empty department for the Grand Total row
                '',  // Empty organization for the Grand Total row
                '',  // Empty MIPR No for the Grand Total row
                '',  // Empty MIPR Date for the Grand Total row
                '',  // Empty Bill No for the Grand Total row
                '',  // Empty Bill Date for the Grand Total row
                '',  // Empty Size/Sec for the Grand Total row
                '₹ ' . number_format($this->grandTotalAmount, 2),  // Grand Total amount
            ];
        }

        // Return values for normal rows
        return [
            $this->rowCount,
            !empty($bill->created_at) ? $bill->created_at->format('d-m-Y') : '',
            $bill->dept_name,
            $bill->news_name,
            $bill->mipr_no,
            !empty($bill->issue_date) ? $bill->issue_date->format('d-m-Y') : '',
            $bill->bill_no,
            !empty($bill->bill_date) ? $bill->bill_date->format('d-m-Y') : '',
            !empty($bill->sizes) ? $bill->sizes : '',
            $bill->total_amount,
        ];
    }


    public function styles(Worksheet $sheet)
    {
        // Title and styles
        $sheet->setCellValue('A1', 'Bills not paid by DIPR');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => Color::COLOR_BLACK],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style the headings in A3
        $sheet->getStyle('A3:J3')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A3:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Wrap text for the headers
        $sheet->getStyle('A3:J3')->getAlignment()->setWrapText(true);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(13);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(15);

        // Apply center alignment and wrap text to all columns (except for the Amount column)
        $sheet->getStyle('A4:I' . ($this->rowCount + 3))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Left-align the "Branch of Department" column (column C)
        $sheet->getStyle('C4:C' . ($this->rowCount + 3)) // From row 4 (after heading) to the last row
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Right align the "Amount" column (column J)
        $sheet->getStyle('J4:J' . ($this->rowCount + 3)) // From row 4 (after heading) to the last row
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format the amount in INR format (with commas and 2 decimal places)
        $sheet->getStyle('J4:J' . ($this->rowCount + 3))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00'); // Add commas and decimal precision

        // Style for Grand Total row (exclude serial number and department)
        $lastRow = $this->rowCount + 3; // 3 for the title and headings
        $sheet->getStyle("A{$lastRow}:J{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['argb' => Color::COLOR_BLACK],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Wrap text for the Grand Total row
        $sheet->getStyle("A{$lastRow}:J{$lastRow}")
            ->getAlignment()
            ->setWrapText(true);

        return [];
    }

    public function startCell(): string
    {
        return 'A3'; // Start headings from A3, so title stays at A1
    }
}
