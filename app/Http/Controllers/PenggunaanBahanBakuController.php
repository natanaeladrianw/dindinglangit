<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\BahanBaku;
use App\Models\StokDapur;
use Illuminate\Http\Request;
use App\Services\WMACalculator;
use App\Exports\WmaPrediksiExport;
use Illuminate\Support\Facades\DB;
use App\Models\PenggunaanBahanBaku;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StorePenggunaanBahanBakuRequest;
use App\Http\Requests\UpdatePenggunaanBahanBakuRequest;

class PenggunaanBahanBakuController extends Controller
{
    protected $wmaCalculator;

    public function __construct(WMACalculator $wmaCalculator)
    {
        $this->wmaCalculator = $wmaCalculator;
    }

    public function prediksi(Request $request, WMACalculator $wma)
    {
        $title = 'Prediksi Stok';

        $startDate = $request->input('start_date_historis');
        $endDate = $request->input('end_date_historis');
        $tipeHari = $request->input('tipe_hari', '');

        $hasilPrediksi = collect();
        $bahanBakuMissing = collect();

        if ($startDate && $endDate) {
            // 🔁 Mapping tanggal masa depan ke tanggal historis
            [$histStart, $histEnd] = $this->mapFutureDatesToHistorical($startDate, $endDate, $tipeHari);
            $histStart = \Carbon\Carbon::parse($histStart)->startOfDay();
            $histEnd = \Carbon\Carbon::parse($histEnd)->endOfDay();

            // Ambil data penggunaan historis sesuai range yang telah diubah
            $dataPenggunaan = PenggunaanBahanBaku::with('satuans', 'bahanBakus')
                ->whereBetween('created_at', [$histStart, $histEnd])
                ->get();

            // Kelompokkan berdasarkan nama bahan baku
            $groupedByNama = $dataPenggunaan->groupBy(fn($item) => $item->bahanBakus->nama);

            $hasilPrediksi = $groupedByNama->map(function ($group, $nama) use ($wma, $tipeHari, $histStart, $histEnd) {
                // dd($group);
                $totalPrediksi = $wma->calculateGrouped($group, $tipeHari, $histStart, $histEnd);
                $penggunaanTerakhir = $group->sortByDesc('created_at')->first();
                $satuan = $penggunaanTerakhir?->satuans;
                $satuanKecilModel = $satuan?->toSmallestUnit();
                $satuanKecil = $satuanKecilModel->nama ?? '-';

                // Cari satuan terbesar dalam rantai berdasarkan data di halaman Satuan
                $satuanBesarNama = '-';
                if ($satuanKecilModel) {
                    $current = $satuanKecilModel;
                    // Naik ke atas selama ada satuan yang mereferensikan current sebagai satuan kecil
                    while (true) {
                        $parent = Satuan::where('reference_satuan_id', $current->id)->first();
                        if (!$parent) break;
                        $current = $parent;
                    }
                    $satuanBesarNama = $current->nama ?? $satuanKecil;
                }

                return [
                    'nama' => $nama,
                    'hasil' => number_format($totalPrediksi, 2),
                    'satuan' => $satuanKecil,
                    'satuan_besar' => $satuanBesarNama,
                    'konversi' => 1,
                ];
            })->values();

            $namaSudahDiprediksi = $hasilPrediksi->pluck('nama')->all();
            $bahanBakuMissing = BahanBaku::whereNotIn('nama', $namaSudahDiprediksi)->get();

            foreach ($bahanBakuMissing as $bahan) {
                $stok = $bahan->stokGudangs->first();
                $satuan = $stok?->satuans;

                $satuanKecilModel = $satuan?->toSmallestUnit();
                $satuanKecil = $satuanKecilModel->nama ?? '-';

                $satuanBesarNama = '-';
                if ($satuanKecilModel) {
                    $current = $satuanKecilModel;
                    while (true) {
                        $parent = Satuan::where('reference_satuan_id', $current->id)->first();
                        if (!$parent) break;
                        $current = $parent;
                    }
                    $satuanBesarNama = $current->nama ?? $satuanKecil;
                }

                $hasilPrediksi->push([
                    'nama' => $bahan->nama,
                    'hasil' => 0,
                    'satuan' => $satuanKecil,
                    'satuan_besar' => $satuanBesarNama,
                    'konversi' => 1,
                ]);
            }
        }

        return view('pages.wma.wma', compact('title', 'hasilPrediksi', 'startDate', 'endDate', 'tipeHari'));
    }

