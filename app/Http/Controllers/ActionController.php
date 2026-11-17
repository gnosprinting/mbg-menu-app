<?php

namespace App\Http\Controllers;

use App\Services\Siklus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActionController extends Controller
{
    public function storePenerima(Request $r)
    {
        $data = $r->validate([
            'lokasi_id' => 'required|integer|exists:lokasi,id',
            'nama' => 'required|string|max:120',
            'porsi_kecil' => 'nullable|integer|min:0',
            'porsi_besar' => 'nullable|integer|min:0',
            'guru' => 'nullable|integer|min:0',
        ]);

        DB::table('penerima')->insert([
            'lokasi_id' => $data['lokasi_id'],
            'nama' => $data['nama'],
            'jenis' => DB::table('lokasi')->where('id', $data['lokasi_id'])->value('jenis'),
            'porsi_kecil' => $data['porsi_kecil'] ?? 0,
            'porsi_besar' => $data['porsi_besar'] ?? 0,
            'guru' => $data['guru'] ?? 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('ok', 'Penerima manfaat ditambahkan.');
    }

    public function storeBahan(Request $r)
    {
        $data = $r->validate([
            'nama' => 'required|string|max:120',
            'kelompok' => 'required|string',
            'energi' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'lemak' => 'required|numeric|min:0',
            'kh' => 'required|numeric|min:0',
            'natrium' => 'nullable|numeric|min:0',
            'bdd' => 'nullable|integer|min:1|max:100',
            'harga' => 'nullable|integer|min:0',
            'satuan' => 'nullable|string|max:10',
        ]);

        $id = DB::table('bahan')->insertGetId([
            'nama' => $data['nama'], 'kelompok' => $data['kelompok'],
            'energi' => $data['energi'], 'protein' => $data['protein'], 'lemak' => $data['lemak'],
            'kh' => $data['kh'], 'natrium' => $data['natrium'] ?? 0, 'bdd' => $data['bdd'] ?? 100,
            'harga' => $data['harga'] ?? 0, 'satuan' => $data['satuan'] ?? 'kg',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->bukaVersiBahan($id);

        return back()->with('ok', 'Bahan ditambahkan.');
    }

    public function storeMenu(Request $r)
    {
        $maks = Siklus::panjang();
        $data = $r->validate([
            'hari' => 'required|integer|min:1|max:'.$maks,
            'tanggal' => 'nullable|date',
            'nama' => 'required|string|max:120',
            'metode' => 'nullable|string|max:60',
        ]);
        $siklus = Siklus::aktif();
        $tanggal = $data['tanggal'] ?? now()->toDateString();

        $row = DB::table('menu_hari')
            ->where('siklus_id', $siklus->id)->where('hari', $data['hari'])->first();

        if ($row) {
            DB::table('menu_hari')->where('id', $row->id)->update([
                'nama' => $data['nama'],
                'tanggal' => $tanggal,
                'metode' => $data['metode'] ?? null,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('menu_hari')->insert([
                'siklus_id' => $siklus->id,
                'hari' => $data['hari'],
                'tanggal' => $tanggal,
                'nama' => $data['nama'],
                'metode' => $data['metode'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return redirect('/siklus?hari='.Siklus::panjang())->with('ok', 'Menu hari '.$data['hari'].' disimpan.');
    }

    public function storeMenuBahan(Request $r)
    {
        $data = $r->validate([
            'menu_hari_id' => 'required|integer|exists:menu_hari,id',
            'bahan_id' => 'required|integer|exists:bahan,id',
            'gram' => 'nullable|numeric|min:0.1',
            'peran' => 'required|in:pokok,hewani,nabati,sayur,buah,minyak,bumbu',
        ]);

        // Gram ditentukan aplikasi: bila field kosong (mis. JS mati), pakai profil SD 1–3.
        if (empty($data['gram'])) {
            $kolom = $data['peran'] === 'pokok' ? 'nasi' : ($data['peran'] === 'bumbu' ? 'garam' : $data['peran']);
            $sdId = DB::table('kategori')->where('kode', 'SD13')->value('id') ?? DB::table('kategori')->value('id');
            $data['gram'] = (float) (DB::table('profil_porsi')->where('kategori_id', $sdId)->value($kolom) ?? 0) ?: 50;
        }

        // Tautkan ke periode bahan yang sedang berlaku → riwayat menu tidak ikut berubah.
        $data['bahan_versi_id'] = DB::table('bahan_versi')
            ->where('bahan_id', $data['bahan_id'])->whereNull('berlaku_sampai')->value('id');

        // Harga beli dikunci saat bahan dimasukkan (nasi memakai harga beras).
        $bahan = DB::table('bahan')->find($data['bahan_id']);
        $data['harga_beli'] = $bahan && $bahan->nama === 'Nasi putih'
            ? (int) DB::table('bahan')->where('nama', 'Beras putih')->value('harga')
            : (int) ($bahan->harga ?? 0);

        DB::table('menu_bahan')->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        return back()->with('ok', 'Bahan ditambahkan ke menu.');
    }

    public function storeMutasi(Request $r)
    {
        $data = $r->validate([
            'stok_item_id' => 'required|integer|exists:stok_item,id',
            'jenis' => 'required|in:masuk,keluar,opname',
            'qty' => 'required|numeric',
            'keterangan' => 'nullable|string|max:120',
        ]);

        $item = DB::table('stok_item')->find($data['stok_item_id']);
        $qty = (float) $data['qty'];
        $delta = match ($data['jenis']) {
            'masuk' => $qty,
            'keluar' => -abs($qty),
            'opname' => $qty - (float) $item->stok,
        };

        DB::table('stok_mutasi')->insert([
            'stok_item_id' => $item->id, 'tanggal' => now()->toDateString(), 'jenis' => $data['jenis'],
            'qty' => $data['jenis'] === 'opname' ? $delta : $qty,
            'keterangan' => $data['keterangan'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('stok_item')->where('id', $item->id)
            ->update(['stok' => max(0, (float) $item->stok + $delta), 'updated_at' => now()]);

        return back()->with('ok', 'Mutasi stok dicatat.');
    }

    public function storePmAlergi(Request $r)
    {
        $data = $r->validate([
            'lokasi_id' => 'required|integer|exists:lokasi,id',
            'nama' => 'required|string|max:120',
            'kelas' => 'nullable|string|max:20',
            'kategori_id' => 'required|integer|exists:kategori,id',
            'alergen' => 'required|array|min:1',
            'alergen.*' => 'integer|exists:alergen,id',
        ]);

        $id = DB::table('pm_alergi')->insertGetId([
            'lokasi_id' => $data['lokasi_id'], 'nama' => $data['nama'], 'kelas' => $data['kelas'] ?? null,
            'kategori_id' => $data['kategori_id'], 'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ($data['alergen'] as $a) {
            DB::table('alergen_pm')->insert(['pm_alergi_id' => $id, 'alergen_id' => $a]);
        }

        return back()->with('ok', 'PM alergi ditambahkan.');
    }

    public function updatePengaturan(Request $r)
    {
        foreach ($r->except('_token') as $k => $v) {
            DB::table('pengaturan')->updateOrInsert(['kunci' => $k], ['nilai' => $v, 'updated_at' => now(), 'created_at' => now()]);
        }

        return back()->with('ok', 'Pengaturan disimpan.');
    }

    public function updateSiklus(Request $r)
    {
        $pola = (string) $r->input('pola_siklus');
        $panjang = (int) $r->input('panjang_siklus');
        $opsi = $pola === Siklus::POLA_ENAM ? [6, 12] : [5, 10];

        if (! in_array($pola, [Siklus::POLA_LIMA, Siklus::POLA_ENAM], true)
            || ! in_array($panjang, $opsi, true)) {
            return back()->with('galat', 'Pilihan pola siklus tidak valid.');
        }

        Siklus::set($pola, $panjang);

        return back()->with('ok', 'Pola siklus diubah ke '.$pola.' · '.$panjang.' hari.');
    }

    public function storeHaccp(Request $r)
    {
        $data = $r->validate([
            'tanggal' => 'nullable|date',
            'titik_kontrol' => 'required|string|max:120',
            'batas' => 'required|string|max:120',
            'nilai' => 'required|string|max:60',
            'status' => 'required|in:aman,deviasi',
            'tindakan' => 'nullable|string|max:255',
            'client_uuid' => 'nullable|string|max:36',
        ]);

        // Kiriman ulang dari antrian offline tidak boleh menggandakan baris.
        if (! empty($data['client_uuid'])
            && DB::table('haccp_log')->where('client_uuid', $data['client_uuid'])->exists()) {
            return back()->with('ok', 'Checklist HACCP sudah tersimpan sebelumnya.');
        }

        DB::table('haccp_log')->insert([
            'client_uuid' => $data['client_uuid'] ?? null,
            'tanggal' => $data['tanggal'] ?? now()->toDateString(),
            'titik_kontrol' => $data['titik_kontrol'], 'batas' => $data['batas'],
            'nilai' => $data['nilai'], 'status' => $data['status'],
            'tindakan' => $data['tindakan'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('ok', 'Checklist HACCP dicatat.');
    }

    public function tutupDeviasi(Request $r, int $id)
    {
        $data = $r->validate(['tindakan' => 'required|string|max:255']);

        DB::table('haccp_log')->where('id', $id)->update([
            'tindakan' => $data['tindakan'], 'status' => 'aman', 'updated_at' => now(),
        ]);

        return back()->with('ok', 'Deviasi ditutup dengan tindakan korektif.');
    }

    public function storeSisa(Request $r)
    {
        $data = $r->validate([
            'tanggal' => 'nullable|date',
            'kelompok' => 'required|string|max:40',
            'disajikan' => 'required|numeric|min:0',
            'sisa' => 'required|numeric|min:0',
        ]);

        DB::table('sisa_makanan')->updateOrInsert(
            ['tanggal' => $data['tanggal'] ?? now()->toDateString(), 'kelompok' => $data['kelompok']],
            ['disajikan' => $data['disajikan'], 'sisa' => $data['sisa'],
             'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('ok', 'Sisa makanan dicatat.');
    }

    public function storePoManual(Request $r)
    {
        // Buang baris kosong (form menyediakan beberapa baris).
        $rows = collect($r->input('items', []))
            ->filter(fn ($it) => filled($it['nama'] ?? null))->values()->all();
        if (empty($rows)) {
            return back()->with('galat', 'Isi minimal satu item PO (nama, qty, harga).');
        }
        $r->merge(['items' => $rows]);

        $data = $r->validate([
            'catatan' => 'nullable|string|max:160',
            'items' => 'required|array|min:1',
            'items.*.nama' => 'required|string|max:120',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.satuan' => 'nullable|string|max:10',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.stok_item_id' => 'nullable|integer|exists:stok_item,id',
        ]);

        $items = [];
        $total = 0;
        foreach ($data['items'] as $it) {
            $qty = (float) $it['qty'];
            $harga = (int) round($it['harga']);
            $sub = (int) round($qty * $harga);
            $total += $sub;
            $items[] = [
                'stok_item_id' => $it['stok_item_id'] ?? null,
                'nama' => $it['nama'], 'qty' => $qty, 'satuan' => $it['satuan'] ?? 'kg',
                'harga' => $harga, 'subtotal' => $sub,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        $nomor = 'PO-'.now()->format('ymd').'-'.str_pad((string) (DB::table('po')->count() + 1), 3, '0', STR_PAD_LEFT);
        $id = DB::table('po')->insertGetId([
            'nomor' => $nomor, 'tanggal' => now()->toDateString(), 'status' => 'menunggu',
            'total' => $total, 'catatan' => $data['catatan'] ?? 'PO manual',
            'user_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ($items as $it) {
            DB::table('po_item')->insert(['po_id' => $id] + $it);
        }

        return back()->with('ok', 'PO manual '.$nomor.' dibuat: '.count($items).' item, total Rp '.number_format($total, 0, ',', '.'));
    }

    /** Setujui PO (admin & akuntan). */
    public function setujuiPo(int $id)
    {
        $po = DB::table('po')->find($id);
        abort_if(! $po, 404);

        if ($po->status !== 'menunggu') {
            return back()->with('galat', "PO {$po->nomor} berstatus {$po->status}; hanya PO menunggu yang bisa disetujui.");
        }

        DB::table('po')->where('id', $id)->update([
            'status' => 'disetujui',
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('ok', "PO {$po->nomor} disetujui. Menunggu penerimaan barang oleh dapur.");
    }

    /** Tolak PO dengan alasan (admin & akuntan). */
    public function tolakPo(Request $r, int $id)
    {
        $data = $r->validate(['alasan_tolak' => 'required|string|max:160']);
        $po = DB::table('po')->find($id);
        abort_if(! $po, 404);

        if (! in_array($po->status, ['menunggu', 'disetujui'], true)) {
            return back()->with('galat', "PO {$po->nomor} sudah {$po->status} dan tidak bisa ditolak.");
        }

        DB::table('po')->where('id', $id)->update([
            'status' => 'ditolak',
            'alasan_tolak' => $data['alasan_tolak'],
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('ok', "PO {$po->nomor} ditolak: {$data['alasan_tolak']}");
    }

    /** Terima barang PO → stok bertambah + mutasi masuk (admin & dapur). */
    public function terimaPo(int $id)
    {
        $hasil = DB::transaction(function () use ($id) {
            $po = DB::table('po')->lockForUpdate()->find($id);
            abort_if(! $po, 404);

            if ($po->status !== 'disetujui') {
                return ['galat' => "PO {$po->nomor} berstatus {$po->status}; barang hanya bisa diterima setelah disetujui."];
            }

            $items = DB::table('po_item')->where('po_id', $id)->get();
            $masuk = 0;

            foreach ($items as $it) {
                if (! $it->stok_item_id || (float) $it->qty <= 0) {
                    continue;
                }
                $item = DB::table('stok_item')->lockForUpdate()->find($it->stok_item_id);
                if (! $item) {
                    continue;
                }

                DB::table('stok_item')->where('id', $item->id)->update([
                    'stok' => (float) $item->stok + (float) $it->qty,
                    'harga' => (int) $it->harga > 0 ? (int) $it->harga : $item->harga,
                    'updated_at' => now(),
                ]);

                DB::table('stok_mutasi')->insert([
                    'stok_item_id' => $item->id,
                    'tanggal' => now()->toDateString(),
                    'jenis' => 'masuk',
                    'qty' => (float) $it->qty,
                    'keterangan' => 'Penerimaan '.$po->nomor,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $masuk++;
            }

            DB::table('po')->where('id', $id)->update([
                'status' => 'diterima',
                'diterima_oleh' => auth()->id(),
                'diterima_pada' => now(),
                'updated_at' => now(),
            ]);

            return ['ok' => "PO {$po->nomor} diterima: {$masuk} item masuk stok.", 'masuk' => $masuk];
        });

        if (isset($hasil['galat'])) {
            return back()->with('galat', $hasil['galat']);
        }

        return back()->with('ok', $hasil['ok']);
    }

    public function applyHargaAcuan()
    {
        \Illuminate\Support\Facades\Artisan::call('mbg:isi-harga');

        return back()->with('ok', trim(\Illuminate\Support\Facades\Artisan::output()));
    }

    /** Tarik harga pasar terbaru (PIHPS BI) lalu perbarui harga_acuan. */
    public function tarikHargaAcuan()
    {
        \Illuminate\Support\Facades\Artisan::call('mbg:tarik-harga', ['--ringkas' => true]);
        $out = trim(\Illuminate\Support\Facades\Artisan::output());

        return back()->with(str_contains($out, 'Gagal') ? 'galat' : 'ok', $out);
    }

    public function buatPo(Request $r)
    {
        $kurang = DB::table('stok_item')->whereColumn('stok', '<', 'minimum')
            ->where('jenis', 'bahan')->get();

        if ($kurang->isEmpty()) {
            return back()->with('galat', 'Tidak ada bahan di bawah minimum — PO tidak diperlukan.');
        }

        $nomor = 'PO-'.now()->format('ymd').'-'.str_pad((string) (DB::table('po')->count() + 1), 3, '0', STR_PAD_LEFT);
        $total = 0;
        $items = [];

        foreach ($kurang as $k) {
            $qty = max(0, (float) $k->minimum - (float) $k->stok);
            $subtotal = (int) round($qty * $k->harga);
            $total += $subtotal;
            $items[] = [
                'stok_item_id' => $k->id, 'nama' => $k->nama, 'qty' => $qty,
                'satuan' => $k->satuan, 'harga' => $k->harga, 'subtotal' => $subtotal,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        $id = DB::table('po')->insertGetId([
            'nomor' => $nomor, 'tanggal' => now()->toDateString(), 'status' => 'menunggu',
            'total' => $total, 'catatan' => $r->input('catatan'),
            'user_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ($items as $it) {
            DB::table('po_item')->insert($it + ['po_id' => $id]);
        }

        return back()->with('ok', "PO {$nomor} dibuat: {$kurang->count()} item, total Rp ".number_format($total, 0, ',', '.').'.');
    }

    public function storeUser(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'peran' => 'required|in:admin,gizi,dapur,pengawas',
            'lokasi_id' => 'nullable|integer|exists:lokasi,id',
        ]);

        DB::table('users')->insert([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'peran' => $data['peran'], 'lokasi_id' => $data['lokasi_id'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('ok', 'Pengguna ditambahkan.');
    }

    public function storeKategori(Request $r)
    {
        $data = $r->validate([
            'kode' => 'required|string|max:10|unique:kategori,kode',
            'nama' => 'required|string|max:80',
            'akg_energi' => 'required|integer|min:0',
            'akg_protein' => 'required|numeric|min:0',
            'akg_lemak' => 'nullable|numeric|min:0',
            'akg_kh' => 'nullable|numeric|min:0',
            'target_energi' => 'nullable|numeric|min:0',
            'target_protein' => 'nullable|numeric|min:0',
        ]);

        $id = DB::table('kategori')->insertGetId($data + [
            'urutan' => (int) DB::table('kategori')->max('urutan') + 1,
            'khusus' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $base = DB::table('profil_porsi')->join('kategori', 'kategori.id', '=', 'profil_porsi.kategori_id')
            ->where('kategori.kode', 'SD13')->first();
        DB::table('profil_porsi')->insert([
            'kategori_id' => $id, 'nasi' => $base->nasi ?? 145, 'hewani' => $base->hewani ?? 45,
            'nabati' => $base->nabati ?? 35, 'sayur' => $base->sayur ?? 60, 'buah' => $base->buah ?? 70,
            'minyak' => $base->minyak ?? 5, 'garam' => $base->garam ?? 0.8, 'susu' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('ok', 'Kategori ditambahkan.');
    }

    public function updateKategori(Request $r, int $id)
    {
        $data = $r->validate([
            'nama' => 'required|string|max:80',
            'akg_energi' => 'required|integer|min:0',
            'akg_protein' => 'required|numeric|min:0',
            'target_energi' => 'nullable|numeric|min:0',
            'target_protein' => 'nullable|numeric|min:0',
            'nasi' => 'nullable|numeric|min:0', 'hewani' => 'nullable|numeric|min:0',
            'nabati' => 'nullable|numeric|min:0', 'sayur' => 'nullable|numeric|min:0',
            'buah' => 'nullable|numeric|min:0', 'minyak' => 'nullable|numeric|min:0',
            'garam' => 'nullable|numeric|min:0',
        ]);

        DB::table('kategori')->where('id', $id)->update([
            'nama' => $data['nama'], 'akg_energi' => $data['akg_energi'], 'akg_protein' => $data['akg_protein'],
            'target_energi' => $data['target_energi'] ?? null, 'target_protein' => $data['target_protein'] ?? null,
            'updated_at' => now(),
        ]);

        DB::table('profil_porsi')->where('kategori_id', $id)->update([
            'nasi' => $data['nasi'] ?? 0, 'hewani' => $data['hewani'] ?? 0, 'nabati' => $data['nabati'] ?? 0,
            'sayur' => $data['sayur'] ?? 0, 'buah' => $data['buah'] ?? 0, 'minyak' => $data['minyak'] ?? 0,
            'garam' => $data['garam'] ?? 0, 'updated_at' => now(),
        ]);

        return back()->with('ok', 'Kategori diperbarui.');
    }

    public function deleteKategori(int $id)
    {
        $dipakai = DB::table('penerima_kategori')->where('kategori_id', $id)->count()
            + DB::table('pm_alergi')->where('kategori_id', $id)->count();
        if ($dipakai > 0) {
            return back()->with('galat', 'Kategori masih dipakai penerima atau PM alergi — tidak bisa dihapus.');
        }
        DB::table('profil_porsi')->where('kategori_id', $id)->delete();
        DB::table('kategori')->where('id', $id)->delete();

        return back()->with('ok', 'Kategori dihapus.');
    }

    public function storeAlergen(Request $r)
    {
        $data = $r->validate(['nama' => 'required|string|max:40|unique:alergen,nama']);
        DB::table('alergen')->insert($data);

        return back()->with('ok', 'Alergen ditambahkan.');
    }

    public function updateAlergen(Request $r, int $id)
    {
        $data = $r->validate(['nama' => 'required|string|max:40|unique:alergen,nama,'.$id]);
        DB::table('alergen')->where('id', $id)->update($data);

        return back()->with('ok', 'Nama alergen diperbarui.');
    }

    public function deleteAlergen(int $id)
    {
        $dipakai = DB::table('alergen_pm')->where('alergen_id', $id)->count()
            + DB::table('alergen_bahan')->where('alergen_id', $id)->count();
        if ($dipakai > 0) {
            return back()->with('galat', 'Alergen masih terhubung ke PM atau bahan — tidak bisa dihapus.');
        }
        DB::table('alergen')->where('id', $id)->delete();

        return back()->with('ok', 'Alergen dihapus.');
    }

    public function storePenerimaKat(Request $r)
    {
        $data = $r->validate([
            'lokasi_id' => 'required|integer|exists:lokasi,id',
            'kategori_id' => 'required|integer|exists:kategori,id',
            'jumlah' => 'required|integer|min:0',
        ]);

        $this->catatPeriodePenerima((int) $data['lokasi_id'], (int) $data['kategori_id'], (int) $data['jumlah']);

        return back()->with('ok', 'Penerima per kategori disimpan sebagai periode baru.');
    }

    /** Catat jumlah penerima sebagai periode: baris lama ditutup, baris baru dibuka. */
    private function catatPeriodePenerima(int $lokasiId, int $kategoriId, int $jumlah): void
    {
        $aktif = DB::table('penerima_kategori')
            ->where('lokasi_id', $lokasiId)->where('kategori_id', $kategoriId)
            ->whereNull('berlaku_sampai')->first();

        if ($aktif && (int) $aktif->jumlah === $jumlah) {
            return;
        }

        DB::transaction(function () use ($aktif, $lokasiId, $kategoriId, $jumlah) {
            if ($aktif) {
                DB::table('penerima_kategori')->where('id', $aktif->id)
                    ->update(['berlaku_sampai' => now()->subDay()->toDateString(), 'updated_at' => now()]);
            }
            DB::table('penerima_kategori')->insert([
                'lokasi_id' => $lokasiId, 'kategori_id' => $kategoriId, 'jumlah' => $jumlah,
                'berlaku_dari' => now()->toDateString(), 'berlaku_sampai' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function updatePenerimaKat(Request $r, int $id)
    {
        $data = $r->validate(['jumlah' => 'required|integer|min:0']);
        $row = DB::table('penerima_kategori')->find($id);
        abort_if(! $row, 404);

        if ($row->berlaku_sampai === null) {
            $this->catatPeriodePenerima((int) $row->lokasi_id, (int) $row->kategori_id, (int) $data['jumlah']);
        } else {
            DB::table('penerima_kategori')->where('id', $id)->update(['jumlah' => $data['jumlah'], 'updated_at' => now()]);
        }

        return redirect('/master-penerima')->with('ok', 'Jumlah penerima diperbarui — periode lama tetap tersimpan.');
    }

    public function deletePenerimaKat(int $id)
    {
        $row = DB::table('penerima_kategori')->find($id);
        abort_if(! $row, 404);
        $pm = DB::table('pm_alergi')->where('lokasi_id', $row->lokasi_id)->where('kategori_id', $row->kategori_id)->count();
        if ($pm > 0) {
            return back()->with('galat', 'Penerima ini punya PM alergi terkait — hapus PM alergi dulu.');
        }
        DB::table('penerima_kategori')
            ->where('lokasi_id', $row->lokasi_id)->where('kategori_id', $row->kategori_id)->delete();

        return back()->with('ok', 'Penerima dihapus (termasuk riwayat periodenya).');
    }

    /** Buka periode baru untuk sebuah bahan memakai nilai master yang berlaku sekarang. */
    private function bukaVersiBahan(int $bahanId, ?string $dari = null): void
    {
        $b = DB::table('bahan')->find($bahanId);
        if (! $b) {
            return;
        }

        DB::table('bahan_versi')->insert([
            'bahan_id' => $b->id, 'berlaku_dari' => $dari ?? now()->toDateString(), 'berlaku_sampai' => null,
            'energi' => $b->energi, 'protein' => $b->protein, 'lemak' => $b->lemak, 'kh' => $b->kh,
            'natrium' => $b->natrium, 'serat' => $b->serat, 'vit_a' => $b->vit_a ?? 0, 'vit_b' => $b->vit_b ?? 0,
            'bdd' => $b->bdd, 'harga' => $b->harga, 'satuan' => $b->satuan,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function updateBahan(Request $r, int $id)
    {
        $data = $r->validate([
            'nama' => 'required|string|max:120',
            'kelompok' => 'required|string|max:30',
            'energi' => 'required|numeric|min:0', 'protein' => 'required|numeric|min:0',
            'lemak' => 'required|numeric|min:0', 'kh' => 'required|numeric|min:0',
            'serat' => 'nullable|numeric|min:0', 'vit_a' => 'nullable|numeric|min:0',
            'vit_b' => 'nullable|numeric|min:0', 'natrium' => 'nullable|numeric|min:0',
            'bdd' => 'required|integer|min:1|max:100', 'harga' => 'nullable|integer|min:0',
            'satuan' => 'nullable|string|max:10', 'sp_label' => 'nullable|string|max:20',
            'urt_label' => 'nullable|string|max:40',
        ]);

        $lama = DB::table('bahan')->find($id);
        abort_if(! $lama, 404);

        // Hanya nilai gizi/harga/BDD yang dianggap perubahan periode. Nama & label tidak.
        $berubah = false;
        foreach (['energi', 'protein', 'lemak', 'kh', 'natrium', 'serat', 'vit_a', 'vit_b', 'bdd', 'harga'] as $k) {
            if (abs((float) ($data[$k] ?? 0) - (float) ($lama->$k ?? 0)) > 0.0001) {
                $berubah = true;
                break;
            }
        }
        if (($data['satuan'] ?? 'kg') !== $lama->satuan) {
            $berubah = true;
        }

        DB::transaction(function () use ($data, $id, $berubah) {
            DB::table('bahan')->where('id', $id)->update($data + ['updated_at' => now()]);

            if ($berubah) {
                // Tutup periode lama per kemarin, buka periode baru hari ini.
                DB::table('bahan_versi')->where('bahan_id', $id)->whereNull('berlaku_sampai')
                    ->update(['berlaku_sampai' => now()->subDay()->toDateString(), 'updated_at' => now()]);
                $this->bukaVersiBahan($id);
            }
        });

        return back()->with('ok', $berubah
            ? 'Bahan diperbarui. Periode baru dibuka — menu lama tetap memakai nilai periode sebelumnya.'
            : 'Bahan diperbarui (nilai gizi & harga tidak berubah, periode tetap).');
    }

    public function deleteBahan(int $id)
    {
        $menu = DB::table('menu_bahan')->where('bahan_id', $id)->count();
        $alergen = DB::table('alergen_bahan')->where('bahan_id', $id)->count();
        $nama = DB::table('bahan')->where('id', $id)->value('nama');

        // Beras putih dipakai perhitungan nasi→beras walau tidak punya baris menu.
        if ($nama === 'Beras putih') {
            return back()->with('galat', 'Bahan acuan nasi→beras tidak bisa dihapus.');
        }

        if ($menu + $alergen > 0) {
            return back()->with('galat', 'Bahan tidak bisa dihapus: dipakai di '.$menu.' baris menu dan '.$alergen.' relasi alergen.');
        }

        DB::transaction(function () use ($id) {
            DB::table('bahan_versi')->where('bahan_id', $id)->delete();
            DB::table('bahan')->where('id', $id)->delete();
        });

        return back()->with('ok', 'Bahan dihapus.');
    }

    public function updateMenu(Request $r, int $id)
    {
        $data = $r->validate([
            'nama' => 'required|string|max:120',
            'tanggal' => 'nullable|date',
            'metode' => 'nullable|string|max:60',
        ]);

        DB::table('menu_hari')->where('id', $id)->update($data + ['updated_at' => now()]);

        return back()->with('ok', 'Menu diperbarui.');
    }

    public function deleteMenu(int $id)
    {
        DB::table('menu_bahan')->where('menu_hari_id', $id)->delete();
        DB::table('menu_hari')->where('id', $id)->delete();

        return redirect('/siklus')->with('ok', 'Menu dihapus.');
    }

    public function deleteMenuBahan(int $id)
    {
        DB::table('menu_bahan')->where('id', $id)->delete();

        return back()->with('ok', 'Bahan dikeluarkan dari menu.');
    }

    public function updateUser(Request $r, int $id)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:users,email,'.$id,
            'peran' => 'required|in:admin,gizi,dapur,pengawas',
            'lokasi_id' => 'nullable|integer|exists:lokasi,id',
            'aktif' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $upd = [
            'name' => $data['name'], 'email' => $data['email'], 'peran' => $data['peran'],
            'lokasi_id' => $data['lokasi_id'] ?? null, 'aktif' => (bool) ($data['aktif'] ?? false),
            'updated_at' => now(),
        ];
        if (! empty($data['password'])) {
            $upd['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        DB::table('users')->where('id', $id)->update($upd);

        return redirect('/pengguna')->with('ok', 'Pengguna diperbarui.');
    }

    public function toggleUser(int $id)
    {
        $u = DB::table('users')->find($id);
        abort_if(! $u, 404);
        if ($u->id === auth()->id()) {
            return back()->with('galat', 'Tidak bisa menonaktifkan akun sendiri.');
        }
        DB::table('users')->where('id', $id)->update(['aktif' => ! $u->aktif, 'updated_at' => now()]);

        return back()->with('ok', 'Status pengguna diubah.');
    }

    public function uploadLogo(Request $r)
    {
        $r->validate(['logo' => 'required|image|max:2048']);
        $path = $r->file('logo')->store('logo', 'public');
        DB::table('pengaturan')->updateOrInsert(['kunci' => 'logo'], ['nilai' => $path, 'updated_at' => now(), 'created_at' => now()]);

        return back()->with('ok', 'Logo diperbarui.');
    }
}
