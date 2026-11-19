<?php

namespace App\Http\Controllers;

use App\Services\Belanja;
use App\Services\Nutrition;
use App\Services\Siklus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * PageController — semua endpoint baca (GET).
 *
 * Controller ini tidak mengubah data. Tugasnya mengambil data dari database,
 * memakai Nutrition/Belanja/Siklus untuk perhitungan, lalu mengirim hasilnya
 * ke Blade. Query yang berat (Beranda, komposisi) memakai cache.
 */
class PageController extends Controller
{
    /** Ambil siklus yang sedang dipakai sesuai pengaturan. */
    private function siklusAktif()
    {
        return Siklus::aktif();
    }

    /** Daftar kategori penerima manfaat, diurutkan sesuai tampilan. */
    private function kategoriList()
    {
        return DB::table('kategori')->orderBy('urutan')->get();
    }

    /** Nama menu untuk satu hari pada siklus tertentu. */
    private function menuNama(int $hari, ?int $siklusId = null): string
    {
        return DB::table('menu_hari')->where('siklus_id', $siklusId ?? $this->siklusAktif()->id)
            ->where('hari', $hari)->value('nama') ?? '—';
    }

    /** Profil gram per kategori, dipakai untuk gramasi otomatis di form menu. */
    private function profiles(): array
    {
        $out = [];
        foreach (DB::table('profil_porsi')->get() as $p) {
            $out[$p->kategori_id] = [
                'pokok' => (float) $p->nasi, 'hewani' => (float) $p->hewani, 'nabati' => (float) $p->nabati,
                'sayur' => (float) $p->sayur, 'buah' => (float) $p->buah, 'minyak' => (float) $p->minyak,
                'bumbu' => (float) $p->garam,
            ];
        }

        return $out;
    }

    /** Ringkasan dashboard: menu, gizi, belanja, stok kritis, dan penerima; di-cache 10 menit. */
    public function beranda()
    {
        $siklus = $this->siklusAktif();
        $hari = 3;
        $v = (int) Cache::get('mbg.cache_version', 1);
        $key = "beranda:v{$v}:s{$siklus->id}:h{$hari}";

        // Simpan sebagai JSON, bukan serialisasi objek: aman dari incomplete class
        // saat cache dibaca oleh proses lain.
        $raw = Cache::get($key);
        $data = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

        if (empty($data['menu'])) {
            $data = (function () use ($siklus, $hari) {
            $penerima = Belanja::totalPenerima();
            $pagu = (int) DB::table('lokasi')->where('aktif', true)->sum('pagu_harian');
            $belanja = Belanja::perKategoriHariIni($hari, $siklus->id);
            $sd = DB::table('kategori')->where('kode', 'SD13')->first();

            // Hanya hari dalam siklus aktif. Menu uji beban (hari >= 100) tidak ikut dihitung.
            $menu = DB::table('menu_hari')->where('siklus_id', $siklus->id)->where('hari', '<=', Siklus::panjang())
                ->orderBy('hari')->get()
                ->map(function ($m) use ($sd, $siklus) {
                    $k = Nutrition::komposisi($sd->id, $m->hari, $siklus->id);
                    $m->energi = $k['total']['energi'];
                    $m->capaian = $k['pct']['energi'];
                    $m->jumlah_bahan = DB::table('menu_bahan')->where('menu_hari_id', $m->id)->count();

                    return $m;
                });

            $stokKritis = DB::table('stok_item')->whereColumn('stok', '<', 'minimum')->orderBy('stok')->limit(4)->get();
            $pmTerpicu = $this->deteksiAlergi($hari);
            $avg = collect($belanja['rows'])->avg('capaian') ?? 0;

            $c3 = Nutrition::komposisi($sd->id, $hari, $siklus->id);
            $e3 = max($c3['total']['energi'], 1);
            $dist = [
                'protein' => $c3['total']['protein'] * 4 / $e3 * 100,
                'lemak' => $c3['total']['lemak'] * 9 / $e3 * 100,
                'kh' => $c3['total']['kh'] * 4 / $e3 * 100,
            ];

            return [
                'siklus' => (array) $siklus,
                'hari' => $hari,
                'penerima' => $penerima,
                'pagu' => $pagu,
                'belanja' => $belanja,
                'menu' => $menu->map(fn ($m) => (array) $m)->all(),
                'stokKritis' => $stokKritis->map(fn ($s) => (array) $s)->all(),
                'pmTerpicu' => is_iterable($pmTerpicu) ? collect($pmTerpicu)->map(fn ($p) => (array) $p)->all() : $pmTerpicu,
                'avg' => $avg,
                'sd' => (array) $sd,
                'dist' => $dist,
                'c3' => $c3,
            ];
            })();

            // Normalisasi: Collection/objek menjadi array murni sebelum JSON.
            $data = json_decode(json_encode($data), true);
            Cache::put($key, json_encode($data), 600);
        }

        // Hidrasi ulang menjadi objek agar view tidak berubah.
        $data['siklus'] = (object) $data['siklus'];
        $data['sd'] = (object) $data['sd'];
        $data['menu'] = collect($data['menu'])->map(fn ($m) => (object) $m);
        $data['penerima']['per_kategori'] = collect($data['penerima']['per_kategori'] ?? []);
        $data['stokKritis'] = collect($data['stokKritis'])->map(fn ($s) => (object) $s);
        if (is_array($data['pmTerpicu'])) {
            $data['pmTerpicu'] = collect($data['pmTerpicu'])->map(fn ($p) => (object) $p);
        }

        return view('beranda', $data);
    }

