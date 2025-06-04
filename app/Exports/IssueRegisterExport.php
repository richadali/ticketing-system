<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IssueRegisterExport implements WithMultipleSheets
{
    protected $advertisements;

    public function __construct($advertisements)
    {
        $this->advertisements = $advertisements;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Add all data to one sheet
        $sheets[] = new IssueRegisterSheet($this->advertisements, 'All Issues');

        // Group advertisements by month and year
        $groupedAdvertisements = $this->advertisements->groupBy(function ($advertisement) {
            return Carbon::parse($advertisement['issue_date'])->format('F Y'); // Group by month and year
        });

        // Create a sheet for each month
        foreach ($groupedAdvertisements as $month => $ads) {
            $sheets[] = new IssueRegisterSheet($ads, $month); // Use month name for sheet title
        }

        return $sheets;
    }
}


class IssueRegisterSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function startCell(): string
    {
        return 'A1'; // Start at cell A1
    }

    public function collection()
    {
        $groupedData = [];
        $lastMiprNo = null;

        foreach ($this->data as $row) {
            // Log the row data as an array
            \Log::info('Row data:', ['data' => $row]);

            // Group by MIPR No to prevent merging rows
            if ($lastMiprNo !== $row['mipr_no']) {
                $groupedData[] = [
                    'mipr_no' => $row['mipr_no'],
                    'issue_date' => $row['issue_date'],
                    'dept_name' => $row['dept_name'],
                    'size_seconds' => $row['size_seconds'],
                    'subject' => $row['subject'],
                    'ref_no_date' => $row['ref_no_date'],
                    'newspaper' => $row['newspaper'],
                    'positively_on' => $row['positively_on'],
                    'no_of_insertions' => $row['no_of_insertions'],
                    'remarks' => $row['remarks'],
                ];
                $lastMiprNo = $row['mipr_no'];
            } else {
                $groupedData[] = [
                    'mipr_no' => '',
                    'issue_date' => '',
                    'dept_name' => '',
                    'size_seconds' => $row['size_seconds'],
                    'subject' => '',
                    'ref_no_date' => '',
                    'newspaper' => $row['newspaper'],
                    'positively_on' => $row['positively_on'],
                    'no_of_insertions' => $row['no_of_insertions'],
                    'remarks' => '',
                ];
            }
        }

        return collect($groupedData);
    }




    public function headings(): array
    {
        return [
            'MIPR No',
            'Date of Issue',
            'Name of Department Concerned',
            'Size/Seconds',
            'Subject',
            'Ref. No & Date',
            'Newspaper',
            'Positively On',
            'No of Insertions',
            'Remarks',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling for the header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // MIPR No
            'B' => 20, // Date of Issue
            'C' => 40, // Name of Department Concerned
            'D' => 15, // Size/Seconds
            'E' => 30, // Subject
            'F' => 30, // Ref. No & Date
            'G' => 25, // Newspaper
            'H' => 25, // Positively On
            'I' => 20, // No of Insertions
            'J' => 30, // Remarks
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowIndex = 2; // Start from the first data row
                $lastMiprNo = null;
                $mergeStartRow = 2;

                foreach ($this->data as $index => $row) {
                    if ($lastMiprNo !== $row['mipr_no']) {
                        // Merge cells for non-split columns when MIPR No changes
                        if ($index > 0) {
                            $sheet->mergeCells("A{$mergeStartRow}:A" . ($rowIndex - 1));
                            $sheet->mergeCells("B{$mergeStartRow}:B" . ($rowIndex - 1));
                            $sheet->mergeCells("C{$mergeStartRow}:C" . ($rowIndex - 1));
                            // Do not merge 'Size/Seconds' column (D column) here
                            $sheet->mergeCells("E{$mergeStartRow}:E" . ($rowIndex - 1));
                            $sheet->mergeCells("F{$mergeStartRow}:F" . ($rowIndex - 1));
                            $sheet->mergeCells("J{$mergeStartRow}:J" . ($rowIndex - 1));
                        }

                        // Update the starting row for the next merge
                        $mergeStartRow = $rowIndex;
                    }

                    $lastMiprNo = $row['mipr_no'];
                    $rowIndex++;
                }

                // Final merge for the last MIPR No (exclude 'Size/Seconds')
                $sheet->mergeCells("A{$mergeStartRow}:A" . ($rowIndex - 1));
                $sheet->mergeCells("B{$mergeStartRow}:B" . ($rowIndex - 1));
                $sheet->mergeCells("C{$mergeStartRow}:C" . ($rowIndex - 1));
                // Do not merge 'Size/Seconds' column (D column)
                $sheet->mergeCells("E{$mergeStartRow}:E" . ($rowIndex - 1));
                $sheet->mergeCells("F{$mergeStartRow}:F" . ($rowIndex - 1));
                $sheet->mergeCells("J{$mergeStartRow}:J" . ($rowIndex - 1));
            },
        ];
    }
}
