<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Menu;
use App\Models\Satuan;
use App\Models\BahanBaku;
use App\Models\NotaKirim;
use App\Models\StokDapur;
use App\Models\Transaksi;
use App\Models\ShiftKasir;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use App\Models\BahanBakuNotaBeli;
use Illuminate\Support\Facades\DB;
use App\Models\PenggunaanBahanBaku;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        
        // Get low stock alerts for dapur
        $stokDapurMenipis = collect();
        $stokDapurs = StokDapur::with(['bahanBakus.grupBahanBaku', 'satuans'])->get();
        
        foreach ($stokDapurs as $stokDapur) {
            // Get minimal stock setting for this bahan baku group in dapur
            $stokMinimal = \App\Models\StokMinimal::where('grup_bahan_baku_id', $stokDapur->bahanBakus->grup_bahan_baku_id)
                ->where('lokasi', 'dapur')
                ->first();
            
            if ($stokMinimal) {
                // Convert current stock to minimal stock unit for comparison
                $currentStockInMinimalUnit = $stokDapur->jumlah * $stokDapur->satuans->getKonversiKeTerkecil();
                $minimalStockInMinimalUnit = $stokMinimal->jumlah_minimal * $stokMinimal->satuans->getKonversiKeTerkecil();
                
                if ($currentStockInMinimalUnit <= $minimalStockInMinimalUnit) {
                    $stokDapurMenipis->push([
                        'bahanBakus' => $stokDapur->bahanBakus,
                        'stokDapur' => $stokDapur,
                        'jumlah' => $currentStockInMinimalUnit,
                        'satuans' => $stokMinimal->satuans,
                        'pengajuan_restok' => $stokDapur->bahanBakus->grupBahanBaku->pengajuan ? 1 : 0
                    ]);
                }
            }
        }
        
        // Get low stock alerts for gudang
        $stokGudangMenipis = collect();
        
        // Group by grup_bahan_baku_id to get total stock per group
        $grupBahanBakuIds = StokGudang::with(['bahanBakus.grupBahanBaku'])
            ->get()
            ->pluck('bahanBakus.grup_bahan_baku_id')
            ->unique()
            ->filter();
        
        foreach ($grupBahanBakuIds as $grupId) {
            // Get minimal stock setting for this bahan baku group in gudang
            $stokMinimal = \App\Models\StokMinimal::where('grup_bahan_baku_id', $grupId)
                ->where('lokasi', 'gudang')
                ->first();
            
            if ($stokMinimal) {
                // Get all stok gudang for this grup
                $stokGudangsInGrup = StokGudang::with(['bahanBakus.grupBahanBaku', 'satuans'])
                    ->whereHas('bahanBakus', function($query) use ($grupId) {
                        $query->where('grup_bahan_baku_id', $grupId);
                    })
                    ->get();
                
                // Calculate total stock in minimal unit for comparison
                $totalStockInMinimalUnit = 0;
                foreach ($stokGudangsInGrup as $stokGudang) {
                    $totalStockInMinimalUnit += $stokGudang->jumlah * $stokGudang->satuans->getKonversiKeTerkecil();
                }
                
                $minimalStockInMinimalUnit = $stokMinimal->jumlah_minimal * $stokMinimal->satuans->getKonversiKeTerkecil();
                
                if ($totalStockInMinimalUnit <= $minimalStockInMinimalUnit) {
                    // Get the first bahan baku from this grup for display
                    $firstBahanBaku = $stokGudangsInGrup->first()->bahanBakus;
                    
                    $stokGudangMenipis->push([
                        'bahanBakus' => $firstBahanBaku,
                        'jumlah' => $totalStockInMinimalUnit,
                        'satuans' => $stokMinimal->satuans
                    ]);
                }
            }
        }
        
        $lastShift = ShiftKasir::where('user_id', Auth::user()->id)
                        ->whereNull('jam_keluar')
                        ->latest()
                        ->first();

        $today = Carbon::today();
        // dd($today);

        // Total transaksi masuk (pembayaran customer)
        $transaksiMasuk = Transaksi::whereDate('created_at', $today)
            ->where('tipe', 'transaksi')
            ->select('metode_pembayaran', DB::raw('SUM(total_pembayaran) as total'))
            ->groupBy('metode_pembayaran')
            ->get();

        // Total setoran keluar (setor ke pemilik)
        $setoranKeluar = Transaksi::whereDate('created_at', $today)
            ->where('tipe', 'setor')
            ->select('metode_pembayaran', DB::raw('SUM(total_pembayaran) as total'))
            ->groupBy('metode_pembayaran')
            ->get();

        // dd($setoranKeluar);

        if ($transaksiMasuk->isEmpty()) {
            $saldoQRIS = 0;

            $saldoCash = 0;
        } else if ($setoranKeluar->isEmpty()) {
            $saldoQRIS = $transaksiMasuk->firstWhere('metode_pembayaran', 'QRIS')->total ?? 0;

            $saldoCash = $transaksiMasuk->firstWhere('metode_pembayaran', 'Cash')->total ?? 0;
        } else {
            $saldoQRIS = ($transaksiMasuk->firstWhere('metode_pembayaran', 'QRIS')->total ?? 0)
               - ($setoranKeluar->firstWhere('metode_pembayaran', 'QRIS')->total ?? 0);

            $saldoCash = ($transaksiMasuk->firstWhere('metode_pembayaran', 'Cash')->total ?? 0)
               - ($setoranKeluar->firstWhere('metode_pembayaran', 'Cash')->total ?? 0);
        }

        $qrisSetor = Transaksi::select('metode_pembayaran', DB::raw('SUM(total_pembayaran) as total'))
            ->whereDate('created_at', $today)
            ->where('tipe', 'setor')
            ->where('metode_pembayaran', 'QRIS')
            ->groupBy('metode_pembayaran')
            ->first();
        $cashSetor = Transaksi::select('metode_pembayaran', DB::raw('SUM(total_pembayaran) as total'))
            ->whereDate('created_at', $today)
            ->where('tipe', 'setor')
            ->where('metode_pembayaran', 'Cash')
            ->groupBy('metode_pembayaran')
            ->first();

        $tanggal = $today;

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
        })->filter(function ($item) { // Filter setelah map
            return $item['selisih'] < 0; // Hanya ambil yang selisihnya minus
        })->values();

        // dd($data);
        $shiftKasirs = ShiftKasir::whereDate('updated_at', Carbon::today())->get();

        $title = 'Dashboard';

        return view('pages.dashboard', compact('data', 'title', 'menus', 'qrisSetor', 'cashSetor', 'lastShift', 'transaksiMasuk', 'saldoQRIS', 'saldoCash', 'shiftKasirs', 'stokDapurMenipis', 'stokGudangMenipis'));
    }
}