    /** Halaman siklus menu: daftar hari, bahan, filter tanggal, dan tambahan 3B. */
    public function siklus(Request $r)
    {
        $siklus = $this->siklusAktif();
        $opsi = Siklus::opsi();
        $pola = Siklus::pola();
        $jumlah = Siklus::panjang();
        $sd = DB::table('kategori')->where('kode', 'SD13')->first();

        // Batasi hari sesuai panjang siklus aktif; filter tanggal opsional.
        $q = DB::table('menu_hari')->where('siklus_id', $siklus->id)->where('hari', '<=', $jumlah);
        if ($r->dari) {
            $q->whereDate('tanggal', '>=', $r->dari);
        }
        if ($r->sampai) {
            $q->whereDate('tanggal', '<=', $r->sampai);
        }
        $menu = $q->orderBy('hari')->get()->map(function ($m) use ($sd, $siklus) {
            $m->items = DB::table('menu_bahan as mb')->join('bahan as b', 'b.id', '=', 'mb.bahan_id')
                ->where('mb.menu_hari_id', $m->id)->orderBy('mb.id')->get(['mb.id', 'mb.gram', 'mb.peran', 'b.nama']);
            $m->energi = Nutrition::komposisi($sd->id, $m->hari, $siklus->id)['total']['energi'];

            return $m;
        });

        // Hanya bahan mentah + bumbu wajib; nama olahan disaring lewat kata kunci.
        // Tujuannya agar dropdown berisi bahan yang benar-benar dibeli dapur.
        $bumbuWajib = ['Garam beryodium', 'Kecap manis', 'Minyak kelapa sawit', 'Gula putih'];
        $olahan = ['goreng', 'rebus', 'kukus', 'bakar', 'panggang', 'tumis', 'keripik', 'kerupuk',
            'biskuit', 'kue', 'roti', 'sambal', 'masakan', 'kaleng', 'asin', 'manisan', 'selai',
            'sirup', 'dodol', 'bubur', 'instan', 'kemasan'];
        $bahan = DB::table('bahan')
            ->where(function ($q) use ($olahan, $bumbuWajib) {
                $q->where(function ($w) use ($olahan) {
                    $w->where('mentah', 1);
                    foreach ($olahan as $k) {
                        $w->where('nama', 'not like', '%'.$k.'%');
                    }
                })->orWhereIn('nama', $bumbuWajib);
            })
            ->orderBy('nama')->get(['id', 'nama', 'kelompok', 'mentah']);
        $profiles = $this->profiles();
        $kategori = $this->kategoriList();
        $base = $this->profiles()[$sd->id];
        $kategori3b = DB::table('kategori')->whereIn('kode', ['HAMIL', 'MENYUSUI', 'PRE'])
            ->orderBy('urutan')->get();
        // Tambahan 3B diambil per menu/kategori agar bisa tampil sebagai chip.
        $tambahan = DB::table('menu_tambahan as mt')
            ->join('menu_hari as mh', 'mh.id', '=', 'mt.menu_hari_id')
            ->join('kategori as k', 'k.id', '=', 'mt.kategori_id')
            ->join('bahan as b', 'b.id', '=', 'mt.bahan_id')
            ->where('mh.siklus_id', $siklus->id)
            ->where('mh.hari', '<=', $jumlah)
            ->whereIn('mt.kategori_id', $kategori3b->pluck('id')->all())
            ->orderBy('mh.hari')->orderBy('k.urutan')->orderBy('b.nama')
            ->get(['mt.id', 'mt.menu_hari_id', 'mh.hari', 'mt.gram', 'mt.metode', 'mt.catatan',
                'k.id as kategori_id', 'k.kode', 'k.nama as kategori',
                'b.id as bahan_id', 'b.nama as bahan', 'b.satuan']);

        return view('siklus_menu', compact('siklus', 'menu', 'bahan', 'jumlah', 'opsi', 'pola', 'profiles', 'kategori', 'base', 'kategori3b', 'tambahan'));
    }

