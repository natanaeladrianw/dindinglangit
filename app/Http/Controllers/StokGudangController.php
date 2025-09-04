<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Satuan;
use App\Models\BahanBaku;
use App\Models\NotaKirim;
use App\Models\StokDapur;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use App\Exports\StokOpnameExport;
use App\Models\BahanBakuNotaBeli;
use App\Models\PenggunaanBahanBaku;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreStokGudangRequest;
use App\Http\Requests\UpdateStokGudangRequest;

class StokGudangController extends Controller
{
    public function stokOpname(Request $request)
    {
        $tanggal = $request->input('tanggal') ?? now()->toDateString();

        $bahanBakus = BahanBaku::with([
            'penggunaanBahanBakus' => function ($q) use ($tanggal) {
                $q->whereDate('created_at', $tanggal)->with('satuans');
            },
            'stokGudangs.satuans',
            'stokGudangs',
            'stokDapurs.satuans',
            'stokDapurs',
        ])->get();

        $data = $bahanBakus->map(function ($bahan, $index) use ($tanggal) {
            if (
                $bahan->penggunaanBahanBakus->isEmpty() &&
                $bahan->stokDapurs->isEmpty() &&
                $bahan->stokGudangs->isEmpty() &&
                NotaKirim::where('bahan_baku_id', $bahan->id)->whereDate('created_at', $tanggal)->count() == 0
            ) {
                return [
                    'no' => $index + 1,
                    'kode' => $bahan->kode,
                    'nama' => $bahan->nama,
                    'satuan' => '-',
                    'stok_awal' => 0,
                    'stok_masuk' => 0,
                    'stok_pakai' => 0,
                    'sisa_fisik' => 0,
                    'sisa_seharusnya' => 0,
                    'selisih' => 0,
                    'keterangan' => '-',
                ];
            }

            // Satuan Terkecil
            $satuanTerkecil = $bahan->stokDapurs->first()?->satuans->toSmallestUnit();
            $satuanKecil = $bahan->stokDapurs->first()?->satuans->toSmallestUnit()->nama;

            // Stok Pakai
            $stokPakai = $bahan->penggunaanBahanBakus->sum(function ($item) {
                return ($item->jumlah_pakai) * ($item->satuans?->getKonversiKeTerkecil() ?? 1);
            });

            // Stok Masuk
            $notaKirimHariIni = NotaKirim::where('bahan_baku_id', $bahan->id)
                ->whereDate('created_at', $tanggal)
                ->with('satuans')
                ->get();

            $satuanTerkecilnotaKirimHariIni = $notaKirimHariIni->first()?->satuans->toSmallestUnit();

            $stokMasuk = $notaKirimHariIni->sum(function ($nota) use ($satuanTerkecilnotaKirimHariIni) {
                try {
                    return Satuan::convertAmount($nota->jumlah, $nota->satuan_id, $satuanTerkecilnotaKirimHariIni->id) ?? 0;
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }
            });

            // Stok Awal
            // Cek nota kirim pertama
            $notaKirimPertama = NotaKirim::where('bahan_baku_id', $bahan->id)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($notaKirimPertama && $notaKirimPertama->created_at > $tanggal) {
                $stokAwal = 0;
            } else {
                // Ambil semua penggunaan sebelum tanggal
                $lastPenggunaan = PenggunaanBahanBaku::where('bahan_baku_id', $bahan->id)
                    ->where('created_at', '<', $tanggal)
                    ->orderBy('id', 'DESC')
                    ->first();

                if (!$lastPenggunaan) {
                    // Jika tidak ada penggunaan sebelum tanggal, maka stok awal adalah stok dapur sebelum ada stok masuk
                    $satuanDapur = $bahan->stokDapurs->first()?->satuans;
                    $jmlStokDapur = ($bahan->stokDapurs->first()?->jumlah ?? 0);

                    if ($satuanDapur && $satuanTerkecil) {
                        try {
                            $stokDapurTerkecil = Satuan::convertAmount($jmlStokDapur, $satuanDapur->id, $satuanTerkecil->id);
                        } catch (\Exception $e) {
                            return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                        }
                    } else {
                        $stokDapurTerkecil = 0; // Atau nilai default lain sesuai kebutuhan
                    }

                    // Ambil semua penggunaan SETELAH $tanggal (karena kita ingin "kembalikan" stok ke masa lalu)
                    $penggunaanSetelahTanggal = PenggunaanBahanBaku::where('bahan_baku_id', $bahan->id)
                        ->where('created_at', '>=', $tanggal)
                        ->get();

                    $jumlahPakaiSetelahTanggal = $penggunaanSetelahTanggal->sum(function ($item) use ($satuanTerkecil) {
                        try {
                            return Satuan::convertAmount($item->jumlah_pakai, $item->satuan_id, $satuanTerkecil->id) ?? 0;
                        } catch (\Exception $e) {
                            return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                        }
                    });

                    // Ambil semua nota kirim SETELAH $tanggal
                    $notaKirimSetelahTanggal = NotaKirim::where('bahan_baku_id', $bahan->id)
                        ->where('created_at', '>=', $tanggal)
                        ->get();

                    $jumlahNotaKirimSetelahTanggal = $notaKirimSetelahTanggal->sum(function ($item) use ($satuanTerkecil) {
                        try {
                            return Satuan::convertAmount($item->jumlah, $item->satuan_id, $satuanTerkecil->id) ?? 0;
                        } catch (\Exception $e) {
                            return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                        }
                    });

                    $stokAwal = $stokDapurTerkecil + $jumlahPakaiSetelahTanggal - $jumlahNotaKirimSetelahTanggal;
                    $stokAwal = $stokAwal - ($stokMasuk ?? 0);
                } else {
                    // Jika ada penggunaan sebelum tanggal, maka stok awal adalah sisa fisik penggunaan terakhir
                    $stokAwal = ($lastPenggunaan?->sisa_fisik ?? 0) * ($lastPenggunaan?->satuans?->getKonversiKeTerkecil() ?? 1);
                }
            }

            // Sisa Fisik
            if ($bahan->penggunaanBahanBakus->isEmpty()) {
                if ($stokMasuk > 0) {
                    $sisaFisik = $stokAwal + $stokMasuk;
                } else {
                    $sisaFisik = $stokAwal;
                }
                // $sisaFisik = $stokAwal;
            } else {
                $sisaFisik = $bahan->penggunaanBahanBakus->last()?->sisa_fisik * ($bahan->stokDapurs->first()?->satuans?->getKonversiKeTerkecil() ?? 1);
            }

            // Sisa Seharusnya
            $sisaSeharusnya = $stokAwal + $stokMasuk - $stokPakai;

            // Selisih
            $selisih = $sisaFisik - $sisaSeharusnya;

            return [
                'no' => $index + 1,
                'kode' => $bahan->kode,
                'nama' => $bahan->nama,
                'satuan' => $satuanKecil,
                'stok_awal' => $stokAwal,
                'stok_masuk' => $stokMasuk,
                'stok_pakai' => $stokPakai,
                'sisa_fisik' => $sisaFisik,
                'sisa_seharusnya' => $sisaSeharusnya,
                'selisih' => $bahan->penggunaanBahanBakus->isEmpty() ? 0 : $selisih,
                'keterangan' => $bahan->penggunaanBahanBakus->first()?->keterangan,
            ];
        });

        $title = 'Stok Opname';

        return view('pages.stokopname.stokopname', compact('data', 'title'));
    }

