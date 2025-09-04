<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $satuans = Satuan::with('satuanKecil')->orderBy('nama')->get();
        $title = 'Satuan';

        return view('pages.satuan.satuan', compact('title', 'satuans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $satuans = Satuan::all();
        $title = 'Satuan';
        return view('pages.satuan.create-satuan', compact('title', 'satuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'reference_satuan_id' => 'nullable|exists:satuans,id',
            'nilai' => 'required|min:1',
        ]);

        Satuan::create($request->all());

        return redirect()->route('satuan.index')->with('alert', 'Satuan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Satuan $satuan)
    {
        return view('pages.satuan.show', compact('satuan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Satuan $satuan)
    {
        $satuans = Satuan::where('id', '!=', $satuan->id)->get();
        $title = 'Satuan';
        return view('pages.satuan.edit-satuan', compact('title', 'satuan', 'satuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Satuan $satuan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'reference_satuan_id' => 'nullable|exists:satuans,id',
            'nilai' => 'required|min:1',
        ]);

        $satuan->update($request->all());

        return redirect()->route('satuan.index')->with('alert', 'Satuan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Satuan $satuan)
    {
        $satuan->delete();
        return redirect()->route('satuan.index')->with('alert', 'Satuan berhasil dihapus.');
    }
}