    /** Detail satu hari menu beserta bahan dan komposisi gizinya. */
    public function siklusDetail(int $id)
    {
        $m = DB::table('menu_hari')->find($id);
        abort_if(! $m, 404);
        $items = DB::table('menu_bahan as mb')->join('bahan as b', 'b.id', '=', 'mb.bahan_id')
            ->where('mb.menu_hari_id', $id)->orderBy('mb.id')
            ->get(['mb.id', 'mb.gram', 'mb.peran', 'b.nama', 'b.kelompok', 'b.energi', 'b.protein']);
        $sd = DB::table('kategori')->where('kode', 'SD13')->first();
        $komp = Nutrition::komposisi($sd->id, $m->hari, $m->siklus_id);
        $tambahan = DB::table('menu_tambahan as mt')
            ->join('kategori as k', 'k.id', '=', 'mt.kategori_id')
            ->join('bahan as b', 'b.id', '=', 'mt.bahan_id')
            ->where('mt.menu_hari_id', $id)
            ->orderBy('k.urutan')->orderBy('b.nama')
            ->get(['mt.id', 'mt.gram', 'mt.metode', 'mt.catatan', 'k.nama as kategori', 'b.nama as bahan', 'b.satuan']);

        return view('siklus_detail', compact('m', 'items', 'komp', 'sd', 'tambahan'));
    }

    /** Tabel komposisi gizi satu kategori pada satu hari. */
    public function komposisi(Request $r)
    {
        $kategori = $this->kategoriList();
        $kat = DB::table('kategori')->find($r->kategori ?? DB::table('kategori')->where('kode', 'SD13')->value('id'));
        $siklus = $this->siklusAktif();
        // Hari di luar siklus aktif dikembalikan ke hari 1 agar URL tidak error.
        $hari = (int) ($r->hari ?: 1);
        if ($hari < 1 || $hari > $siklus->jumlah_hari) {
            $hari = 1;
        }
        $data = Nutrition::komposisi($kat->id, $hari, $siklus->id);
        $menuNama = $this->menuNama($hari, $siklus->id);

        return view('komposisi', compact('kategori', 'kat', 'hari', 'data', 'menuNama') + ['jumlah' => $siklus->jumlah_hari]);
    }