    public function exportWmaPrediksi(Request $request, WMACalculator $wma)
    {
        $title = 'Prediksi Stok';

        $startDate = $request->input('start_date_historis');
        $endDate = $request->input('end_date_historis');
        $tipeHari = $request->input('tipe_hari', '');

        $hasilPrediksi = collect();
        $bahanBakuMissing = collect();

        if ($startDate && $endDate) {
            // 🔁 Mapping tanggal masa depan ke tanggal historis
            [$histStart, $histEnd] = $this->mapFutureDatesToHistorical($startDate, $endDate, $tipeHari);
            $histStart = \Carbon\Carbon::parse($histStart)->startOfDay();
            $histEnd = \Carbon\Carbon::parse($histEnd)->endOfDay();


            // Ambil data penggunaan historis sesuai range yang telah diubah
            $dataPenggunaan = PenggunaanBahanBaku::with('satuans', 'bahanBakus')
                ->whereBetween('created_at', [$histStart, $histEnd])
                ->get();

            // Kelompokkan berdasarkan nama bahan baku
            $groupedByNama = $dataPenggunaan->groupBy(fn($item) => $item->bahanBakus->nama);

            $hasilPrediksi = $groupedByNama->map(function ($group, $nama) use ($wma, $tipeHari, $histStart, $histEnd) {
                // dd($group);
                $totalPrediksi = $wma->calculateGrouped($group, $tipeHari, $histStart, $histEnd);
                $penggunaanTerakhir = $group->sortByDesc('created_at')->first();
                $satuan = $penggunaanTerakhir?->satuans;
                $satuanKecil = $satuan?->toSmallestUnit()->nama ?? '-';

                return [
                    'nama' => $nama,
                    'hasil' => number_format($totalPrediksi, 2),
                    'satuan' => $satuanKecil,
                ];
            })->values();

            $namaSudahDiprediksi = $hasilPrediksi->pluck('nama')->all();
            $bahanBakuMissing = BahanBaku::whereNotIn('nama', $namaSudahDiprediksi)->get();

            foreach ($bahanBakuMissing as $bahan) {
                $stok = $bahan->stokGudangs->first();
                $satuan = $stok?->satuans;

                $satuanKecil = $satuan?->reference_satuan_id
                    ? $satuan->satuanKecil->nama ?? $satuan->nama
                    : $satuan->nama ?? '-';

                $hasilPrediksi->push([
                    'nama' => $bahan->nama,
                    'hasil' => 0,
                    'satuan' => $satuanKecil,
                ]);
            }
        }

        $filename = 'Wma_Prediksi_' . now()->toDateString() . '.xlsx';
        return Excel::download(new WmaPrediksiExport($hasilPrediksi), $filename);
    }

