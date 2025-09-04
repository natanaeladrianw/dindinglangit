<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use App\Models\Satuan;
use App\Models\StokDapur;
use App\Models\Transaksi;
use App\Models\ShiftKasir;
use Illuminate\Http\Request;
use App\Models\BahanBakuMenu;
use App\Models\TransaksiMenu;
use Illuminate\Support\Facades\DB;
use App\Models\PenggunaanBahanBaku;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $transaksis = Transaksi::where('tipe', 'transaksi')->whereDate('created_at', $today)->paginate(10);

        $title = 'Transaksi';
        return view('pages.transaksi.transaksi', compact('transaksis', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shiftKasir = ShiftKasir::where('user_id', Auth::user()->id)->latest()->first();
        $user = Auth::user();

        // Ambil semua menu dengan relasi yang benar
        $menus = Menu::with(['bahanBakuMenus.grupBahanBaku', 'bahanBakuMenus.satuans'])->get();
        


        // Modifikasi collection untuk menambahkan status ketersediaan yang lebih detail
        $menus = $menus->map(function ($menu) {
            
            // Cek apakah menu memiliki resep
            if ($menu->bahanBakuMenus->isEmpty()) {
                $menu->setAttribute('status', 'no_recipe');
                $menu->setAttribute('tersedia', false);
                $menu->setAttribute('alasan_tidak_tersedia', 'Menu belum masuk ke dalam resep');
                return $menu;
            }

            $tersedia = true;
            $alasan = [];
            $bahanBakuHabis = [];
            $semuaBahanHabis = true;
            $maxPesanan = PHP_INT_MAX; // Inisialisasi dengan nilai maksimal

            // Cek setiap bahan baku dalam menu
            foreach ($menu->bahanBakuMenus as $resep) {
                $grupId = $resep->grup_bahan_baku_id;
                $jumlahResep = $resep->jml_bahan; // Jumlah untuk 1 porsi
                
                // Dapatkan nama bahan baku dari grup bahan baku dengan query langsung
                $namaBahanBaku = 'Bahan Baku';
                $grupBahanBaku = \App\Models\GrupBahanBaku::find($grupId);
                if ($grupBahanBaku) {
                    $namaBahanBaku = $grupBahanBaku->nama;
                }
                


                $stokDapur = StokDapur::whereHas('bahanBakus', function ($query) use ($grupId) {
                        $query->where('grup_bahan_baku_id', $grupId);
                    })
                    ->where('jumlah', '>', 0)
                    ->orderBy('created_at')
                    ->with('satuans')
                    ->first();

                if (!$stokDapur) {
                    $tersedia = false;
                    $bahanBakuHabis[] = $namaBahanBaku;
                    $semuaBahanHabis = $semuaBahanHabis && true;
                } else {
                    $semuaBahanHabis = false;
                    try {
                        $satuanResep = $resep->satuans;
                        $satuanDapur = $stokDapur->satuans;
                        $jumlahDapur = Satuan::convertAmount($jumlahResep, $satuanResep->id, $satuanDapur->id);

                        if ($stokDapur->jumlah < $jumlahDapur) {
                            $tersedia = false;
                            $bahanBakuHabis[] = $namaBahanBaku;
                            $semuaBahanHabis = $semuaBahanHabis && true;
                        } else {
                            $semuaBahanHabis = false;
                            // Hitung berapa kali bisa dipesan berdasarkan stok ini
                            $pesananMungkin = intval($stokDapur->jumlah / $jumlahDapur);
                            $maxPesanan = min($maxPesanan, $pesananMungkin);
                        }
                    } catch (\Exception $e) {
                        $tersedia = false;
                        $bahanBakuHabis[] = $namaBahanBaku;
                        $semuaBahanHabis = $semuaBahanHabis && true;
                    }
                }
            }

            // Tentukan status dan alasan
            if ($semuaBahanHabis) {
                $menu->setAttribute('status', 'all_stock_empty');
                $menu->setAttribute('tersedia', false);
                $menu->setAttribute('alasan_tidak_tersedia', 'Stok habis atau stok belum tersedia');
            } elseif (!empty($bahanBakuHabis)) {
                $menu->setAttribute('status', 'partial_stock_empty');
                $menu->setAttribute('tersedia', false);
                $menu->setAttribute('alasan_tidak_tersedia', 'Bahan baku yang habis: ' . implode(', ', array_unique($bahanBakuHabis)));
            } else {
                $menu->setAttribute('status', 'available');
                $menu->setAttribute('tersedia', true);
                $menu->setAttribute('alasan_tidak_tersedia', null);
                // Tambahkan informasi maksimal pesanan
                if ($maxPesanan < PHP_INT_MAX && $maxPesanan > 0) {
                    $menu->setAttribute('max_pesanan', $maxPesanan);
                    $menu->setAttribute('info_stok', "Menu dapat dipesan sampai {$maxPesanan} kali");
                } else {
                    $menu->setAttribute('max_pesanan', null);
                    $menu->setAttribute('info_stok', null);
                }
            }

            return $menu;
        });

        $title = 'Order';

        return view('pages.transaksi.create-transaksi', compact('user', 'shiftKasir', 'menus', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'shift_kasir_id' => 'required|exists:shift_kasirs,id',
                'menu_id' => 'required|array',
                'menu_id.*' => 'exists:menus,id',
                'jumlah' => 'required|array',
                'jumlah.*' => 'integer|min:1',
                'total_pembayaran' => 'required|numeric|min:0',
                'metode_pembayaran' => 'required|in:Cash,Debit,QRIS,Transfer'
            ]);

            $transaksi = Transaksi::create([
                'user_id' => $request->user_id,
                'shift_kasir_id' => $request->shift_kasir_id,
                'total_pembayaran' => $request->total_pembayaran,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tanggal' => now()
            ]);

            foreach ($request->menu_id as $index => $menuId) {
                $jumlahPesanan = $request->jumlah[$index];
                $catatanPesanan = $request->catatan[$index] ?? null;

                // Validasi maksimal pesanan berdasarkan stok
                $menu = Menu::with(['bahanBakuMenus.grupBahanBaku'])->find($menuId);
                if ($menu) {
                    $maxPesanan = PHP_INT_MAX;
                    foreach ($menu->bahanBakuMenus as $resep) {
                        $grupId = $resep->grup_bahan_baku_id;
                        $jumlahResep = $resep->jml_bahan;
                        
                        $stokDapur = StokDapur::whereHas('bahanBakus', function ($query) use ($grupId) {
                                $query->where('grup_bahan_baku_id', $grupId);
                            })
                            ->where('jumlah', '>', 0)
                            ->orderBy('created_at')
                            ->with('satuans')
                            ->first();
                        
                        if ($stokDapur) {
                            try {
                                $satuanResep = $resep->satuans;
                                $satuanDapur = $stokDapur->satuans;
                                $jumlahDapur = Satuan::convertAmount($jumlahResep, $satuanResep->id, $satuanDapur->id);
                                
                                $pesananMungkin = intval($stokDapur->jumlah / $jumlahDapur);
                                $maxPesanan = min($maxPesanan, $pesananMungkin);
                            } catch (\Exception $e) {
                                // Skip jika konversi gagal
                            }
                        }
                    }
                    
                    if ($maxPesanan < PHP_INT_MAX && $jumlahPesanan > $maxPesanan) {
                        DB::rollBack();
                        return redirect()->route('transaksi.create')->with('alert', 
                            "Menu '{$menu->nama_item}' hanya dapat dipesan maksimal {$maxPesanan} kali berdasarkan stok tersedia."
                        );
                    }
                }

                $transaksi->menus()->attach($menuId, [
                    'jumlah_pesanan' => $jumlahPesanan,
                    'catatan_pesanan' => $catatanPesanan,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $resepsMenu = BahanBakuMenu::with(['bahanBakus', 'satuans', 'grupBahanBaku'])
                    ->where('menu_id', $menuId)
                    ->get();

                if ($resepsMenu->isEmpty()) {
                    DB::rollBack();
                    return redirect()->route('transaksi.create')->with('alert', 'Menu belum memiliki resep, silakan tambahkan resep!');
                }

                // dd($resepsMenu);

                foreach ($resepsMenu as $resep) {
                    $grupId = $resep->grupBahanBaku->id;
                    $jumlahResep = $resep->jml_bahan * $jumlahPesanan;
                    $satuanResep = $resep->satuans;

                    $stokDapur = StokDapur::whereHas('bahanBakus', function ($query) use ($grupId) {
                            $query->where('grup_bahan_baku_id', $grupId);
                        })
                        ->where('jumlah', '>', 0)
                        ->orderBy('created_at')
                        ->with('satuans')
                        ->lockForUpdate() // Kunci row
                        ->first();

                    if (!$stokDapur) {
                        DB::rollBack();
                        return redirect()->route('transaksi.create')->with('alert', 'Stok untuk grup bahan baku tidak tersedia.');
                    }

                    $satuanDapur = $stokDapur->satuans;
                    try {
                        $jumlahDapur = Satuan::convertAmount($jumlahResep, $satuanResep->id, $satuanDapur->id);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return back()->with('alert', 'Konversi satuan gagal untuk bahan: ' . $resep->bahanBakus->nama . $e->getMessage());
                    }

                    // if ($resep->grupBahanBaku->nama === 'Air Mineral Galon') {
                    //     dd([
                    //         'stokDapur' => $stokDapur,
                    //         'jumlahResep' => $jumlahResep,
                    //         'jumlahDapur' => $jumlahDapur,
                    //         'satuanDapur' => $satuanDapur,
                    //     ]);
                    // }

                    if ($stokDapur->jumlah < $jumlahDapur) {
                        DB::rollBack();
                        return redirect()->route('transaksi.create')->with('alert',
                            'Stok bahan baku "' . ($resep->bahanBakus->nama ?? 'N/A') .
                            '" tidak mencukupi. Dibutuhkan: ' . $jumlahDapur .
                            ' ' . ($satuanDapur->nama ?? 'N/A') .
                            ', Stok Tersedia: ' . ($stokDapur->jumlah ?? 0) . ' ' . ($satuanDapur->nama ?? 'N/A') . '.'
                        );
                    }

                    $sisaFisikBaru = $stokDapur->jumlah - $jumlahDapur;

                    PenggunaanBahanBaku::create([
                        'bahan_baku_id' => $stokDapur->bahan_baku_id,
                        'satuan_id' => $satuanResep->id,
                        'jumlah_pakai' => $jumlahResep,
                        'sisa_fisik' => $sisaFisikBaru,
                    ]);

                    $stokDapur->update([
                        'jumlah' => $sisaFisikBaru,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('transaksi.index')->with('alert', 'Transaksi berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat membuat transaksi: \n' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $transaksi->update([
            'status' => (int) $request->status,
        ]);

        return back()->with('alert', 'Status transaksi berhasil diperbarui.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:transaksis,id',
            'status' => 'required|in:0,1',
        ]);

        Transaksi::whereIn('id', $request->ids)->update([
            'status' => (int) $request->status,
        ]);

        return back()->with('alert', 'Status transaksi terpilih berhasil diperbarui.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaksi $transaksi)
    {
        $user = Auth::user();
        $title = 'Transaksi';

        return view('pages.transaksi.show-transaksi', compact('user', 'transaksi', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaksi $transaksi)
    {
        $title = 'Transaksi';
        return view('pages.transaksi.edit-transaksi', compact('transaksi', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaksi $transaksi)
    {
        try {
            $request->validate([
                'status' => 'required'
            ]);

            $transaksi->update([
                'status' => $request->status
            ]);

            return redirect()->route('transaksi.index')->with('alert', 'Transaksi berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat membuat transaksi: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaksi $transaksi)
    {
        //
    }
}
