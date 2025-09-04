<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\StokDapur;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use App\Models\GrupBahanBaku;
use App\Models\KategoriBahanBaku;
use App\Http\Requests\StoreBahanBakuRequest;
use App\Http\Requests\UpdateBahanBakuRequest;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bahanBakus = BahanBaku::all();
        $title = 'Bahan Baku';
        return view('pages.bahanbaku.bahanbaku', compact('bahanBakus', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $grupBahanBakus = GrupBahanBaku::all();
        $kategori_bahan_bakus = KategoriBahanBaku::all();
        $title = 'Bahan Baku';
        return view('pages.bahanbaku.create-bahanbaku', compact('kategori_bahan_bakus', 'grupBahanBakus', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $count = count($request->nama); // asumsi semua array field konsisten panjangnya
            
            // Validasi kode duplikat
            $existingKodes = BahanBaku::pluck('kode')->toArray();
            $duplicateKodes = [];
            
            for ($i = 0; $i < $count; $i++) {
                if (in_array($request->kode[$i], $existingKodes)) {
                    $duplicateKodes[] = $request->kode[$i];
                }
            }
            
            if (!empty($duplicateKodes)) {
                return redirect()->back()
                    ->with('alert', 'Kode bahan baku berikut sudah ada: ' . implode(', ', $duplicateKodes))
                    ->withInput();
            }

            for ($i = 0; $i < $count; $i++) {
                BahanBaku::create([
                    'grup_bahan_baku_id' => $request->grup_bahan_baku_id[$i],
                    'kategori_bahan_baku_id' => $request->kategori_bahan_baku_id[$i],
                    'kode' => $request->kode[$i],
                    'nama' => $request->nama[$i],
                ]);
            }

            return redirect()->route('bahanbaku.index')->with('alert', 'Semua bahan baku berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Gagal menambahkan bahan baku: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BahanBaku $bahanBaku)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BahanBaku $bahanbaku)
    {
        $grupBahanBakus = GrupBahanBaku::all();
        $kategori_bahan_baku = KategoriBahanBaku::all();
        $title = 'Bahan Baku';

        return view('pages.bahanbaku.edit-bahanbaku', compact('kategori_bahan_baku', 'bahanbaku', 'grupBahanBakus', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBahanBakuRequest $request, BahanBaku $bahanbaku)
    {
        try {
            $validatedData = $request->validated();

            $bahanbaku->update($validatedData);

            return redirect()->route('bahanbaku.index')->with('alert', 'Bahan Baku berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui bahan baku: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BahanBaku $bahanbaku)
    {
        try {
            $bahanbaku->delete();

            return redirect()->route('bahanbaku.index')->with('alert', 'Bahan Baku berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus bahan baku: \n' . $e->getMessage());
        }
    }
}