    /** Capaian gizi seluruh kategori pada satu hari. */
    public function capaian(Request $r)
    {
        $kategori = $this->kategoriList();
        $kat = DB::table('kategori')->find($r->kategori ?? DB::table('kategori')->where('kode', 'SD13')->value('id'));
        $siklus = $this->siklusAktif();
        $hari = (int) ($r->hari ?: 1);
        if ($hari < 1 || $hari > $siklus->jumlah_hari) {
            $hari = 1;
        }
        $data = Nutrition::komposisi($kat->id, $hari, $siklus->id);

        $semua = $kategori->map(function ($k) use ($hari, $siklus) {
            $c = Nutrition::komposisi($k->id, $hari, $siklus->id);
            $k->energi = $c['total']['energi'];
            $k->protein = $c['total']['protein'];
            $k->pct = $c['pct']['energi'];
            $k->status = Nutrition::status($c['pct']['energi']);

            return $k;
        });

        return view('capaian', compact('kategori', 'kat', 'hari', 'data', 'semua') + ['jumlah' => $siklus->jumlah_hari]);
    }

    /** Kebutuhan bahan dan biaya untuk satu kategori/hari/sejumlah porsi. */
    public function kebutuhan(Request $r)
    {
        $kategori = $this->kategoriList();
        $kat = DB::table('kategori')->find($r->kategori ?? DB::table('kategori')->where('kode', 'SD13')->value('id'));
        $siklus = $this->siklusAktif();
        $hari = (int) ($r->hari ?: 1);
        if ($hari < 1 || $hari > $siklus->jumlah_hari) {
            $hari = 1;
        }
        $porsi = (int) ($r->porsi ?: 100);
        $keb = Belanja::kebutuhan($kat->id, $hari, $porsi, $siklus->id);
        $pagu = (int) round($porsi * 10000);
        $penerima = Belanja::totalPenerima();
        $semua = Belanja::perKategoriHariIni($hari, $siklus->id);

        return view('kebutuhan', compact('kategori', 'kat', 'hari', 'porsi', 'keb', 'pagu', 'penerima', 'semua') + ['jumlah' => $siklus->jumlah_hari]);
    }

    /** Halaman gabungan pemeriksaan alergi dan aturan/varian. */
    public function alergi(Request $r)
    {
        $siklus = $this->siklusAktif();
        $jumlah = $siklus->jumlah_hari;
        $hari = (int) ($r->hari ?: 3);
        if ($hari < 1 || $hari > $jumlah) {
            $hari = 1;
        }
        $tab = in_array($r->tab, ['periksa', 'aturan'], true) ? $r->tab : 'periksa';
        $pm = DB::table('pm_alergi as p')
            ->leftJoin('kategori as k', 'k.id', '=', 'p.kategori_id')
            ->join('lokasi as l', 'l.id', '=', 'p.lokasi_id')
            ->orderBy('p.nama')->get(['p.*', 'l.nama as lokasi', 'k.nama as kategori']);
        $pm = $pm->map(function ($p) {
            $p->alergen = DB::table('alergen_pm as ap')->join('alergen as a', 'a.id', '=', 'ap.alergen_id')
                ->where('ap.pm_alergi_id', $p->id)->pluck('a.nama')->all();

            return $p;
        });
        // Hasil pemeriksaan: hanya PM yang alergennya benar-benar ada di menu hari itu.
        $terpicu = $this->deteksiAlergi($hari);
        $lokasi = DB::table('lokasi')->orderBy('nama')->get();
        $alergen = DB::table('alergen')->orderBy('nama')->get();
        $kategori = $this->kategoriList();
        $menu = DB::table('menu_hari')->where('siklus_id', $siklus->id)->where('hari', $hari)->first();
        $v = $this->varianData();

        return view('alergi', array_merge(
            compact('pm', 'terpicu', 'lokasi', 'alergen', 'hari', 'menu', 'kategori', 'tab') + ['jumlah' => $jumlah],
            $v
        ));
    }

