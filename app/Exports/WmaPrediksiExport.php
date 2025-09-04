<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WmaPrediksiExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * Return the collection to export
     */
    public function collection()
    {
        return $this->data->map(function ($item, $index) {
            return [
                'No.' => $index + 1,
                'Bahan Baku' => $item['nama'],
                'Hasil Prediksi' => $item['hasil'],
                'Satuan' => $item['satuan'],
            ];
        });
    }

    /**
     * Set the headings for the Excel sheet
     */
    public function headings(): array
    {
        return ['No.', 'Bahan Baku', 'Hasil Prediksi', 'Satuan'];
    }

    /**
     * Set the title of the sheet
     */
    public function title(): string
    {
        return 'Prediksi WMA';
    }
}
