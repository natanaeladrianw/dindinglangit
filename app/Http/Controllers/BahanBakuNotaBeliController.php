<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\NotaBeli;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use App\Models\BahanBakuNotaBeli;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBahanBakuNotaBeliRequest;
use App\Http\Requests\UpdateBahanBakuNotaBeliRequest;

class BahanBakuNotaBeliController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notaBelis = BahanBakuNotaBeli::with(['bahanBakus', 'notaBelis'])->paginate(15);
        $title = 'Nota Beli';
        return view('pages.notabeli.notabeli', compact('notaBelis', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get bahan baku that don't have any nota beli records yet
        $bahanBakus = BahanBaku::whereDoesntHave('bahanBakuNotaBelis')->get();
        $suppliers = Supplier::all();
        $satuans = Satuan::all();
        $title = 'Nota Beli';
        return view('pages.notabeli.create-notabeli', compact('suppliers', 'satuans', 'bahanBakus', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBahanBakuNotaBeliRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            // dd($validatedData);
            // 1. Create Nota Beli
            $notabaru = NotaBeli::create([
                'supplier_id' => $validatedData['supplier_id'],
                'tanggal_transaksi' => $validatedData['tanggal_transaksi']
            ]);

            // 2. Attach bahan baku
            $stokGudang = StokGudang::where('bahan_baku_id', $validatedData['bahan_baku_id'])->first();
            $satuanRequest = Satuan::find($validatedData['satuan_id']);
            $jumlahPengajuan = $validatedData['jumlah'];

            if ($stokGudang === null) {
                // Gunakan satuan dari request sebagai satuan awal gudang
                $satuanGudang = $satuanRequest;
                $jumlahDalamSatuanGudang = $jumlahPengajuan;
            } else {
                $satuanGudang = $stokGudang->satuans;

                if ($satuanGudang->id !== $satuanRequest->id) {
                    try {
                        $jumlahDalamSatuanGudang = Satuan::convertToAmount($jumlahPengajuan, $satuanRequest->id, $satuanGudang->id);
                    } catch (\Exception $e) {
                        return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                    }
                } else {
                    $jumlahDalamSatuanGudang = $jumlahPengajuan;
                }
            }

            // Konversi jumlah jika satuan tidak sama
            if ($satuanGudang->id !== $satuanRequest->id) {
                try {
                    $jumlahDalamSatuanGudang = Satuan::convertToAmount($jumlahPengajuan, $satuanRequest->id, $satuanGudang->id);
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }
            } else {
                $jumlahDalamSatuanGudang = $jumlahPengajuan;
            }

            $notabaru->bahanBakus()->attach($validatedData['bahan_baku_id'], [
                'harga' => $validatedData['harga'],
                'jumlah' => $jumlahDalamSatuanGudang,
                'tgl_exp' => $validatedData['tgl_exp'] ?? null,
            ]);

            // dd($notabaru);
            // 3. Update stok gudang
            $stokGudang = StokGudang::updateOrCreate(
                ['bahan_baku_id' => $validatedData['bahan_baku_id']],
                [
                    'jumlah' => DB::raw("COALESCE(jumlah, 0) + " . (float)$jumlahDalamSatuanGudang),
                    'tanggal_exp' => $validatedData['tgl_exp'] ?? null,
                    'satuan_id' => $satuanGudang->id
                ]
            );

            // dd($stokGudang);

            DB::commit();

            return redirect()->route('notabeli.index')->with('alert', 'Nota Beli berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BahanBakuNotaBeli $notabeli)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BahanBakuNotaBeli $notabeli)
    {
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::all();
        $satuans = Satuan::all();
        $title = 'Nota Beli';
        return view('pages.notabeli.edit-notabeli', compact('satuans', 'suppliers', 'notabeli', 'bahanBakus', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBahanBakuNotaBeliRequest $request, BahanBakuNotaBeli $notabeli)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            // dd($validatedData);
            // 1. Get original values from pivot
            $originalBahanBakuId = $notabeli->bahan_baku_id;
            $originalJumlah = $notabeli->jumlah; // Now from pivot table
            $newBahanBakuId = $validatedData['bahan_baku_id'];
            $newJumlah = $validatedData['jumlah'];

            // 2. Handle stock changes
            $stokGudang = StokGudang::where('bahan_baku_id', $validatedData['bahan_baku_id'])->firstOrFail();
            $satuanRequest = Satuan::find($validatedData['satuan_id']);
            $satuanGudang = $stokGudang->satuans;
            // Konversi jumlah jika satuan tidak sama
            if ($satuanGudang->id !== $satuanRequest->id) {
                try {
                    $jumlahDalamSatuanGudang = Satuan::convertToAmount($newJumlah, $satuanRequest->id, $satuanGudang->id);
                    // dd($jumlahDalamSatuanGudang);
                } catch (\Exception $e) {
                    return back()->with('alert', 'Gagal konversi satuan: ' . $e->getMessage());
                }
            } else {
                $jumlahDalamSatuanGudang = $newJumlah;
                // dd($jumlahDalamSatuanGudang);
            }

            if ($originalBahanBakuId == $newBahanBakuId) {
                // Case 1: Same item - adjust by difference
                $difference = $jumlahDalamSatuanGudang - $originalJumlah;
                // dd($difference);
                StokGudang::where('bahan_baku_id', $newBahanBakuId)
                    ->increment('jumlah', $difference);
            } else {
                // Case 2: Different item
                // 2a. Reverse original stock impact
                StokGudang::where('bahan_baku_id', $originalBahanBakuId)
                    ->decrement('jumlah', $originalJumlah);

                // 2b. Add new stock impact
                StokGudang::where('bahan_baku_id', $newBahanBakuId)
                    ->increment('jumlah', $jumlahDalamSatuanGudang);
            }

            // dd($notabeli);

            // 3. Update pivot record (including jumlah)
            $updateData = [
                'bahan_baku_id' => $newBahanBakuId,
                'jumlah' => $jumlahDalamSatuanGudang,
                'tgl_exp' => $validatedData['tgl_exp'] ?? null,
            ];

            $notaBeliData = [
                'supplier_id' => $validatedData['supplier_id'],
                'tanggal_transaksi' => $validatedData['tanggal_transaksi'],
            ];

            $notabeliBase = Notabeli::find($validatedData['nota_beli_id']);
            $notabeliBase->update($notaBeliData);

            if (isset($validatedData['harga'])) {
                // Bersihkan format harga
                $harga = str_replace(',', '', $validatedData['harga']);
                $harga = is_numeric($harga) ? (float)$harga : 0;
                $updateData['harga'] = $harga;
            }

            // dd($updateData);
            $notabeli = $notabeli->update($updateData);

            StokGudang::where('bahan_baku_id', $newBahanBakuId)
                    ->update(['tanggal_exp' => $validatedData['tgl_exp'] ?? null]);

            // 4. Update satuan_id if provided
            if (isset($validatedData['satuan_id'])) {
                StokGudang::where('bahan_baku_id', $newBahanBakuId)
                    ->update(['satuan_id' => $satuanGudang->id]);
            }

            DB::commit();
            return redirect()->route('notabeli.index')->with('alert', 'Nota beli berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BahanBakuNotaBeli $notabeli)
    {
        DB::beginTransaction();
        try {
            // 1. Check if bahan baku has been sent to dapur
            $bahanBaku = BahanBaku::find($notabeli->bahan_baku_id);
            if ($bahanBaku && $bahanBaku->stokDapurs()->count() > 0) {
                DB::rollBack();
                return redirect()->back()->with('alert', 'Tidak dapat menghapus nota beli karena bahan baku ini sudah dikirim ke dapur.');
            }

            // 2. Dapatkan data stok yang terkait
            $stokGudang = StokGudang::where('bahan_baku_id', $notabeli->bahan_baku_id)->first();
            
            if ($stokGudang) {
                // 3. Hapus seluruh record stok gudang untuk bahan baku ini
                $stokGudang->delete();
            }

            // 4. Hapus record nota beli
            $notabeli->delete();

            // 5. Hapus record nota beli base jika tidak ada lagi bahan baku yang terkait
            $notaBeliBase = NotaBeli::find($notabeli->nota_beli_id);
            if ($notaBeliBase && $notaBeliBase->bahanBakus()->count() == 0) {
                $notaBeliBase->delete();
            }

            DB::commit();
            return redirect()->route('notabeli.index')->with('alert', 'Nota Beli berhasil dihapus dan stok gudang dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('alert', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