    /** Cocokkan alergen penerima dengan bahan menu (termasuk tambahan 3B). */
    private function deteksiAlergi(int $hari): array
    {
        $mh = DB::table('menu_hari')->where('siklus_id', $this->siklusAktif()->id)->where('hari', $hari)->first();
        if (! $mh) {
            return [];
        }
        $bahanIds = DB::table('menu_bahan')->where('menu_hari_id', $mh->id)->pluck('bahan_id');

        $hasil = [];
        foreach (DB::table('pm_alergi')->get() as $p) {
            $bahanPm = $bahanIds->merge(
                DB::table('menu_tambahan')->where('menu_hari_id', $mh->id)
                    ->where('kategori_id', $p->kategori_id)->pluck('bahan_id')
            );
            $alergenBahan = DB::table('alergen_bahan')->whereIn('bahan_id', $bahanPm)
                ->join('alergen as a', 'a.id', '=', 'alergen_bahan.alergen_id')
                ->join('bahan as b', 'b.id', '=', 'alergen_bahan.bahan_id')
                ->get(['a.id as alergen_id', 'a.nama as alergen', 'b.nama as bahan']);
            $alergenPm = DB::table('alergen_pm')->where('pm_alergi_id', $p->id)->pluck('alergen_id');
            $bentrok = $alergenBahan->whereIn('alergen_id', $alergenPm);
            if ($bentrok->isNotEmpty()) {
                $p->bahan = $bentrok->pluck('bahan')->unique()->values()->all();
                $p->alergen = $bentrok->pluck('alergen')->unique()->values()->all();
                $hasil[] = $p;
            }
        }

        return $hasil;
    }

    /** Stok bahan, perkiraan pemakaian, mutasi terakhir, dan PO. */
    public function stok()
    {
        // Urutkan dari rasio stok/minimum terkecil: barang paling kritis di atas.
        $items = DB::table('stok_item')->orderByRaw('stok / NULLIF(minimum,0) ASC')->get();
        $sd = DB::table('kategori')->where('kode', 'SD13')->first();
        $penerima = Belanja::totalPenerima();
        $keb = Belanja::kebutuhan($sd->id, 3, max($penerima['total'], 1));
        $pakai = collect($keb['rows'])->pluck('qty', 'nama');

        $items = $items->map(function ($i) use ($pakai) {
            $i->terpakai = (float) ($pakai[$i->nama] ?? 0);
            $i->sisa = (float) $i->stok - $i->terpakai;

            return $i;
        });

        $mutasi = DB::table('stok_mutasi as m')->join('stok_item as i', 'i.id', '=', 'm.stok_item_id')
            ->orderByDesc('m.tanggal')->orderByDesc('m.id')->limit(8)
            ->get(['m.*', 'i.nama', 'i.satuan']);

        $po = DB::table('po')->orderByDesc('id')->limit(8)->get();
        $poItems = DB::table('po_item')->get()->groupBy('po_id');
        $poUsers = DB::table('users')->pluck('name', 'id');

        return view('stok', compact('items', 'mutasi', 'penerima', 'po', 'poItems', 'poUsers'));
    }

    /** Log HACCP dan jumlah deviasi. */
    public function haccp()
    {
        $logs = DB::table('haccp_log')->orderBy('id')->get();
        $deviasi = $logs->where('status', 'deviasi')->count();

        return view('haccp', compact('logs', 'deviasi'));
    }

