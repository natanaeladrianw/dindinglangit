<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::all();
        $title = 'Supplier';
        return view('pages.supplier.supplier', compact('suppliers', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Supplier';
        return view('pages.supplier.create-supplier', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            Supplier::create($validatedData);

            return redirect()->route('supplier.index')->with('alert', 'Supplier berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menambahkan supplier: \n' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $title = 'Supplier';

        return view('pages.supplier.edit-supplier', compact('supplier', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        try {
            $validatedData = $request->validated();

            $supplier->update($validatedData);

            return redirect()->route('supplier.index')->with('alert', 'Supplier berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat memperbarui supplier: \n' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return redirect()->route('supplier.index')->with('alert', 'Supplier berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('alert', 'Terjadi kesalahan saat menghapus supplier: \n' . $e->getMessage());
        }
    }
}
