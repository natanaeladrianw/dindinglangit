<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaksi;
use App\Models\ShiftKasir;
use Illuminate\Http\Request;
use App\Models\BahanBakuNotaBeli;
use App\Models\NotaKirim;
use Illuminate\Support\Facades\DB;
use App\Models\PenggunaanBahanBaku;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $notaStart = $request->input('nota_start');
        $notaEnd = $request->input('nota_end');
        $beliStart = $request->input('beli_start');
        $beliEnd = $request->input('beli_end');

        $notaQuery = BahanBakuNotaBeli::with(['bahanBakus', 'notaBelis']);
        $beliQuery = BahanBakuNotaBeli::with(['bahanBakus', 'notaBelis']);

        // Filter untuk Laporan Nota Beli
        if ($notaStart && $notaEnd) {
            $notaQuery->whereHas('notaBelis', function ($q) use ($notaStart, $notaEnd) {
                $q->whereBetween('tanggal_transaksi', [$notaStart, $notaEnd]);
            });
        }

        // Filter untuk Laporan Pembelian Bahan Baku
        if ($beliStart && $beliEnd) {
            $beliQuery->whereHas('notaBelis', function ($q) use ($beliStart, $beliEnd) {
                $q->whereBetween('tanggal_transaksi', [$beliStart, $beliEnd]);
            });
        }

        $notaBelis = $notaQuery->paginate(5, ['*'], 'nota_beli_page');
        $pembelianBahanBakus = $beliQuery->paginate(4, ['*'], 'pembelian_page');
        $title = 'Laporan';

        return view('pages.laporan', compact('pembelianBahanBakus', 'notaBelis', 'title'));
    }

    public function pembelianBahanBaku(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $namaBahanBaku = $request->input('nama_bahan_baku');
        $kodeBahanBaku = $request->input('kode_bahan_baku');
        $supplierName = $request->input('supplier');
        $showAll = $request->boolean('show_all');

        // Penanganan tanggal default
        if (!$showAll) {
            if (empty($startDate) && empty($endDate)) {
                $startDate = Carbon::today()->toDateString();
                $endDate = Carbon::today()->toDateString();
            } elseif (empty($startDate)) {
                $startDate = Carbon::parse($endDate)->startOfDay()->toDateString();
            } elseif (empty($endDate)) {
                $endDate = Carbon::parse($startDate)->endOfDay()->toDateString();
            }
        } else {
            $startDate = $startDate; // keep as provided (can be null)
            $endDate = $endDate;     // keep as provided (can be null)
        }

        $query = BahanBakuNotaBeli::with(['bahanBakus', 'notaBelis.suppliers'])
            ->when(!$showAll && ($startDate || $endDate), function ($query) use ($startDate, $endDate) {
                $query->whereHas('notaBelis', function ($q) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        $q->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
                    } elseif ($startDate) {
                        $q->whereDate('tanggal_transaksi', '>=', $startDate);
                    } elseif ($endDate) {
                        $q->whereDate('tanggal_transaksi', '<=', $endDate);
                    }
                });
            })
            ->when($namaBahanBaku, function ($query, $namaBahanBaku) {
                $query->whereHas('bahanBakus', function ($q) use ($namaBahanBaku) {
                    $q->where('nama', 'like', "%$namaBahanBaku%");
                });
            })
            ->when($kodeBahanBaku, function ($query, $kodeBahanBaku) {
                $query->whereHas('bahanBakus', function ($q) use ($kodeBahanBaku) {
                    $q->where('kode', 'like', "%$kodeBahanBaku%");
                });
            })
            ->when($supplierName, function ($query, $supplierName) {
                $query->whereHas('notaBelis.suppliers', function ($q) use ($supplierName) {
                    $q->where('nama', 'like', "%$supplierName%");
                });
            })
            ->whereHas('notaBelis')
            ->join('nota_belis', 'bahan_baku_nota_belis.nota_beli_id', '=', 'nota_belis.id')
            ->orderBy('nota_belis.tanggal_transaksi')
            ->select('bahan_baku_nota_belis.*');

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $pembelianBahanBakus = $query->paginate($perPage, ['*'], 'pembelian_page')
            ->withQueryString();

        $title = 'Laporan Pembelian Bahan Baku';

        return view('pages.laporan-pembelianbahanbaku', compact('pembelianBahanBakus', 'title', 'startDate', 'endDate'));
    }

    public function penjualan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Jika tanggal tidak ditentukan, default ke hari ini
        if (empty($startDate) && empty($endDate)) {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } elseif (empty($startDate)) {
            $startDate = Carbon::parse($endDate)->startOfDay()->toDateString();
        } elseif (empty($endDate)) {
            $endDate = Carbon::parse($startDate)->endOfDay()->toDateString();
        }

        $query = Transaksi::where('tipe', 'transaksi')
        ->when($startDate, function ($query, $startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        })
        ->when($endDate, function ($query, $endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        })
        ->orderBy('created_at');

        $transaksis = $query->paginate(10, ['*'], 'penjualan_page')
            ->withQueryString();

        $title = 'Laporan Penjualan';

        return view('pages.laporan-penjualan', compact('transaksis', 'title', 'startDate', 'endDate'));
    }

    public function pengirimanBahanBaku(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $namaBahanBaku = $request->input('nama_bahan_baku');
        $kodeBahanBaku = $request->input('kode_bahan_baku');
        $userName = $request->input('user');
        $showAll = $request->boolean('show_all');

        if (!$showAll) {
            if (empty($startDate) && empty($endDate)) {
                $startDate = Carbon::today()->toDateString();
                $endDate = Carbon::today()->toDateString();
            } elseif (empty($startDate)) {
                $startDate = Carbon::parse($endDate)->startOfDay()->toDateString();
            } elseif (empty($endDate)) {
                $endDate = Carbon::parse($startDate)->endOfDay()->toDateString();
            }
        }

        $query = NotaKirim::with(['bahanBakus', 'satuans', 'users', 'stokDapurs'])
            ->when(!$showAll && ($startDate || $endDate), function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
            })
            ->when($namaBahanBaku, function ($query, $namaBahanBaku) {
                $query->whereHas('bahanBakus', function ($q) use ($namaBahanBaku) {
                    $q->where('nama', 'like', "%$namaBahanBaku%");
                });
            })
            ->when($kodeBahanBaku, function ($query, $kodeBahanBaku) {
                $query->whereHas('bahanBakus', function ($q) use ($kodeBahanBaku) {
                    $q->where('kode', 'like', "%$kodeBahanBaku%");
                });
            })
            ->when($userName, function ($query, $userName) {
                $query->whereHas('users', function ($q) use ($userName) {
                    $q->where('name', 'like', "%$userName%");
                });
            })
            ->orderBy('created_at');

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $notaKirims = $query->paginate($perPage, ['*'], 'pengiriman_page')->withQueryString();

        $title = 'Laporan Pengiriman Bahan Baku';
        return view('pages.laporan-pengirimanbahanbaku', compact('notaKirims', 'title', 'startDate', 'endDate'));
    }

    public function penggunaanBahanBaku(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Jika tanggal tidak ditentukan, default ke hari ini
        if (empty($startDate) && empty($endDate)) {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } elseif (empty($startDate)) {
            $startDate = Carbon::parse($endDate)->startOfDay()->toDateString();
        } elseif (empty($endDate)) {
            $endDate = Carbon::parse($startDate)->endOfDay()->toDateString();
        }

        // --- Query untuk Penggunaan Bahan Baku ---
        $penggunaanBahanBakuRaw = PenggunaanBahanBaku::query()
            ->select(
                'penggunaan_bahan_bakus.bahan_baku_id',
                'penggunaan_bahan_bakus.satuan_id',
                'penggunaan_bahan_bakus.jumlah_pakai',
                DB::raw('DATE(penggunaan_bahan_bakus.created_at) as date'),
                'satuans.nilai', // Menggunakan kolom 'nilai' dari tabel satuans
                'satuans.nama as satuan_penggunaan_nama' // Ambil nama satuan asli
            )
            ->join('satuans', 'penggunaan_bahan_bakus.satuan_id', '=', 'satuans.id')
            ->with([
                'bahanBakus',
                'satuans' => function($query) { // Eager load relasi satuanKecil jika ada di model Satuan
                    $query->with('satuanKecil');
                }
            ])
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('penggunaan_bahan_bakus.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('penggunaan_bahan_bakus.created_at', '<=', $endDate);
            })
            ->orderBy(DB::raw('DATE(penggunaan_bahan_bakus.created_at)'))
            ->get();

        // --- Agregasi Manual setelah konversi ---
        $penggunaanBahanBakuPerHariAggr = []; // Mengubah nama variabel agar lebih jelas
        foreach ($penggunaanBahanBakuRaw as $item) {
            $date = $item->date;
            $bahanBakuId = $item->bahan_baku_id;

            // Hitung jumlah pakai dalam satuan dasar
            // Menggunakan $item->nilai sebagai faktor konversi
            $jumlahPakaiKonversi = $item->jumlah_pakai * ($item->satuans->getKonversiKeTerkecil() ?? 1); // Tambahkan null coalescing untuk keamanan

            // Menentukan nama satuan yang akan ditampilkan (satuan kecil jika ada, jika tidak, satuan asli)
            $satuanDisplayName = $item->satuans->toSmallestUnit()-> nama;

            // Inisialisasi jika belum ada
            if (!isset($penggunaanBahanBakuPerHariAggr[$date])) {
                $penggunaanBahanBakuPerHariAggr[$date] = [];
            }
            if (!isset($penggunaanBahanBakuPerHariAggr[$date][$bahanBakuId])) {
                $penggunaanBahanBakuPerHariAggr[$date][$bahanBakuId] = [
                    'bahan_baku_name' => $item->bahanBakus->nama ?? 'N/A',
                    'total_jumlah_pakai' => 0, // Ini akan mengakumulasi jumlah setelah konversi
                    'satuan_name' => $satuanDisplayName,
                ];
            }

            // Tambahkan ke total jumlah pakai yang sudah dikonversi
            $penggunaanBahanBakuPerHariAggr[$date][$bahanBakuId]['total_jumlah_pakai'] += $jumlahPakaiKonversi;
        }

        // Mengubah struktur menjadi array list untuk bahan_baku_daily_summary di finalReport
        $formattedBahanBakuSummary = []; // Mengubah nama variabel agar lebih jelas
        foreach ($penggunaanBahanBakuPerHariAggr as $date => $bahanBakus) {
            $formattedBahanBakuSummary[$date] = [];
            foreach ($bahanBakus as $bahanBakuId => $data) {
                $formattedBahanBakuSummary[$date][] = $data; // Langsung masukkan data yang sudah diagregasi
            }
        }

        // --- Query untuk Transaksi per Hari dan Shift ---
        $transaksiPerHariShift = Transaksi::query()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                'shift_kasir_id',
                DB::raw('SUM(total_pembayaran) as total_pembayaran_harian'),
                DB::raw('COUNT(id) as total_transaksi')
            )
            ->with('shiftKasirs.users') // Eager load ShiftKasir dan User-nya
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('tanggal', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('tanggal', '<=', $endDate);
            })
            ->groupBy(DB::raw('DATE(tanggal)'), 'shift_kasir_id')
            ->orderBy(DB::raw('DATE(tanggal)'))
            ->get();

        // --- Struktur Laporan Akhir (Final Report) ---
        $finalReport = [];

        // Mengisi data transaksi per hari dan shift
        foreach ($transaksiPerHariShift as $item) {
            $date = $item->date;
            // Gunakan relasi yang sudah di-eager load untuk ShiftKasir
            $shiftKasirData = $item->shiftKasirs;

            if (!isset($finalReport[$date])) {
                $finalReport[$date] = [
                    'date' => $date,
                    'daily_total_pembayaran' => 0,
                    'daily_total_transaksi' => 0,
                    'shifts' => [],
                    'bahan_baku_daily_summary' => [],
                ];
            }
            $finalReport[$date]['daily_total_pembayaran'] += $item->total_pembayaran_harian;
            $finalReport[$date]['daily_total_transaksi'] += $item->total_transaksi;
            $finalReport[$date]['shifts'][] = [
                'shift_id' => $item->shift_kasir_id,
                // Pastikan saldo_awal dan saldo_akhir diakses dari $shiftKasirData
                'saldo_awal' => $shiftKasirData->saldo_awal ?? 0,
                'saldo_akhir' => $shiftKasirData->saldo_akhir ?? 0,
                'kasir_name' => $shiftKasirData->users->name ?? 'N/A',
                'total_pembayaran_shift' => $item->total_pembayaran_harian,
                'total_transaksi_shift' => $item->total_transaksi,
            ];
        }

        // Mengisi data penggunaan bahan baku ke dalam struktur laporan yang sudah ada
        // Menggunakan $formattedBahanBakuSummary yang sudah diformat
        foreach ($formattedBahanBakuSummary as $date => $bahanBakus) {
            if (!isset($finalReport[$date])) {
                // Jika ada penggunaan bahan baku di tanggal yang tidak ada transaksi, buat entri dasar
                $finalReport[$date] = [
                    'date' => $date,
                    'daily_total_pembayaran' => 0, // Tidak ada transaksi, jadi 0
                    'daily_total_transaksi' => 0,  // Tidak ada transaksi, jadi 0
                    'shifts' => [],                // Tidak ada shift
                    'bahan_baku_daily_summary' => [],
                ];
            }
            // Tetapkan ringkasan bahan baku yang sudah diformat dan dikonversi
            $finalReport[$date]['bahan_baku_daily_summary'] = $bahanBakus;
        }

        // Urutkan laporan berdasarkan tanggal
        ksort($finalReport);

        $title = 'Laporan Penggunaan Bahan Baku';

        return view('pages.laporan-penggunaanbahanbaku', compact('finalReport', 'startDate', 'endDate', 'title'));
    }
}