    /** Catatan sisa makanan dan persentasenya. */
    public function sisa()
    {
        $rows = DB::table('sisa_makanan')->orderBy('id')->get();
        $totalSaji = $rows->sum('disajikan');
        $totalSisa = $rows->sum('sisa');
        $pct = $totalSaji > 0 ? $totalSisa / $totalSaji * 100 : 0;

        return view('sisa', compact('rows', 'totalSaji', 'totalSisa', 'pct'));
    }

    /** Master lokasi/penerima dengan periode aktif dan jumlah PM alergi. */
    public function masterPenerima()
    {
        $lokasi = DB::table('lokasi')->orderBy('nama')->get()->map(function ($l) {
            $l->baris = DB::table('penerima_kategori as pk')->join('kategori as k', 'k.id', '=', 'pk.kategori_id')
                ->where('pk.lokasi_id', $l->id)->whereNull('pk.berlaku_sampai')->orderBy('k.urutan')
                ->get(['pk.id', 'pk.jumlah', 'pk.berlaku_dari', 'k.nama as kategori', 'k.id as kategori_id']);
            $l->total = $l->baris->sum('jumlah');
            $l->alergi = DB::table('pm_alergi')->where('lokasi_id', $l->id)->count();

            return $l;
        });
        $total = Belanja::totalPenerima();
        $kategori = $this->kategoriList();

        return view('master_penerima', compact('lokasi', 'total', 'kategori'));
    }

    /** Detail satu baris penerima dan riwayat periodenya. */
    public function penerimaDetail(int $id)
    {
        $row = DB::table('penerima_kategori as pk')->join('kategori as k', 'k.id', '=', 'pk.kategori_id')
            ->join('lokasi as l', 'l.id', '=', 'pk.lokasi_id')
            ->where('pk.id', $id)->first(['pk.*', 'k.nama as kategori', 'l.nama as lokasi']);
        abort_if(! $row, 404);
        $kategori = $this->kategoriList();
        $pmTerkait = DB::table('pm_alergi')->where('lokasi_id', $row->lokasi_id)->where('kategori_id', $row->kategori_id)->count();
        $riwayat = DB::table('penerima_kategori')->where('lokasi_id', $row->lokasi_id)
            ->where('kategori_id', $row->kategori_id)->orderByDesc('berlaku_dari')->orderByDesc('id')->get();

        return view('penerima_detail', compact('row', 'kategori', 'pmTerkait', 'riwayat'));
    }

    /** Master bahan dengan pencarian, filter kelompok, dan pagination. */
    public function masterBahan(Request $r)
    {
        $q = DB::table('bahan');
        if ($r->q) {
            $q->where('nama', 'like', '%'.$r->q.'%');
        }
        if ($r->kelompok) {
            $q->where('kelompok', $r->kelompok);
        }
        $bahan = $q->orderBy('nama')->paginate(25)->withQueryString();
        $bahan->getCollection()->transform(function ($b) {
            $b->dipakai = DB::table('menu_bahan')->where('bahan_id', $b->id)->count();

            return $b;
        });
        $kelompokList = DB::table('bahan')->select('kelompok')->distinct()->orderBy('kelompok')->pluck('kelompok');
        $total = DB::table('bahan')->count();

        return view('master_bahan', compact('bahan', 'kelompokList', 'total'));
    }

    /** Detail bahan, riwayat versi, alergen, dan pemakaian di menu. */
    public function bahanDetail(int $id)
    {
        $b = DB::table('bahan')->find($id);
        abort_if(! $b, 404);
        $dipakai = DB::table('menu_bahan')->where('bahan_id', $id)->count();
        $terpakaiMenu = DB::table('menu_bahan as mb')->join('menu_hari as m', 'm.id', '=', 'mb.menu_hari_id')
            ->where('mb.bahan_id', $id)->orderBy('m.hari')
            ->get(['m.hari', 'm.nama as menu', 'mb.gram', 'mb.peran']);
        $riwayat = DB::table('bahan_versi')->where('bahan_id', $id)
            ->orderByDesc('berlaku_dari')->orderByDesc('id')->get();
        $alergen = DB::table('alergen_bahan as ab')->join('alergen as a', 'a.id', '=', 'ab.alergen_id')
            ->where('ab.bahan_id', $id)->pluck('a.nama');

        return view('bahan_detail', compact('b', 'dipakai', 'riwayat', 'alergen', 'terpakaiMenu'));
    }

