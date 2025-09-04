<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\BahanBaku;
use App\Models\NotaKirim;
use App\Models\StokDapur;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PenggunaanBahanBaku;
use App\Http\Requests\StoreStokDapurRequest;
use App\Http\Requests\UpdateStokDapurRequest;

class StokDapurController extends Controller
{
    public function ajukanDapur($id)
    {
        $stokDapur = StokDapur::find($id);
        
        // Update status pengajuan di stok dapur
        $stokDapur->update([
            'pengajuan' => 1
        ]);
        
        // Update status pengajuan di grup bahan baku
        $bahanBaku = $stokDapur->bahanBakus;
        if ($bahanBaku && $bahanBaku->grupBahanBaku) {
            $bahanBaku->grupBahanBaku->update([
                'pengajuan' => 1
            ]);
        }

        return back()->with('alert', 'Pengajuan restok berhasil!');
    }

    public function restokDapur(Request $request, $id)
    {
        $stokdapur = StokDapur::find($id);
        $bahanBakus = BahanBaku::all();
        $satuans = Satuan::all();
        $title = 'Dashboard';

        return view('pages.stokdapur.edit-stokdapur', compact('stokdapur', 'bahanBakus', 'satuans', 'title'));
    }

    public function tambahStokDapur(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'bahan_baku_id' => 'required|exists:bahan_bakus,id',
                'satuan_id' => 'required|exists:satuans,id',
                'jumlah' => 'required',
            ]);

            $stokDapur = StokDapur::where('bahan_baku_id', $request->bahan_baku_id)->firstOrFail();
            $stokGudang = StokGudang::where('bahan_baku_id', $request->bahan_baku_id)->firstOrFail();

            // Check if bahan baku is expired
            if ($stokGudang->tanggal_exp && \Carbon\Carbon::parse($stokGudang->tanggal_exp)->isPast()) {
                return back()->with('alert', 'Bahan baku ini sudah kadaluwarsa dan tidak dapat dikirim ke dapur!');
            }

            $satuanRequest = Satuan::find($request->satuan_id);
            $satuanGudang = $stokGudang->satuans;
            // dd($satuanRequest);
            // dd($satuanRequest);

            $jumlahPengajuan = $request->jumlah;

            // Konversi jumlah jika satuan tidak sama
            if ($satuanGudang->id !== $satuanRequest->id) {
                try {
                    // Ubah jumlah dari satuan dapur ke satuan gudang untuk pengecekan stok
                    $jumlahDalamSatuanGudang = Satuan::convertAmount($jumlahPengajuan, $satuanRequest->id, $satuanGudang->id);
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }
            } else {
                $jumlahDalamSatuanGudang = $jumlahPengajuan;
            }

            // Cek apakah stok gudang cukup
            if ($jumlahDalamSatuanGudang <= $stokGudang->jumlah) {
                // Update Stok Dapur (jumlah tetap di satuan dapur)
                if ($stokDapur) {
                    $satuanGudangTerkecil = $satuanGudang->toSmallestUnit();
                    // dd($satuanGudangTerkecil);
                    try {
                        $jumlahDalamSatuanTerkecil = Satuan::convertAmount($jumlahPengajuan, $satuanRequest->id, $satuanGudangTerkecil->id);
                        // dd($jumlahDalamSatuanTerkecil);
                    } catch (\Exception $e) {
                        return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                    }

                    $jumlahStok = $stokDapur->jumlah + $jumlahDalamSatuanTerkecil;

                    $stokDapur->update([
                        'jumlah' => $jumlahStok,
                        'pengajuan' => 0,
                    ]);

                    PenggunaanBahanBaku::create([
                        'bahan_baku_id' => $request->bahan_baku_id,
                        'satuan_id' => $stokDapur->satuan_id,
                        'jumlah_pakai' => 0,
                        'sisa_fisik' => $jumlahStok,
                        'keterangan' => 'Penambahan stok dari gudang', // Penanda jelas
                    ]);

                    NotaKirim::create([
                        'bahan_baku_id' => $request->bahan_baku_id,
                        'satuan_id' => $stokDapur->satuan_id,
                        'stok_dapur_id' => $stokDapur->id,
                        'jumlah' => $jumlahDalamSatuanTerkecil,
                        'user_id' => auth()->id(),
                        'keterangan' => 'Transfer stok dari gudang ke dapur',
                    ]);
                } else {
                    $stokDapur = StokDapur::create([
                        'bahan_baku_id' => $request->bahan_baku_id,
                        'satuan_id' => $satuanGudangTerkecil->id,
                        'jumlah' => $jumlahDalamSatuanTerkecil,
                        'pengajuan' => 0
                    ]);

                    NotaKirim::create([
                        'bahan_baku_id' => $request->bahan_baku_id,
                        'satuan_id' => $satuanGudangTerkecil->id,
                        'stok_dapur_id' => $stokDapur->id,
                        'jumlah' => $jumlahDalamSatuanTerkecil,
                        'user_id' => auth()->id(),
                        'keterangan' => 'Transfer stok dari gudang ke dapur',
                    ]);
                }

                // Kurangi stok gudang (jumlah sudah dikonversi ke satuan gudang)
                $stokGudang->update([
                    'jumlah' => $stokGudang->jumlah - $jumlahDalamSatuanGudang,
                ]);

                DB::commit();
                return back()->with('alert', 'Penambahan stok berhasil!');
            } else {
                DB::rollBack();
                return back()->with('alert', 'Penambahan stok gagal, stok gudang tidak cukup!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan Stok Dapur: \n' . $e->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Stok Dapur';

        $stokQuery = StokDapur::with(['satuans.satuanKecil']);

        // Filter hanya berlaku untuk admin_dapur
        if (auth()->user()->role === 'admin_dapur' && $request->filled(['start_date', 'end_date'])) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();

            $stokQuery->whereBetween('updated_at', [$start, $end]);
        }

        $stokDapurs = $stokQuery->get();
        
        // Add expiration data from stok gudang and calculate expiration status
        $stokDapurs->each(function ($stokDapur) {
            $stokGudang = StokGudang::where('bahan_baku_id', $stokDapur->bahan_baku_id)->first();
            if ($stokGudang) {
                $stokDapur->tanggal_exp = $stokGudang->tanggal_exp;
                
                // Calculate expiration status exactly like stok gudang (35 days threshold)
                if ($stokGudang->tanggal_exp) {
                    $expDate = \Carbon\Carbon::parse($stokGudang->tanggal_exp);
                    $now = \Carbon\Carbon::now();
                    
                    if ($expDate->isPast()) {
                        $stokDapur->mendekati_kadaluarsa = 0;
                        $stokDapur->sudah_kadaluarsa = $stokDapur->jumlah;
                    } elseif ($expDate->diffInDays($now) <= 35) {
                        $stokDapur->mendekati_kadaluarsa = $stokDapur->jumlah;
                        $stokDapur->sudah_kadaluarsa = 0;
                    } else {
                        $stokDapur->mendekati_kadaluarsa = 0;
                        $stokDapur->sudah_kadaluarsa = 0;
                    }
                } else {
                    $stokDapur->mendekati_kadaluarsa = 0;
                    $stokDapur->sudah_kadaluarsa = 0;
                }
            } else {
                $stokDapur->tanggal_exp = null;
                $stokDapur->mendekati_kadaluarsa = 0;
                $stokDapur->sudah_kadaluarsa = 0;
            }
        });
        
        $belumAdaStok = BahanBaku::whereDoesntHave('stokDapurs')->get();

        return view('pages.stokdapur.stokdapur', compact('stokDapurs', 'belumAdaStok', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bahanBakus = BahanBaku::whereDoesntHave('stokDapurs')->get();
        $satuans = Satuan::all();
        $title = 'Stok Dapur';

        return view('pages.stokdapur.create-stokdapur', compact('satuans', 'bahanBakus', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStokDapurRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'bahan_baku_id' => 'required|exists:bahan_bakus,id',
                'satuan_id' => 'required|exists:satuans,id',
                'jumlah' => 'required',
            ]);

            $stokGudang = StokGudang::where('bahan_baku_id', $request->bahan_baku_id)->firstOrFail();

            // Check if bahan baku is expired
            if ($stokGudang->tanggal_exp && \Carbon\Carbon::parse($stokGudang->tanggal_exp)->isPast()) {
                return back()->with('alert', 'Bahan baku ini sudah kadaluwarsa dan tidak dapat dikirim ke dapur!');
            }

            $satuanRequest = Satuan::find($request->satuan_id);
            $satuanGudang = $stokGudang->satuans;
            // dd($satuanRequest);

            $jumlahPengajuan = $request->jumlah;

            // Konversi jumlah jika satuan tidak sama
            if ($satuanGudang->id !== $satuanRequest->id) {
                try {
                    // Ubah jumlah dari satuan dapur ke satuan gudang untuk pengecekan stok
                    $jumlahDalamSatuanGudang = Satuan::convertAmount($jumlahPengajuan, $satuanRequest->id, $satuanGudang->id);
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }
            } else {
                $jumlahDalamSatuanGudang = $jumlahPengajuan;
            }

            // Cek apakah stok gudang cukup
            if ($jumlahDalamSatuanGudang <= $stokGudang->jumlah) {
                $satuanGudangTerkecil = $satuanGudang->toSmallestUnit();
                // dd($satuanGudangTerkecil);
                try {
                    $jumlahDalamSatuanTerkecil = Satuan::convertAmount($jumlahPengajuan, $satuanRequest->id, $satuanGudangTerkecil->id);
                    // dd($jumlahDalamSatuanTerkecil);
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }

                $stokDapur = StokDapur::create([
                    'bahan_baku_id' => $request->bahan_baku_id,
                    'satuan_id' => $satuanGudangTerkecil->id,
                    'jumlah' => $jumlahDalamSatuanTerkecil,
                    'pengajuan' => 0
                ]);

                NotaKirim::create([
                    'bahan_baku_id' => $request->bahan_baku_id,
                    'satuan_id' => $satuanGudangTerkecil->id,
                    'stok_dapur_id' => $stokDapur->id,
                    'jumlah' => $jumlahDalamSatuanTerkecil,
                    'user_id' => auth()->id(),
                    'keterangan' => 'Transfer stok dari gudang ke dapur',
                ]);

                // Kurangi stok gudang (jumlah sudah dikonversi ke satuan gudang)
                $stokGudang->update([
                    'jumlah' => $stokGudang->jumlah - $jumlahDalamSatuanGudang,
                ]);

                DB::commit();
                return back()->with('alert', 'Penambahan stok berhasil!');
            } else {
                DB::rollBack();
                return back()->with('alert', 'Penambahan stok gagal, stok gudang tidak cukup!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan Stok Dapur: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StokDapur $stokdapur)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokDapur $stokdapur)
    {
        $bahanBakus = BahanBaku::all();
        $satuans = Satuan::all();
        $title = 'Stok Dapur';

        return view('pages.stokdapur.edit-stokdapur', compact('satuans', 'stokdapur', 'bahanBakus', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStokDapurRequest $request, StokDapur $stokdapur)
    {
        try {
            $validatedData = $request->validated();

            $stokdapur->update($validatedData);

            return redirect()->route('stokdapur.index')->with('alert', 'Stok Dapur berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui Stok Dapur: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokDapur $stokdapur)
    {
        try {
            $stokdapur->delete();

            return redirect()->route('stokdapur.index')->with('alert', 'Stok Dapur berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus Stok Dapur: \n' . $e->getMessage());
        }
    }
}
