<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBahanBaku;
use App\Http\Requests\StoreKategoriBahanBakuRequest;
use App\Http\Requests\UpdateKategoriBahanBakuRequest;

class KategoriBahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoriBahanBakus = KategoriBahanBaku::all();
        $title = 'Kategori Bahan Baku';

        return view('pages.kategoribahanbaku.kategoribahanbaku', compact('kategoriBahanBakus', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Kategori Bahan Baku';
        return view('pages.kategoribahanbaku.create-kategoribahanbaku', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKategoriBahanBakuRequest $request)
    {
        try {
            $validatedData = $request->validated();

            KategoriBahanBaku::create($validatedData);

            return redirect()->route('kategoribahanbaku.index')->with('alert', 'Kategori Bahan Baku berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan kategori bahan baku: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriBahanBaku $kategoriBahanBaku)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriBahanBaku $kategoribahanbaku)
    {
        $title = 'Kategori Bahan Baku';

        return view('pages.kategoribahanbaku.edit-kategoribahanbaku', compact('kategoribahanbaku', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKategoriBahanBakuRequest $request, KategoriBahanBaku $kategoribahanbaku)
    {
        try {
            $validatedData = $request->validated();

            $kategoribahanbaku->update($validatedData);

            return redirect()->route('kategoribahanbaku.index')->with('alert', 'Kategori Bahan Baku berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui kategori bahan baku: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriBahanBaku $kategoribahanbaku)
    {
        try {
            $kategoribahanbaku->delete();

            return redirect()->route('kategoribahanbaku.index')->with('alert', 'Kategori Bahan Baku berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus kategori bahan baku: \n' . $e->getMessage());
        }
    }
}
