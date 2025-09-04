<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::all();
        $title = 'Menu';
        return view('pages.menu.menu', compact('menus', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori_menu = KategoriMenu::all();
        $title = 'Menu';

        return view('pages.menu.create-menu', compact('kategori_menu', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request)
    {
        try {
            $validatedData = $request->validated();

            Menu::create($validatedData);

            return redirect()->route('menu.index')->with('alert', 'Menu berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat membuat menu: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $kategori_menu = KategoriMenu::all();
        $title = 'Menu';

        return view('pages.menu.edit-menu', compact('kategori_menu', 'menu', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        try {
            $validatedData = $request->validated();

            $menu->update($validatedData);

            return redirect()->route('menu.index')->with('alert', 'Menu berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui menu: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        try {
            $menu->delete();

            return redirect()->route('menu.index')->with('alert', 'Menu berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus menu: \n' . $e->getMessage());
        }
    }
}
