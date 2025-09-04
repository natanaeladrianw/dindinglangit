<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Http\Requests\StoreKategoriMenuRequest;
use App\Http\Requests\UpdateKategoriMenuRequest;

class KategoriMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoriMenus = KategoriMenu::all();
        $title = 'Kategori Menu';

        return view('pages.kategorimenu.kategorimenu', compact('kategoriMenus', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Kategori Menu';

        return view('pages.kategorimenu.create-kategorimenu', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKategoriMenuRequest $request)
    {
        try {
            $validated = $request->validated();

            KategoriMenu::create($validated);

            return redirect()->route('kategorimenu.index')->with('alert', 'Kategori Menu berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan kategori menu: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriMenu $kategorimenu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriMenu $kategorimenu)
    {
        $title = 'Kategori Menu';

        return view('pages.kategorimenu.edit-kategorimenu', compact('kategorimenu', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKategoriMenuRequest $request, KategoriMenu $kategorimenu)
    {
        try {
            $validatedData = $request->validated();

            $kategorimenu->update($validatedData);

            return redirect()->route('kategorimenu.index')->with('alert', 'Kategori Menu berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui Kategori Menu: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriMenu $kategorimenu)
    {
        try {
            $kategorimenu->delete();

            return redirect()->route('kategorimenu.index')->with('alert', 'Kategori Menu berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus Kategori Menu: \n' . $e->getMessage());
        }
    }
}
