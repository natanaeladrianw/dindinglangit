<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Models\StokMinimal;
use App\Models\GrupBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StokMinimalController extends Controller
{
    public function index()
    {
        $title = 'Satuan Minimal';
        $query = StokMinimal::with(['grupBahanBaku', 'satuans']);
        if (Schema::hasColumn('stok_minimals', 'lokasi')) {
            $query->orderBy('lokasi');
        }
        $items = $query->get();
        return view('pages.stok_minimal.stok_minimal', compact('items', 'title'));
    }

    public function create()
    {
        $title = 'Tambah Satuan Minimal';
        $grupBahanBakus = GrupBahanBaku::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();
        return view('pages.stok_minimal.create-stok_minimal', compact('title', 'grupBahanBakus', 'satuans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grup_bahan_baku_id' => 'required|exists:grup_bahan_bakus,id',
            'satuan_id' => 'required|exists:satuans,id',
            'lokasi' => 'required|in:dapur,gudang',
            'jumlah_minimal' => 'required|integer|min:0',
        ]);

        // Update-or-create to avoid duplicates per grup + lokasi
        StokMinimal::updateOrCreate(
            ['grup_bahan_baku_id' => $data['grup_bahan_baku_id'], 'lokasi' => $data['lokasi']],
            ['satuan_id' => $data['satuan_id'], 'jumlah_minimal' => $data['jumlah_minimal']]
        );

        return redirect()->route('stok-minimal.index')->with('success', 'Satuan minimal disimpan.');
    }

    public function edit(StokMinimal $stokMinimal)
    {
        $title = 'Ubah Satuan Minimal';
        $grupBahanBakus = GrupBahanBaku::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();
        return view('pages.stok_minimal.edit-stok_minimal', compact('title', 'stokMinimal', 'grupBahanBakus', 'satuans'));
    }

    public function update(Request $request, StokMinimal $stokMinimal)
    {
        $data = $request->validate([
            'grup_bahan_baku_id' => 'required|exists:grup_bahan_bakus,id',
            'satuan_id' => 'required|exists:satuans,id',
            'lokasi' => 'required|in:dapur,gudang',
            'jumlah_minimal' => 'required|integer|min:0',
        ]);

        $stokMinimal->update($data);
        return redirect()->route('stok-minimal.index')->with('success', 'Satuan minimal diperbarui.');
    }

    public function destroy(StokMinimal $stokMinimal)
    {
        $stokMinimal->delete();
        return redirect()->route('stok-minimal.index')->with('success', 'Satuan minimal dihapus.');
    }
}


