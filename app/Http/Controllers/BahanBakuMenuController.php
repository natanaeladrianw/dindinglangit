<?php

namespace App\Http\Controllers;

use App\Models\GrupBahanBaku;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;          // Model Menu
use App\Models\Satuan;        // Model Satuan
use App\Models\BahanBaku;     // Model BahanBaku
use App\Models\BahanBakuMenu; // Menggunakan model pivot eksplisit Anda
use Illuminate\Http\Request;  // Gunakan Request standar untuk validasi awal jika belum ada Form Request

// Jika Anda sudah membuat StoreBahanBakuMenuRequest dan UpdateBahanBakuMenuRequest
// use App\Http\Requests\StoreBahanBakuMenuRequest;
// use App\Http\Requests\UpdateBahanBakuMenuRequest;

class BahanBakuMenuController extends Controller
{
    /**
     * Display a listing of the resource (daftar resep).
     * Akan menampilkan semua entri di tabel bahan_baku_menus.
     */
    public function index()
    {
        // Ambil semua resep dengan eager loading relasi terkait
        $reseps = BahanBakuMenu::with('bahanBakus', 'menus', 'satuans')->get(); // Contoh pagination
        // dd($reseps);
        $groupedReseps = $reseps->groupBy('menu_id');
        $menus = Menu::all();

        $title = 'Resep'; // Judul halaman

        return view('pages.resep.resep', compact('menus', 'groupedReseps', 'title'));
    }

    /**
     * Show the form for creating a new resource (tambah resep).
     * Akan menampilkan form yang Anda berikan di blade.
     */
    public function create()
    {
        // Ambil data BahanBaku dan Satuan yang dibutuhkan untuk dropdown di form
        $bahanBakus = GrupBahanBaku::with('bahanBakus')->get(); // untuk ditampilkan
        $menus = Menu::all();           // Semua menu
        $satuans = Satuan::all();       // Semua satuan
        $title = 'Resep'; // Judul halaman

        return view('pages.resep.create-resep', compact('bahanBakus', 'menus', 'satuans', 'title'));
    }

    /**
     * Store a newly created resource in storage (menyimpan resep baru).
     * Menggunakan Request standar, Anda bisa menggantinya dengan StoreBahanBakuMenuRequest.
     */
    public function store(Request $request)
    {
        // Validasi dasar
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'grup_bahan_baku_ids' => 'required|array',
            'grup_bahan_baku_ids.*' => 'exists:grup_bahan_bakus,id',
            'jml_bahans' => 'required|array',
            'jml_bahans.*' => 'numeric|min:0',
            'satuan_ids' => 'required|array',
            'satuan_ids.*' => 'exists:satuans,id',
        ]);

        foreach ($request->grup_bahan_baku_ids as $grupBahanBakuId) {
            // Ambil jumlah & satuan yang sesuai dari input
            $jumlah = $request->jml_bahans[$grupBahanBakuId] ?? null;
            $satuanId = $request->satuan_ids[$grupBahanBakuId] ?? null;

            // Pastikan data jumlah & satuan tersedia
            if ($jumlah !== null && $satuanId !== null) {
                BahanBakuMenu::create([
                    'menu_id' => $request->menu_id,
                    'grup_bahan_baku_id' => $grupBahanBakuId,
                    'satuan_id' => $satuanId,
                    'jml_bahan' => (float) $jumlah,
                ]);
            }
        }

        return redirect()->route('resep.index')->with('success', 'Resep berhasil ditambahkan!');
    }

    /**
     * Display the specified resource (menampilkan detail resep).
     * Parameter $resep akan di-bind secara otomatis ke model BahanBakuMenu.
     */
    public function show(BahanBakuMenu $resep)
    {
        //
    }

    /**
     * Show the form for editing the specified resource (mengedit resep).
     * Akan menampilkan form dengan data resep yang sudah ada.
     */
    // public function edit(BahanBakuMenu $resep)
    // {
    //     // Ambil data BahanBaku, Menu, dan Satuan untuk dropdown
    //     $bahanBakus = GrupBahanBaku::with('bahanBakus')->get(); // untuk ditampilkan
    //     $menus = Menu::all();           // Semua menu
    //     $satuans = Satuan::all();       // Semua satuan
    //     $title = 'Resep'; // Judul halaman

    //     return view('pages.resep.edit-resep', compact('resep', 'bahanBakus', 'menus', 'satuans', 'title'));
    // }

    public function editMenu($menuId)
    {
        $menu = Menu::with('bahanBakuMenus.grupBahanBaku', 'bahanBakuMenus.satuans')->findOrFail($menuId);
        $bahanBakus = GrupBahanBaku::all(); // ambil berdasarkan grup
        $satuans = Satuan::all();
        $title = 'Edit Resep';

        return view('pages.resep.edit-resep', compact('menu', 'bahanBakus', 'satuans', 'title'));
    }

    /**
     * Update the specified resource in storage (memperbarui resep).
     * Menggunakan Request standar, Anda bisa menggantinya dengan UpdateBahanBakuMenuRequest.
     */
    public function updateMenu(Request $request, $menuId)
    {
        // dd($request);
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'grup_bahan_baku_ids' => 'array',
            'grup_bahan_baku_ids.*' => 'exists:grup_bahan_bakus,id',
            'jml_bahans' => 'array',
            'jml_bahans.*' => 'required|numeric|min:0',
            'satuan_ids' => 'array',
            'satuan_ids.*' => 'required|exists:satuans,id',
        ]);

        $menu = Menu::findOrFail($menuId);

        DB::beginTransaction();
        try {
            // Hapus semua bahan baku terkait resep menu ini
            BahanBakuMenu::where('menu_id', $menu->id)->delete();

            // Simpan entri baru
            foreach ($request->input('grup_bahan_baku_ids', []) as $grupId) {
                $jumlah = $request->input("jml_bahans.$grupId");
                $satuanId = $request->input("satuan_ids.$grupId");

                if (isset($jumlah) && isset($satuanId)) {
                    BahanBakuMenu::create([
                        'menu_id' => $menu->id,
                        'grup_bahan_baku_id' => $grupId,
                        'jml_bahan' => $jumlah,
                        'satuan_id' => $satuanId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('resep.index')->with('success', 'Resep menu berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal update resep: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat update resep: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (menghapus resep).
     */
    public function destroyMenu($menuId)
    {
        // 1. Temukan Menu berdasarkan ID
        // Menggunakan findOrFail akan otomatis melempar 404 jika menu tidak ditemukan.
        $menu = Menu::findOrFail($menuId);

        // 2. Mulai transaksi database
        DB::beginTransaction();

        try {
            // 3. Hapus semua entri BahanBakuMenu yang memiliki menu_id ini
            BahanBakuMenu::where('menu_id', $menu->id)->delete();

            // 4. Komit transaksi jika berhasil
            DB::commit();

            // 5. Redirect dengan pesan sukses
            return redirect()->route('resep.index')->with('success', 'Semua resep untuk menu "' . $menu->nama_item . '" berhasil dihapus!');

        } catch (\Exception $e) {
            // 6. Rollback transaksi jika terjadi kesalahan
            DB::rollBack();

            // 7. Catat error untuk debugging
            Log::error('Gagal menghapus resep menu ' . $menu->id . ': ' . $e->getMessage());

            // 8. Redirect kembali dengan pesan error
            return redirect()->back()->with('error', 'Gagal menghapus resep. Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