    /** Daftar kategori, jumlah penerima, dan profil porsi. */
    public function masterKategori()
    {
        $kategori = $this->kategoriList()->map(function ($k) {
            $k->penerima = (int) DB::table('penerima_kategori')->where('kategori_id', $k->id)->sum('jumlah');
            $k->profil = DB::table('profil_porsi')->where('kategori_id', $k->id)->first();

            return $k;
        });

        return view('master_kategori', compact('kategori'));
    }

    /** Daftar alergen dan jumlah relasinya ke PM/bahan. */
    public function masterAlergen()
    {
        $alergen = DB::table('alergen')->orderBy('nama')->get()->map(function ($a) {
            $a->dipakai = DB::table('alergen_pm')->where('alergen_id', $a->id)->count();
            $a->bahan = DB::table('alergen_bahan')->where('alergen_id', $a->id)->count();

            return $a;
        });

        return view('master_alergen', compact('alergen'));
    }

    /** Audit log dengan filter kata/peran dan ringkasan. */
    public function audit(Request $r)
    {
        $cari = trim((string) $r->q);
        $peran = trim((string) $r->peran);
        $q = DB::table('audit_log');

        if ($peran !== '') {
            $q->where('peran', $peran);
        }

        // Cari di semua kolom teks; setiap kata harus cocok (AND),
        // wildcard % dan _ di-escape agar tidak mengubah pola.
        if ($cari !== '') {
            foreach (preg_split('/\s+/', $cari) as $kata) {
                $w = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $kata).'%';
                $q->where(function ($sub) use ($w) {
                    $sub->where('user_nama', 'like', $w)
                        ->orWhere('peran', 'like', $w)
                        ->orWhere('modul', 'like', $w)
                        ->orWhere('target', 'like', $w)
                        ->orWhere('detail', 'like', $w)
                        ->orWhere('ip', 'like', $w);
                });
            }
        }

        $log = $q->orderByDesc('id')->paginate(40)->withQueryString();
        $filter = $cari !== '' || $peran !== '';
        $ringkas = [
            'total' => $filter ? $log->total() : DB::table('audit_log')->count(),
            'semua' => DB::table('audit_log')->count(),
            'hari' => DB::table('audit_log')->whereDate('created_at', now()->toDateString())->count(),
            'user' => DB::table('audit_log')->distinct('user_nama')->count('user_nama'),
        ];

