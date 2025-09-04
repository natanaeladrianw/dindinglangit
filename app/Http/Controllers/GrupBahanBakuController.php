<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GrupBahanBaku;
use App\Http\Requests\StoreGrupBahanBakuRequest;
use App\Http\Requests\UpdateGrupBahanBakuRequest;

class GrupBahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupBahanBakus = GrupBahanBaku::all();
        $title = 'Grup Bahan Baku';
        return view('pages.grupbahanbaku.grupbahanbaku', compact('title', 'grupBahanBakus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Grup Bahan Baku';
        return view('pages.grupbahanbaku.create-grupbahanbaku', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:grup_bahan_bakus,nama',
            'keterangan' => 'nullable'
        ]);

        GrupBahanBaku::create($validated);

        return redirect()->route('grupbahanbaku.index')->with('alert', 'Grup bahan baku berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GrupBahanBaku $grupbahanbaku)
    {
        $title = 'Grup Bahan Baku';
        $grup = $grupbahanbaku;
        $bahanBakus = $grupbahanbaku->bahanBakus()->with('kategoriBahanBakus')->get();
        return view('pages.grupbahanbaku.show-grupbahanbaku', compact('title', 'grup', 'bahanBakus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GrupBahanBaku $grupbahanbaku)
    {
        $title = 'Grup Bahan Baku';
        return view('pages.grupbahanbaku.edit-grupbahanbaku', compact('title', 'grupbahanbaku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GrupBahanBaku $grupbahanbaku)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:grup_bahan_bakus,nama,' . $grupbahanbaku->id,
            'keterangan' => 'nullable',
        ]);

        $grupbahanbaku->update($validated);

        return redirect()->route('grupbahanbaku.index')->with('alert', 'Grup bahan baku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GrupBahanBaku $grupbahanbaku)
    {
        $grupbahanbaku->delete();
        return redirect()->route('grupbahanbaku.index')->with('alert', 'Grup bahan baku berhasil dihapus.');
    }
    
    /**
     * Toggle pengajuan status
     */
    public function togglePengajuan(GrupBahanBaku $grupbahanbaku)
    {
        $grupbahanbaku->update([
            'pengajuan' => !$grupbahanbaku->pengajuan
        ]);
        
        $status = $grupbahanbaku->pengajuan ? 'diajukan' : 'dibatalkan';
        return redirect()->back()->with('alert', "Status pengajuan grup bahan baku berhasil {$status}.");
    }
}
