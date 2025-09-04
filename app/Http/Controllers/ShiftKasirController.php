<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaksi;
use App\Models\ShiftKasir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreShiftKasirRequest;
use App\Http\Requests\UpdateShiftKasirRequest;

class ShiftKasirController extends Controller
{
    public function setorUang(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'total_pembayaran' => 'required|numeric|min:0',
                'metode_pembayaran' => 'required|string|in:QRIS,Cash' // tambahkan validasi metode
            ]);

            $today = Carbon::today();

            $lastShift = ShiftKasir::where('user_id', Auth::id())
                        ->whereDate('created_at', $today)
                        ->whereNull('jam_keluar')
                        ->latest()
                        ->firstOrFail(); // gunakan firstOrFail()

            // 1. Hitung saldo tersedia untuk metode pembayaran cash
            $transaksiMasuk = Transaksi::whereDate('created_at', $today)
                ->where('tipe', 'transaksi')
                ->where('metode_pembayaran', 'Cash')
                ->select('metode_pembayaran', DB::raw('SUM(total_pembayaran) as total'))
                ->groupBy('metode_pembayaran')
                ->first();

            $cashSetor = Transaksi::select('metode_pembayaran', DB::raw('SUM(total_pembayaran) as total'))
                ->whereDate('created_at', $today)
                ->where('tipe', 'setor')
                ->where('metode_pembayaran', 'Cash')
                ->groupBy('metode_pembayaran')
                ->first();

            // dd($cashSetor);
            $saldoCash = $transaksiMasuk->total - ($cashSetor->total ?? 0);

            // 2. Validasi tidak boleh setor melebihi saldo
            if ($request->metode_pembayaran == 'Cash' && $request->total_pembayaran > $saldoCash) {
                return back()->with('alert', 'Saldo '.$request->metode_pembayaran.' tidak mencukupi. '.'Saldo tersedia: '. number_format($saldoCash, 0))->withInput();
            }

            // 3. Jika validasi lolos, buat transaksi setor
            Transaksi::create([
                'total_pembayaran' => $request->total_pembayaran,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tipe' => 'setor',
                'shift_kasir_id' => $lastShift->id,
                'user_id' => Auth::id(),
                'created_at' => now() // tambahkan timestamp eksplisit
            ]);

            DB::commit();

            return back()->with('alert', 'Setoran berhasil dicatat! Jumlah setor: ' . number_format($request->total_pembayaran, 0));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('alert', $e->getMessage())->withInput();
        }
    }

    public function saldoAwal(Request $request)
    {
        $lastShift = ShiftKasir::where('user_id', $request->user_id)
                        ->whereNull('jam_keluar')
                        ->latest()
                        ->first();
        if ($lastShift) {
            $lastShift->update([
                'saldo_awal' => $request->saldo_awal,
            ]);

            return back()->with('alert', 'Saldo awal berhasil disimpan!');
        }

        return back()->with('alert', 'Saldo awal gagal disimpan!');
    }

    public function saldoAkhir(Request $request)
    {
        $lastShift = ShiftKasir::where('user_id', $request->user_id)
                        ->whereNull('jam_keluar')
                        ->latest()
                        ->first();
        if ($lastShift) {
            $lastShift->update([
                'saldo_akhir' => $request->saldo_akhir,
            ]);

            return back()->with('alert', 'Saldo akhir berhasil disimpan!');
        }

        return back()->with('alert', 'Saldo akhir gagal disimpan!');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shiftKasirs = ShiftKasir::where('user_id', Auth::user()->id)->get();
        $title = 'Shift';
        return view('pages.shiftkasir.shiftkasir', compact('shiftKasirs', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Shift';
        return view('pages.shiftkasir.create-shiftkasir', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShiftKasirRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ShiftKasir $shiftKasir)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShiftKasir $shiftKasir)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShiftKasirRequest $request, ShiftKasir $shiftKasir)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShiftKasir $shiftKasir)
    {
        //
    }
}
