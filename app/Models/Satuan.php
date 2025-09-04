<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';

    protected $guarded = ['id'];

    public function satuanKecil()
    {
        return $this->belongsTo(Satuan::class, 'reference_satuan_id');
    }

    public function satuanBesar()
    {
        return $this->hasMany(Satuan::class, 'reference_satuan_id');
    }

    public function getNamaSatuanTerkecil()
    {
        $satuanTerkecil = $this->toSmallestUnit();
        return $satuanTerkecil ? $satuanTerkecil->nama : $this->nama;
    }

    public static function toSmallestUnitById($satuanId)
    {
        $satuan = self::find($satuanId);

        if (!$satuan) return null;

        while ($satuan->reference_satuan_id) {
            $satuan = self::find($satuan->reference_satuan_id);
        }

        return $satuan;
    }

    public function getKonversiKeTerkecil()
    {
        $nilai = 1;
        $satuan = $this;

        while ($satuan->reference_satuan_id) {
            $nilai *= $satuan->nilai;
            $satuan = $satuan->satuanKecil;
        }

        return $nilai;
    }

    protected static function findConversionPath(int $fromId, int $toId, array $visited = []): ?array
    {
        if ($fromId === $toId) {
            return [$fromId];
        }

        $visited[$fromId] = true;

        $neighbors = Satuan::query()
            ->where(function ($q) use ($fromId) {
                $q->where('id', $fromId)->orWhere('reference_satuan_id', $fromId);
            })
            ->orWhere('id', function ($q) use ($fromId) {
                $q->select('reference_satuan_id')->from('satuans')->where('id', $fromId);
            })
            ->get();

        foreach ($neighbors as $neighbor) {
            $neighborId = $neighbor->id;

            if (isset($visited[$neighborId])) {
                continue;
            }

            $path = self::findConversionPath($neighborId, $toId, $visited);

            if ($path) {
                return array_merge([$fromId], $path);
            }
        }

        return null;
    }

    public static function convertAmount(float|int $amount, int $fromId, int $toId): float|int|null
    {
        // Jika sama, tidak perlu konversi
        if ($fromId === $toId) {
            return $amount;
        }

        // Ambil jalur konversi dari from → to
        $path = self::findConversionPath($fromId, $toId);
        // dd($path);

        if (!$path || count($path) < 2) {
            return null; // Tidak ada jalur konversi
        }

        $convertedAmount = $amount;

        // Hitung konversi berdasarkan arah dari path
        for ($i = 0; $i < count($path) - 1; $i++) {
            $current = $path[$i];
            $next = $path[$i + 1];

            $currentSatuan = self::find($current);
            $nextSatuan = self::find($next);

            if ($nextSatuan && $nextSatuan->reference_satuan_id == $current) {
                // Dari besar → kecil (misal Liter → ml), maka dikali
                $convertedAmount /= $nextSatuan->nilai;
            } elseif ($currentSatuan && $currentSatuan->reference_satuan_id == $next) {
                // Dari kecil → besar (misal ml → Liter), maka dibagi
                $convertedAmount *= $currentSatuan->nilai;
            } else {
                return null; // Arah konversi tidak valid
            }
        }

        return $convertedAmount;
    }

    // Helper konversi
    public static function convertTo($sourceId, $targetId): float|int|null
    {
        if ($sourceId == $targetId) return 1;

        $satuans = self::all()->keyBy('id');

        $factors = [];
        $currentId = $sourceId;

        while ($currentId && $satuans->has($currentId)) {
            $satuan = $satuans->get($currentId);
            $factors[] = $satuan->nilai;
            $currentId = $satuan->reference_satuan_id;
        }

        $sourceToBase = array_product($factors);

        $factors = [];
        $currentId = $targetId;

        while ($currentId && $satuans->has($currentId)) {
            $satuan = $satuans->get($currentId);
            $factors[] = $satuan->nilai;
            $currentId = $satuan->reference_satuan_id;
        }

        $targetToBase = array_product($factors);

        if ($targetToBase == 0) return null;
        return $sourceToBase / $targetToBase;
    }

    public function toSmallestUnit()
    {
        $satuan = $this;

        while ($satuan->reference_satuan_id) {
            $satuan = $satuan->satuanKecil;
        }

        return $satuan;
    }

    public function convertToSatuan($jumlah, Satuan $target): float|int|null
    {
        return self::convertAmount($jumlah, $this->id, $target->id);
    }

    public function stokDapurs()
    {
        return $this->hasMany(StokDapur::class);
    }

    public function stokGudangs()
    {
        return $this->hasMany(StokGudang::class);
    }

    public function penggunaanBahanBakus()
    {
        return $this->hasMany(PenggunaanBahanBaku::class);
    }

    public function notaKirims()
    {
        return $this->hasMany(NotaKirim::class);
    }
}
