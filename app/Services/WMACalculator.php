<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\PenggunaanBahanBaku;

class WMACalculator
{
    public function calculateGrouped(Collection $data, string $tipeHari, Carbon $histStart, Carbon $histEnd): float
    {
        $weights = in_array($tipeHari, ['weekend', 'libur_nasional'])
            ? ['Sabtu' => 1, 'Minggu' => 2]
            : ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];

        // 1. Kelompokkan pemakaian berdasarkan tanggal (Y-m-d)
        $usageByDate = [];

        foreach ($data as $item) {
            $tanggal = \Carbon\Carbon::parse($item->created_at)->toDateString();
            $satuanItem = $item->satuans;
            $nilaiKonversi = $satuanItem->getKonversiKeTerkecil();
            $jumlah = $item->jumlah_pakai * $nilaiKonversi;

            $usageByDate[$tanggal] = ($usageByDate[$tanggal] ?? 0) + $jumlah;
        }

        // 2. Tambahkan tanggal-tanggal yang "hilang" dengan nilai 0
        $expectedDays = in_array($tipeHari, ['weekend', 'libur_nasional'])
            ? ['Sabtu', 'Minggu']
            : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $period = \Carbon\CarbonPeriod::create($histStart, $histEnd);

        foreach ($period as $date) {
            $dayName = $date->locale('id')->isoFormat('dddd');
            $tanggalStr = $date->toDateString();

            // Jika hari sesuai tipeHari dan tanggal belum ada di usageByDate, set default 0
            if (in_array($dayName, $expectedDays) && !isset($usageByDate[$tanggalStr])) {
                $usageByDate[$tanggalStr] = 0;
            }
        }

        // 3. Hitung WMA berdasarkan usageByDate
        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($usageByDate as $tanggal => $jumlah) {
            $dayName = \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd');
            $weight = $weights[$dayName] ?? 0;

            if ($weight > 0) {
                $weightedSum += $jumlah * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

}