    public function exportStokOpname(Request $request)
    {
        $tanggal = $request->input('tanggal') ?? now()->toDateString();

        $bahanBakus = BahanBaku::with([
            'penggunaanBahanBakus' => function ($q) use ($tanggal) {
                $q->whereDate('created_at', $tanggal)->with('satuans');
            },
            'stokGudangs.satuans',
            'stokGudangs',
            'stokDapurs.satuans',
            'stokDapurs',
        ])->get();

        $data = $bahanBakus->map(function ($bahan, $index) use ($tanggal) {
            if (
                $bahan->penggunaanBahanBakus->isEmpty() &&
                $bahan->stokDapurs->isEmpty() &&
                $bahan->stokGudangs->isEmpty() &&
                NotaKirim::where('bahan_baku_id', $bahan->id)->whereDate('created_at', $tanggal)->count() == 0
            ) {
                return [
                    'no' => $index + 1,
                    'kode' => $bahan->kode,
                    'nama' => $bahan->nama,
                    'satuan' => '-',
                    'stok_awal' => 0,
                    'stok_masuk' => 0,
                    'stok_pakai' => 0,
                    'sisa_fisik' => 0,
                    'sisa_seharusnya' => 0,
                    'selisih' => 0,
                    'keterangan' => '-',
                ];
            }

            // Satuan Terkecil
            $satuanTerkecil = $bahan->stokDapurs->first()?->satuans->toSmallestUnit();
            $satuanKecil = $bahan->stokDapurs->first()?->satuans->toSmallestUnit()->nama;

            // Stok Pakai
            $stokPakai = $bahan->penggunaanBahanBakus->sum(function ($item) {
                return ($item->jumlah_pakai) * ($item->satuans?->getKonversiKeTerkecil() ?? 1);
            });

            // Stok Masuk
            $notaKirimHariIni = NotaKirim::where('bahan_baku_id', $bahan->id)
                ->whereDate('created_at', $tanggal)
                ->with('satuans')
                ->get();

            $satuanTerkecilnotaKirimHariIni = $notaKirimHariIni->first()?->satuans->toSmallestUnit();

            $stokMasuk = $notaKirimHariIni->sum(function ($nota) use ($satuanTerkecilnotaKirimHariIni) {
                try {
                    return Satuan::convertAmount($nota->jumlah, $nota->satuan_id, $satuanTerkecilnotaKirimHariIni->id) ?? 0;
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }
            });

            // Stok Awal
            // Cek nota kirim pertama
            $notaKirimPertama = NotaKirim::where('bahan_baku_id', $bahan->id)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($notaKirimPertama && $notaKirimPertama->created_at > $tanggal) {
                $stokAwal = 0;
            } else {
                // Ambil semua penggunaan sebelum tanggal
                $lastPenggunaan = PenggunaanBahanBaku::where('bahan_baku_id', $bahan->id)
                    ->where('created_at', '<', $tanggal)
                    ->orderBy('id', 'DESC')
                    ->first();

                if (!$lastPenggunaan) {
                    // Jika tidak ada penggunaan sebelum tanggal, maka stok awal adalah stok dapur sebelum ada stok masuk
                    $satuanDapur = $bahan->stokDapurs->first()?->satuans;
                    $jmlStokDapur = ($bahan->stokDapurs->first()?->jumlah ?? 0);

                    if ($satuanDapur && $satuanTerkecil) {
                        try {
                            $stokDapurTerkecil = Satuan::convertAmount($jmlStokDapur, $satuanDapur->id, $satuanTerkecil->id);
                        } catch (\Exception $e) {
                            return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                        }
                    } else {
                        $stokDapurTerkecil = 0; // Atau nilai default lain sesuai kebutuhan
                    }

                    // Ambil semua penggunaan SETELAH $tanggal (karena kita ingin "kembalikan" stok ke masa lalu)
                    $penggunaanSetelahTanggal = PenggunaanBahanBaku::where('bahan_baku_id', $bahan->id)
                        ->where('created_at', '>=', $tanggal)
                        ->get();

                    $jumlahPakaiSetelahTanggal = $penggunaanSetelahTanggal->sum(function ($item) use ($satuanTerkecil) {
                        try {
                            return Satuan::convertAmount($item->jumlah_pakai, $item->satuan_id, $satuanTerkecil->id) ?? 0;
                        } catch (\Exception $e) {
                            return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                        }
                    });

                    // Ambil semua nota kirim SETELAH $tanggal
                    $notaKirimSetelahTanggal = NotaKirim::where('bahan_baku_id', $bahan->id)
                        ->where('created_at', '>=', $tanggal)
                        ->get();

                    $jumlahNotaKirimSetelahTanggal = $notaKirimSetelahTanggal->sum(function ($item) use ($satuanTerkecil) {
                        try {
                            return Satuan::convertAmount($item->jumlah, $item->satuan_id, $satuanTerkecil->id) ?? 0;
                        } catch (\Exception $e) {
                            return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                        }
                    });

                    $stokAwal = $stokDapurTerkecil + $jumlahPakaiSetelahTanggal - $jumlahNotaKirimSetelahTanggal;
                    $stokAwal = $stokAwal - ($stokMasuk ?? 0);
                } else {
                    // Jika ada penggunaan sebelum tanggal, maka stok awal adalah sisa fisik penggunaan terakhir
                    $stokAwal = ($lastPenggunaan?->sisa_fisik ?? 0) * ($lastPenggunaan?->satuans?->getKonversiKeTerkecil() ?? 1);
                }
            }

            // Sisa Fisik
            if ($bahan->penggunaanBahanBakus->isEmpty()) {
                $sisaFisik = $stokAwal;

            } else {
                $sisaFisik = $bahan->penggunaanBahanBakus->last()?->sisa_fisik * ($bahan->stokDapurs->first()?->satuans?->getKonversiKeTerkecil() ?? 1);
            }

            // Sisa Seharusnya
            $sisaSeharusnya = $stokAwal + $stokMasuk - $stokPakai;

            // Selisih
            $selisih = $sisaFisik - $sisaSeharusnya;

            return [
                'no' => $index + 1,
                'kode' => $bahan->kode,
                'nama' => $bahan->nama,
                'satuan' => $satuanKecil,
                'stok_awal' => $stokAwal,
                'stok_masuk' => $stokMasuk,
                'stok_pakai' => $stokPakai,
                'sisa_fisik' => $sisaFisik,
                'sisa_seharusnya' => $sisaSeharusnya,
                'selisih' => $bahan->penggunaanBahanBakus->isEmpty() ? 0 : $selisih,
                'keterangan' => $bahan->penggunaanBahanBakus->first()?->keterangan,
            ];
        });

        $filename = 'Stok_Opname_' . $tanggal . '.xlsx';
        return Excel::download(new StokOpnameExport($data), $filename);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bahanBakus = BahanBaku::all();
        // Ambil semua stok gudang dengan relasi bahan baku
        $stokGudangs = StokGudang::with('bahanBakus')->get();

        // Hitung stok yang mendekati kadaluarsa (<= 35 hari lagi)
        $stokGudangs->each(function ($stok) {
            $stok->mendekati_kadaluarsa = $stok->where('bahan_baku_id', $stok->bahan_baku_id)
                ->where('tanggal_exp', '<=', now()->addDays(35))
                ->where('tanggal_exp', '>=', now())
                ->sum('jumlah');

            $stok->sudah_kadaluarsa = $stok->where('bahan_baku_id', $stok->bahan_baku_id)
                ->where('tanggal_exp', '<', now())
                ->sum('jumlah');

            $stok->tanggal_masuk = BahanBakuNotaBeli::where('bahan_baku_id', $stok->bahan_baku_id)
                ->latest()
                ->first('created_at');
        });
        $title = 'Stok Gudang';

        return view('pages.stokgudang.stokgudang', compact('stokGudangs', 'bahanBakus', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bahanBakus = BahanBaku::all();
        $satuans = Satuan::all();
        $title = 'Stok Gudang';

        return view('pages.stokgudang.create-stokgudang', compact('satuans', 'bahanBakus', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStokGudangRequest $request)
    {
        try {
            $validatedData = $request->validated();

            StokGudang::create($validatedData);

            return redirect()->route('stokgudang.index')->with('alert', 'Stok Gudang berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan Stok Gudang: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StokGudang $stokgudang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokGudang $stokgudang)
    {
        $bahanBakus = BahanBaku::all();
        $satuans = Satuan::all();
        $title = 'Stok Gudang';

        return view('pages.stokgudang.edit-stokgudang', compact('satuans', 'stokgudang', 'bahanBakus', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStokGudangRequest $request, StokGudang $stokgudang)
    {
        try {
            $validatedData = $request->validated();

            $stokgudang->update($validatedData);

            return redirect()->route('stokgudang.index')->with('alert', 'Stok Gudang berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui Stok Gudang: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokGudang $stokgudang)
    {
        try {
            $stokgudang->delete();

            return redirect()->route('stokgudang.index')->with('alert', 'Stok Gudang berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus Stok Gudang: \n' . $e->getMessage());
        }
    }

    private function konversiKeSatuanKecil($jumlah, $satuan)
    {
        $total = $jumlah;

        while ($satuan && $satuan->reference_satuan_id) {
            $total *= $satuan->nilai_konversi;
            $satuan = $satuan->satuanKecil; // pastikan relasi ini ada
        }

        return $total;
    }
}