        return view('audit', compact('log', 'ringkas', 'filter', 'cari'));
    }

    /** Halaman sumber data TKPI, DBMP, dan harga acuan. */
    public function sumber()
    {
        $sumber = DB::table('sumber_data')->orderBy('kode')->get();
        $perKelompok = DB::table('bahan')->selectRaw('kelompok, COUNT(*) as n')->groupBy('kelompok')->orderByDesc('n')->get();
        $totalBahan = DB::table('bahan')->count();
        $dbmpBahan = DB::table('dbmp')->orderBy('golongan')->orderBy('nama')->get();
        $golongan = DB::table('dbmp')->selectRaw('golongan, COUNT(*) as n')->groupBy('golongan')->orderBy('golongan')->get();
        $hargaAcuan = DB::table('harga_acuan')->orderBy('kategori')->orderBy('komoditas')->get();
        $bahanTanpaHarga = DB::table('bahan')->where('harga', '<=', 0)->count();

        return view('sumber', compact('sumber', 'perKelompok', 'totalBahan', 'dbmpBahan', 'golongan', 'hargaAcuan', 'bahanTanpaHarga'));
    }

    /** Halaman pusat laporan/ekspor. */
    public function laporan()
    {
        return view('laporan');
    }

    /** Daftar pengguna dan lokasi tugasnya. */
    public function pengguna()
    {
        $users = DB::table('users as u')->leftJoin('lokasi as l', 'l.id', '=', 'u.lokasi_id')
            ->orderBy('u.name')->get(['u.*', 'l.nama as lokasi']);

        return view('pengguna', compact('users'));
    }

    /** Data pengguna untuk halaman ubah. */
    public function penggunaDetail(int $id)
    {
        $u = DB::table('users')->find($id);
        abort_if(! $u, 404);
        $lokasi = DB::table('lokasi')->orderBy('nama')->get();

        return view('pengguna_detail', compact('u', 'lokasi'));
    }

    /** Alias lama: arahkan ke tab aturan alergi. */
    public function varian()
    {
        return redirect('/alergi?tab=aturan');
    }

    /** Data aturan alergi per kategori + varian preeklampsia (dipakai halaman gabungan). */
    private function varianData(): array
    {
        // Aturan alergi per kategori diturunkan dari PM alergi + master alergen (bukan data statis).
        $aturan = DB::table('pm_alergi as p')
            ->join('kategori as k', 'k.id', '=', 'p.kategori_id')
            ->join('alergen_pm as ap', 'ap.pm_alergi_id', '=', 'p.id')
            ->join('alergen as a', 'a.id', '=', 'ap.alergen_id')
            ->selectRaw('k.id as kategori_id, k.nama as kategori, a.id as alergen_id, a.nama as alergen, COUNT(DISTINCT p.id) as pm')
            ->groupBy('k.id', 'k.nama', 'a.id', 'a.nama')->orderBy('k.nama')->get();

        foreach ($aturan as $a) {
            $a->bahan = DB::table('alergen_bahan as ab')->join('bahan as b', 'b.id', '=', 'ab.bahan_id')
                ->where('ab.alergen_id', $a->alergen_id)->orderBy('b.nama')->pluck('b.nama')->take(6)->all();
            $a->lokasi = DB::table('pm_alergi')->where('kategori_id', $a->kategori_id)->count();
        }

        // Varian preeklampsia memakai kategori khusus dan batas natrium dari pengaturan.
        $pre = DB::table('kategori')->where('khusus', 1)->first();
        $natriumMax = (float) (DB::table('pengaturan')->where('kunci', 'natrium_max')->value('nilai') ?? 600);
        $menuPre = [];
        if ($pre) {
            $siklus = $this->siklusAktif();
            for ($h = 1; $h <= Siklus::panjang(); $h++) {
                $k = Nutrition::komposisi($pre->id, $h, $siklus->id);
                if (($k['total']['energi'] ?? 0) <= 0) {
                    continue;
                }
                $menuPre[] = [
                    'hari' => $h,
                    'menu' => DB::table('menu_hari')->where('siklus_id', $siklus->id)->where('hari', $h)->value('nama') ?? '—',
                    'energi' => $k['total']['energi'],
                    'natrium' => $k['total']['natrium'],
                    'aman' => $k['total']['natrium'] <= $natriumMax,
                ];
            }
        }

        return compact('aturan', 'pre', 'menuPre', 'natriumMax');
    }

    /** Halaman pengaturan umum dan pola siklus. */
    public function pengaturan()
    {
        $settings = DB::table('pengaturan')->pluck('nilai', 'kunci')->all();
        $kategori = $this->kategoriList();
        $lokasi = DB::table('lokasi')->orderBy('nama')->get();
        $pola = Siklus::pola();
        $panjang = Siklus::panjang();
        $opsi = Siklus::opsi();
        $siklusAktif = Siklus::aktif();

        return view('pengaturan', compact('settings', 'kategori', 'lokasi', 'pola', 'panjang', 'opsi', 'siklusAktif'));
    }
}