    private function mapFutureDatesToHistorical(string $start, string $end, string $tipeHari): array
    {
        $futureEnd = \Carbon\Carbon::parse($end);

        if ($tipeHari === 'libur_nasional') {
            $weekends = [];

            // Mulai dari tanggal end, mundur ke belakang sampai dapat 3 weekend (Sabtu/Minggu)
            $current = $futureEnd->copy();
            while (count($weekends) < 3) {
                if ($current->isSaturday() || $current->isSunday()) {
                    $weekends[] = $current->copy();
                }
                $current->subDay();
            }

            // Urutkan dari paling lama ke paling baru (opsional)
            $weekends = collect($weekends)->sort()->values();

            return [
                $weekends->first()->startOfDay()->toDateTimeString(),
                $weekends->last()->endOfDay()->toDateTimeString()
            ];
        }

        // Default: untuk weekday/weekend
        $historicalStart = \Carbon\Carbon::parse($start)->subWeek();
        $historicalEnd = \Carbon\Carbon::parse($end)->subWeek();

        return [
            $historicalStart->startOfDay()->toDateTimeString(),
            $historicalEnd->endOfDay()->toDateTimeString()
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start with base query
        $query = PenggunaanBahanBaku::with(['bahanBakus', 'satuans']);

        // Date filtering
        if ($request->input('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->input('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        if ($request->input('sort')) {
            $query->orderBy('created_at', $request->input('sort'));
        }

        // Search functionality
        if ($request->input('search')) {
            $query->whereHas('bahanBakus', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->input('search').'%');
            });
        }

        // Get paginated results (15 items per page)
        $penggunaanBahanBakus = $query->paginate(15)
            ->appends($request->query());


        // Get related data for filters
        $bahanBakus = BahanBaku::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();

        $title = 'Penggunaan Bahan';

        return view('pages.penggunaanbahanbaku.penggunaanbahanbaku',
            compact('title', 'penggunaanBahanBakus', 'bahanBakus', 'satuans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $penggunaanBahanBakus = PenggunaanBahanBaku::all();
        $bahanBakus = BahanBaku::with('stokDapurs')
                        ->whereRelation('stokDapurs', 'jumlah', '>', 0)
                        ->get();
        $satuans = Satuan::all();
        $title = 'Penggunaan Bahan';
        return view('pages.penggunaanbahanbaku.create-penggunaanbahanbaku', compact('title', 'penggunaanBahanBakus', 'bahanBakus', 'satuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePenggunaanBahanBakuRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->sisa_fisik < 0) {
            return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Penggunaan Bahan Baku melebihi Stok Dapur!');
        }

        DB::beginTransaction();
        try {
            $stokDapur = StokDapur::where('bahan_baku_id', $request->bahan_baku_id)->first();
            $satuanDapur = $stokDapur->satuans->id;
            // Buat record penggunaan
            $penggunaan = PenggunaanBahanBaku::create([
                'bahan_baku_id' => $request->bahan_baku_id,
                'satuan_id' => $request->satuan_id,
                'jumlah_pakai' => $request->jumlah_pakai,
                'sisa_fisik' => $request->sisa_fisik,
                'keterangan' => $request->keterangan,
            ]);

            // Update stok dapur
            try {
                $jumlahStokDapur = Satuan::convertAmount($request->sisa_fisik, $request->satuan_id, $satuanDapur);
            } catch (\Exception $e) {
                return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
            }
            StokDapur::where('bahan_baku_id', $request->bahan_baku_id)
                ->update([
                    'jumlah' => $jumlahStokDapur,
                ]);

            DB::commit();
            return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Penggunaan Bahan Baku berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan Penggunaan Bahan Baku: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PenggunaanBahanBaku $penggunaanbahanbaku)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PenggunaanBahanBaku $penggunaanbahanbaku)
    {
        // Dapatkan data satuan untuk konversi
        $satuanStokDapur = $penggunaanbahanbaku->bahanBakus->stokDapurs->first()->satuans;
        $satuanPenggunaan = $penggunaanbahanbaku->satuans;

        // Konversi jumlah pakai ke satuan stok dapur jika berbeda
        $jumlahPakaiDalamSatuanStok = $penggunaanbahanbaku->jumlah_pakai;

        if ($satuanPenggunaan->id != $satuanStokDapur->id) {
            // Jika ada relasi satuan (misal: L -> ml)
            if ($satuanPenggunaan->reference_satuan_id == $satuanStokDapur->id) {
                $jumlahPakaiDalamSatuanStok = $penggunaanbahanbaku->jumlah_pakai * $satuanPenggunaan->nilai;
            }
            // Jika satuan stok yang punya relasi (misal: ml -> L)
            elseif ($satuanStokDapur->reference_satuan_id == $satuanPenggunaan->id) {
                $jumlahPakaiDalamSatuanStok = $penggunaanbahanbaku->jumlah_pakai / $satuanStokDapur->nilai;
            }
            // Jika tidak ada relasi langsung (konversi tidak mungkin)
            else {
                return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Satuan tidak kompatibel antara stok dan penggunaan!');
            }
        }

        // Tambahkan stok yang sedang digunakan ke tampilan sementara
        $bahanBakus = BahanBaku::with(['stokDapurs.satuans'])
            ->get()
            ->map(function($bahan) use ($penggunaanbahanbaku, $jumlahPakaiDalamSatuanStok) {
                if ($bahan->id == $penggunaanbahanbaku->bahan_baku_id) {
                    $stokDapur = $bahan->stokDapurs->first();

                    // Pastikan stokDapur ada dan memiliki satuan
                    if ($stokDapur && $stokDapur->satuan_id) {
                        $stokDapur->jumlah += $jumlahPakaiDalamSatuanStok;
                    }
                }
                return $bahan;
            });

        $satuans = Satuan::all();
        $title = 'Penggunaan Bahan';
        return view('pages.penggunaanbahanbaku.edit-penggunaanbahanbaku', compact('title', 'penggunaanbahanbaku', 'bahanBakus', 'satuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePenggunaanBahanBakuRequest $request, PenggunaanBahanBaku $penggunaanbahanbaku)
    {
        // dd($request);
        if ($request->sisa_fisik < 0) {
            return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Gagal diperbarui!, penggunaan bahan baku melebihi Stok Dapur!');
        }

        if ($penggunaanbahanbaku->created_at->diffInHours(now()) > 24) {
            return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Tidak bisa mengubah data kemarin');
        }

        DB::beginTransaction();
        try {
            // 1. Simpan data lama sebelum diubah
            $originalBahanBakuId = $penggunaanbahanbaku->bahan_baku_id;
            $originalJumlahPakai = $penggunaanbahanbaku->jumlah_pakai;
            $originalSisaFisik = $penggunaanbahanbaku->sisa_fisik;

            // 2. Dapatkan data satuan
            $satuanStok = StokDapur::where('bahan_baku_id', $originalBahanBakuId)
                        ->first()->satuans;
            $satuanPakai = Satuan::find($request->satuan_id);

            // 3. Kembalikan stok lama ke nilai sebelum penggunaan
            if ($satuanStok->id == $satuanPakai->id) {
                StokDapur::where('bahan_baku_id', $originalBahanBakuId)
                    ->increment('jumlah', $originalJumlahPakai);
            } else {
                $jumlahPakai = Satuan::convertAmount($originalJumlahPakai, $request->satuan_id, $satuanStok->id);

                StokDapur::where('bahan_baku_id', $originalBahanBakuId)
                    ->increment('jumlah', $jumlahPakai);
            }

            // 4. Update record penggunaan
            $penggunaanbahanbaku->update([
                'bahan_baku_id' => $request->bahan_baku_id,
                'satuan_id' => $satuanStok->id,
                'jumlah_pakai' => $request->jumlah_pakai,
                'sisa_fisik' => $request->sisa_fisik,
                'keterangan' => $request->keterangan,
            ]);

            // 5. Kurangi stok baru
            $jumlahPakaiSatuanStok = Satuan::convertAmount($request->jumlah_pakai, $satuanPakai->id, $satuanStok->id);
            StokDapur::where('bahan_baku_id', $request->bahan_baku_id)
                ->decrement('jumlah', $jumlahPakaiSatuanStok);

            DB::commit();
            return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Data berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('alert', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PenggunaanBahanBaku $penggunaanbahanbaku)
    {
        DB::beginTransaction();
        try {
            // 1. Kembalikan stok
            StokDapur::where('bahan_baku_id', $penggunaanbahanbaku->bahan_baku_id)
                ->increment('jumlah', $penggunaanbahanbaku->jumlah_pakai);

            // 2. Hapus record
            $penggunaanbahanbaku->delete();

            DB::commit();
            return redirect()->route('penggunaanbahanbaku.index')->with('alert', 'Data berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('alert', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
