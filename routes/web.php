<?php

// Stok Minimal
use App\Http\Controllers\StokMinimalController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotaBeliController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StokDapurController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\ShiftKasirController;
use App\Http\Controllers\StokGudangController;
use App\Http\Controllers\KategoriMenuController;
use App\Http\Controllers\BahanBakuMenuController;
use App\Http\Controllers\GrupBahanBakuController;
use App\Http\Controllers\BahanBakuNotaBeliController;
use App\Http\Controllers\KategoriBahanBakuController;
use App\Http\Controllers\PenggunaanBahanBakuController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthController::class, 'index'])->name('/');

Route::post('/signin', [AuthController::class, 'signIn']);
Route::post('/signup', [AuthController::class, 'signUp']);
Route::post('/signout', [AuthController::class, 'signOut']);
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/wma-prediksi', [PenggunaanBahanBakuController::class, 'prediksi'])->name('wma.prediksi');
Route::get('/wma-prediksi/export', [PenggunaanBahanBakuController::class, 'exportWmaPrediksi'])->name('wma_prediksi.export');
Route::get('/stok-opname/export', [StokGudangController::class, 'exportStokOpname'])->name('stok_opname.export');

// Invoice
Route::get('/cetakinvoice/{id}', [InvoiceController::class, 'cetakInvoice'])->name('cetakinvoice');

// Route untuk owner
Route::middleware(['auth', 'cekrole:owner'])->group(function () {
    Route::resource('/user', UserController::class);
    Route::get('/laporan-penggunaan-bahan-baku', [LaporanController::class, 'penggunaanBahanBaku']);
});

// Route untuk admin_gudang dan owner
Route::middleware(['auth', 'cekrole:admin_gudang,owner'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::get('/laporan-pembelian-bahan-baku', [LaporanController::class, 'pembelianBahanBaku']);
    Route::get('/laporan-pengiriman-bahan-baku', [LaporanController::class, 'pengirimanBahanBaku']);
    Route::get('/laporan-penjualan', [LaporanController::class, 'penjualan']);
    Route::resource('/notabeli', BahanBakuNotaBeliController::class);
});

// Route untuk admin_gudang
Route::middleware(['auth', 'cekrole:admin_gudang'])->group(function () {
    Route::resource('/kategoribahanbaku', KategoriBahanBakuController::class);
    Route::resource('/supplier', SupplierController::class);
    Route::resource('/stokgudang', StokGudangController::class);
    Route::resource('/satuan', SatuanController::class);
    Route::resource('/bahanbaku', BahanBakuController::class);
    Route::resource('/grupbahanbaku', GrupBahanBakuController::class);
    Route::patch('/grupbahanbaku/{grupbahanbaku}/toggle-pengajuan', [GrupBahanBakuController::class, 'togglePengajuan'])->name('grupbahanbaku.toggle-pengajuan');
    
    // Stok Minimal (khusus admin gudang)
    Route::get('/stok-minimal', [StokMinimalController::class, 'index'])->name('stok-minimal.index');
    Route::get('/stok-minimal/create', [StokMinimalController::class, 'create'])->name('stok-minimal.create');
    Route::post('/stok-minimal', [StokMinimalController::class, 'store'])->name('stok-minimal.store');
    Route::get('/stok-minimal/{stokMinimal}/edit', [StokMinimalController::class, 'edit'])->name('stok-minimal.edit');
    Route::put('/stok-minimal/{stokMinimal}', [StokMinimalController::class, 'update'])->name('stok-minimal.update');
    Route::delete('/stok-minimal/{stokMinimal}', [StokMinimalController::class, 'destroy'])->name('stok-minimal.destroy');
});

// Route untuk admin_gudang dan admin_dapur
Route::middleware(['auth', 'cekrole:admin_gudang,admin_dapur'])->group(function () {
    Route::get('/restokdapur/{id}', [StokDapurController::class, 'restokDapur']);
    Route::post('/tambah-stok-dapur/{id}', [StokDapurController::class, 'tambahStokDapur']);
    Route::resource('/stokdapur', StokDapurController::class);
    Route::get('/stokopname', [StokGudangController::class, 'stokOpname']);
});

// Route untuk admin_dapur
Route::middleware(['auth', 'cekrole:admin_dapur'])->group(function () {
    Route::resource('/kategorimenu', KategoriMenuController::class);
    Route::resource('/penggunaanbahanbaku', PenggunaanBahanBakuController::class);
    Route::resource('/resep', BahanBakuMenuController::class);
    Route::get('/resep/{menuId}/edit-menu', [BahanBakuMenuController::class, 'editMenu'])->name('resep.edit_menu');
    Route::put('/resep/{menuId}/update-menu', [BahanBakuMenuController::class, 'updateMenu'])->name('resep.update_menu');
    Route::delete('/resep/{menuId}/destroy-menu', [BahanBakuMenuController::class, 'destroyMenu'])->name('resep.destroy_menu');
});

// Route untuk admin_dapur dan kasir
Route::middleware(['auth', 'cekrole:admin_dapur,kasir'])->group(function () {
    Route::get('/ajukandapur/{id}', [StokDapurController::class, 'ajukanDapur'])->name('ajukandapur');
    Route::resource('/menu', MenuController::class);
});

// Route untuk kasir dan owner
Route::middleware(['auth', 'cekrole:kasir,owner'])->group(function () {
    // Place bulk and status routes BEFORE the resource to avoid being captured by resource wildcard
    Route::patch('/transaksi/bulk-update-status', [TransaksiController::class, 'bulkUpdateStatus'])->name('transaksi.bulk_update_status');
    Route::patch('/transaksi/{transaksi}/status', [TransaksiController::class, 'updateStatus'])->name('transaksi.update_status');
    Route::resource('/transaksi', TransaksiController::class);
});

// Route untuk kasir
Route::middleware(['auth', 'cekrole:kasir'])->group(function () {
    Route::resource('/shiftkasir', ShiftKasirController::class);
    Route::post('/saldo-awal-kasir', [ShiftKasirController::class, 'saldoAwal']);
    Route::post('/saldo-akhir-kasir', [ShiftKasirController::class, 'saldoAkhir']);
    Route::post('/setoruang', [ShiftKasirController::class, 'setorUang']);
});
