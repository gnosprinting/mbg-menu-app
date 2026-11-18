<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'form'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', [PageController::class, 'beranda'])->name('beranda');
    Route::get('/siklus', [PageController::class, 'siklus'])->name('siklus');
    Route::get('/siklus/{id}', [PageController::class, 'siklusDetail'])->name('siklus.detail');
    Route::get('/komposisi', [PageController::class, 'komposisi'])->name('komposisi');
    Route::get('/capaian', [PageController::class, 'capaian'])->name('capaian');
    Route::get('/kebutuhan', [PageController::class, 'kebutuhan'])->name('kebutuhan');
    Route::get('/alergi', [PageController::class, 'alergi'])->name('alergi');
    Route::get('/varian', [PageController::class, 'varian'])->name('varian');
    Route::get('/stok', [PageController::class, 'stok'])->name('stok');
    Route::get('/haccp', [PageController::class, 'haccp'])->name('haccp');
    Route::get('/sisa', [PageController::class, 'sisa'])->name('sisa');
    Route::get('/master-penerima', [PageController::class, 'masterPenerima'])->name('master-penerima');
    Route::get('/master-penerima/{id}', [PageController::class, 'penerimaDetail'])->name('penerima.detail');
    Route::get('/master-bahan', [PageController::class, 'masterBahan'])->name('master-bahan');
    Route::get('/master-bahan/{id}', [PageController::class, 'bahanDetail'])->name('bahan.detail');
    Route::get('/master-kategori', [PageController::class, 'masterKategori'])->name('master-kategori');
    Route::get('/master-alergen', [PageController::class, 'masterAlergen'])->name('master-alergen');
    Route::get('/sumber', [PageController::class, 'sumber'])->name('sumber');
    Route::get('/laporan', [PageController::class, 'laporan'])->name('laporan');
    Route::get('/pengguna', [PageController::class, 'pengguna'])->middleware('peran:admin')->name('pengguna');
    Route::get('/pengguna/{id}', [PageController::class, 'penggunaDetail'])->middleware('peran:admin')->name('pengguna.detail');
    Route::get('/pengaturan', [PageController::class, 'pengaturan'])->name('pengaturan');
    Route::get('/audit', [PageController::class, 'audit'])->middleware('peran:admin,akuntan')->name('audit');

    // ekspor nyata (PDF & Excel dari database)
    Route::get('/export/komposisi.pdf', [ExportController::class, 'komposisiPdf'])->name('export.komposisi.pdf');
    Route::get('/export/komposisi.xlsx', [ExportController::class, 'komposisiXlsx'])->name('export.komposisi.xlsx');
    Route::get('/export/capaian.pdf', [ExportController::class, 'capaianPdf'])->name('export.capaian.pdf');
    Route::get('/export/kebutuhan.pdf', [ExportController::class, 'kebutuhanPdf'])->name('export.kebutuhan.pdf');
    Route::get('/export/kebutuhan.xlsx', [ExportController::class, 'kebutuhanXlsx'])->name('export.kebutuhan.xlsx');
    Route::get('/export/alergi.pdf', [ExportController::class, 'alergiPdf'])->name('export.alergi.pdf');
    Route::get('/export/alergi.xlsx', [ExportController::class, 'alergiXlsx'])->name('export.alergi.xlsx');

    // tulis menu & gizi
    Route::middleware('peran:admin,gizi')->group(function () {
        Route::post('/penerima', [ActionController::class, 'storePenerima'])->name('penerima.store');
        Route::post('/bahan', [ActionController::class, 'storeBahan'])->name('bahan.store');
        Route::post('/menu', [ActionController::class, 'storeMenu'])->name('menu.store');
        Route::post('/menu-bahan', [ActionController::class, 'storeMenuBahan'])->name('menu-bahan.store');
        Route::post('/menu-tambahan-3b', [ActionController::class, 'storeTambahan3b'])->name('menu-tambahan.store');
        Route::post('/menu-tambahan-3b/{id}/hapus', [ActionController::class, 'deleteTambahan3b'])->name('menu-tambahan.delete');
        Route::post('/pm-alergi', [ActionController::class, 'storePmAlergi'])->name('pm.store');
        Route::post('/pengaturan', [ActionController::class, 'updatePengaturan'])->name('pengaturan.update');
        Route::post('/pengaturan/siklus', [ActionController::class, 'updateSiklus'])->name('pengaturan.siklus');
        Route::post('/bahan/{id}', [ActionController::class, 'updateBahan'])->name('bahan.update');
        Route::post('/bahan/{id}/hapus', [ActionController::class, 'deleteBahan'])->name('bahan.delete');
        Route::post('/menu/{id}', [ActionController::class, 'updateMenu'])->name('menu.update');
        Route::post('/menu/{id}/hapus', [ActionController::class, 'deleteMenu'])->name('menu.delete');
        Route::post('/menu-bahan/{id}/hapus', [ActionController::class, 'deleteMenuBahan'])->name('menu-bahan.delete');
        Route::post('/penerima-kategori', [ActionController::class, 'storePenerimaKat'])->name('penerima-kat.store');
        Route::post('/penerima-kategori/{id}', [ActionController::class, 'updatePenerimaKat'])->name('penerima-kat.update');
        Route::post('/penerima-kategori/{id}/hapus', [ActionController::class, 'deletePenerimaKat'])->name('penerima-kat.delete');
        Route::post('/kategori', [ActionController::class, 'storeKategori'])->name('kategori.store');
        Route::post('/kategori/{id}', [ActionController::class, 'updateKategori'])->name('kategori.update');
        Route::post('/kategori/{id}/hapus', [ActionController::class, 'deleteKategori'])->name('kategori.delete');
        Route::post('/alergen', [ActionController::class, 'storeAlergen'])->name('alergen.store');
        Route::post('/alergen/{id}', [ActionController::class, 'updateAlergen'])->name('alergen.update');
        Route::post('/alergen/{id}/hapus', [ActionController::class, 'deleteAlergen'])->name('alergen.delete');
        Route::post('/pengaturan/logo', [ActionController::class, 'uploadLogo'])->name('pengaturan.logo');
    });

    // tulis operasional
    Route::middleware('peran:admin,gizi,dapur,akuntan')->group(function () {
        Route::post('/stok/mutasi', [ActionController::class, 'storeMutasi'])->name('stok.mutasi');
        Route::post('/haccp', [ActionController::class, 'storeHaccp'])->name('haccp.store');
        Route::post('/haccp/{log}/tutup', [ActionController::class, 'tutupDeviasi'])->name('haccp.tutup');
        Route::post('/sisa', [ActionController::class, 'storeSisa'])->name('sisa.store');
        Route::post('/po', [ActionController::class, 'buatPo'])->name('po.store');
        Route::post('/po/manual', [ActionController::class, 'storePoManual'])->name('po.manual');
        Route::post('/harga-acuan/apply', [ActionController::class, 'applyHargaAcuan'])->name('harga.acuan.apply');
        Route::post('/harga-acuan/tarik', [ActionController::class, 'tarikHargaAcuan'])->name('harga.acuan.tarik');
    });


    // persetujuan & penerimaan PO
    Route::middleware('peran:admin,akuntan')->group(function () {
        Route::post('/po/{id}/setujui', [ActionController::class, 'setujuiPo'])->name('po.setujui');
        Route::post('/po/{id}/tolak', [ActionController::class, 'tolakPo'])->name('po.tolak');
    });
    Route::middleware('peran:admin,dapur')->group(function () {
        Route::post('/po/{id}/terima', [ActionController::class, 'terimaPo'])->name('po.terima');
    });

    Route::middleware('peran:admin')->group(function () {
        Route::post('/pengguna', [ActionController::class, 'storeUser'])->name('pengguna.store');
        Route::post('/pengguna/{id}', [ActionController::class, 'updateUser'])->name('pengguna.update');
        Route::post('/pengguna/{id}/toggle', [ActionController::class, 'toggleUser'])->name('pengguna.toggle');
    });
});
