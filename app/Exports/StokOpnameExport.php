<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // Tambahkan ini jika ingin memformat data
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Opsional: untuk lebar kolom otomatis

class StokOpnameExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Langsung kembalikan koleksi data yang sudah Anda olah
        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Sesuaikan dengan kunci array yang Anda kembalikan dari fungsi map di controller
        return [
            'No',
            'Kode',
            'Nama Bahan Baku',
            'Satuan',
            'Stok Awal',
            'Stok Masuk',
            'Stok Pakai',
            'Sisa Fisik',
            'Sisa Seharusnya',
            'Selisih',
            'Keterangan',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        // Map data agar sesuai dengan urutan header dan format yang diinginkan
        return [
            $row['no'],
            $row['kode'],
            $row['nama'],
            $row['satuan'],
            $row['stok_awal'],
            $row['stok_masuk'],
            $row['stok_pakai'],
            $row['sisa_fisik'],
            $row['sisa_seharusnya'],
            $row['selisih'],
        ];
    }
}
