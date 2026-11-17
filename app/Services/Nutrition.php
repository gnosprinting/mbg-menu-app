<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Nutrition
{
    public static function baseProfile(): array
    {
        $k = DB::table('kategori')->where('kode', 'SD13')->first();
        $p = DB::table('profil_porsi')->where('kategori_id', $k->id)->first();

        return [
            'pokok' => (float) $p->nasi, 'hewani' => (float) $p->hewani, 'nabati' => (float) $p->nabati,
            'sayur' => (float) $p->sayur, 'buah' => (float) $p->buah, 'minyak' => (float) $p->minyak,
            'bumbu' => (float) $p->garam, 'garam' => (float) $p->garam,
        ];
    }

    public static function profile(int $kategoriId): array
    {
        $p = DB::table('profil_porsi')->where('kategori_id', $kategoriId)->first();
        if (! $p) {
            return self::baseProfile();
        }

        return [
            'pokok' => (float) $p->nasi, 'hewani' => (float) $p->hewani, 'nabati' => (float) $p->nabati,
            'sayur' => (float) $p->sayur, 'buah' => (float) $p->buah, 'minyak' => (float) $p->minyak,
            'bumbu' => (float) $p->garam, 'garam' => (float) $p->garam,
        ];
    }

    public static function menu(int $hari, ?int $siklusId = null)
    {
        $siklusId = $siklusId ?: (int) Siklus::aktif()->id;
        $q = DB::table('menu_hari')->where('hari', $hari)->where('siklus_id', $siklusId);
        $mh = $q->first();
        if (! $mh) {
            return collect();
        }

        return DB::table('menu_bahan as mb')
            ->join('bahan as b', 'b.id', '=', 'mb.bahan_id')
            ->leftJoin('bahan_versi as v', 'v.id', '=', 'mb.bahan_versi_id')
            ->where('mb.menu_hari_id', $mh->id)
            ->orderByRaw("CASE mb.peran WHEN 'pokok' THEN 1 WHEN 'hewani' THEN 2 WHEN 'nabati' THEN 3 WHEN 'sayur' THEN 4 WHEN 'buah' THEN 5 WHEN 'minyak' THEN 6 ELSE 7 END")
            ->get([
                'mb.id', 'mb.gram', 'mb.peran', 'b.nama', 'b.kelompok', 'b.sp_label', 'b.urt_label',
                DB::raw('COALESCE(v.energi, b.energi) as energi'),
                DB::raw('COALESCE(v.protein, b.protein) as protein'),
                DB::raw('COALESCE(v.lemak, b.lemak) as lemak'),
                DB::raw('COALESCE(v.kh, b.kh) as kh'),
                DB::raw('COALESCE(v.natrium, b.natrium) as natrium'),
                DB::raw('COALESCE(v.serat, b.serat) as serat'),
                DB::raw('COALESCE(v.vit_a, b.vit_a, 0) as vit_a'),
                DB::raw('COALESCE(v.vit_b, b.vit_b, 0) as vit_b'),
                DB::raw('COALESCE(v.bdd, b.bdd) as bdd'),
                DB::raw('COALESCE(mb.harga_beli, v.harga, b.harga) as harga'),
                DB::raw('COALESCE(v.satuan, b.satuan) as satuan'),
                'mb.bahan_versi_id',
            ]);
    }

    /**
     * Hitung komposisi satu kategori untuk satu hari.
     * Gram menu disimpan untuk profil basis (SD 1–3), lalu diskalakan per peran.
     */
    public static function komposisi(int $kategoriId, int $hari, ?int $siklusId = null): array
    {
        $siklusId = $siklusId ?: (int) Siklus::aktif()->id;
        $versi = (int) Cache::get('mbg.cache_version', 1);
        $key = "nutrisi:v{$versi}:k{$kategoriId}:h{$hari}:s".($siklusId ?? 0);
        $cached = Cache::get($key);
        if (is_string($cached)) {
            $data = json_decode($cached, true);
            if (is_array($data) && isset($data['rows'])) {
                return $data;
            }
        }

        $base = self::baseProfile();
        $prof = self::profile($kategoriId);
        $target = self::target($kategoriId);
        $rows = [];
        $t = ['gram' => 0, 'energi' => 0, 'protein' => 0, 'lemak' => 0, 'kh' => 0, 'natrium' => 0, 'serat' => 0, 'vit_a' => 0, 'vit_b' => 0];

        foreach (self::menu($hari, $siklusId) as $m) {
            $peran = $m->peran === 'bumbu' ? 'bumbu' : $m->peran;
            $rasio = ($base[$peran] ?? 0) > 0 ? ($prof[$peran] ?? 0) / $base[$peran] : 1;
            $gram = (float) $m->gram * $rasio;

            $e = $gram / 100 * (float) $m->energi;
            $p = $gram / 100 * (float) $m->protein;
            $l = $gram / 100 * (float) $m->lemak;
            $k = $gram / 100 * (float) $m->kh;
            $na = $gram / 100 * (float) $m->natrium;
            $fi = $gram / 100 * (float) $m->serat;
            $va = $gram / 100 * (float) ($m->vit_a ?? 0);
            $vb = $gram / 100 * (float) ($m->vit_b ?? 0);

            $rows[] = [
                'nama' => $m->nama, 'kelompok' => $m->kelompok, 'peran' => $m->peran,
                'sp' => $m->sp_label, 'urt' => $m->urt_label, 'gram' => $gram,
                'energi' => $e, 'protein' => $p, 'lemak' => $l, 'kh' => $k, 'natrium' => $na,
                'serat' => $fi, 'vit_a' => $va, 'vit_b' => $vb,
                'bdd' => (float) ($m->bdd ?? 100), 'harga' => (int) ($m->harga ?? 0),
                'satuan' => $m->satuan ?? 'kg',
            ];

            $t['gram'] += $gram; $t['energi'] += $e; $t['protein'] += $p;
            $t['lemak'] += $l; $t['kh'] += $k; $t['natrium'] += $na; $t['serat'] += $fi;
            $t['vit_a'] += $va; $t['vit_b'] += $vb;
        }

        $pct = fn ($v, $tgt) => $tgt > 0 ? $v / $tgt * 100 : 0;

        $result = ['rows' => $rows, 'total' => $t, 'target' => $target, 'pct' => [
            'energi' => $pct($t['energi'], $target['energi']),
            'protein' => $pct($t['protein'], $target['protein']),
            'lemak' => $pct($t['lemak'], $target['lemak']),
            'kh' => $pct($t['kh'], $target['kh']),
        ]];

        Cache::put($key, json_encode($result), 600);

        return $result;
    }

    public static function target(int $kategoriId): array
    {
        $k = DB::table('kategori')->where('id', $kategoriId)->first();
        $persen = (float) (DB::table('pengaturan')->where('kunci', 'target_persen')->value('nilai') ?? 30) / 100;

        return [
            'energi' => $k->target_energi ?? (float) $k->akg_energi * $persen,
            'protein' => $k->target_protein ?? (float) $k->akg_protein * $persen,
            'lemak' => (float) ($k->akg_lemak ?? 0) * $persen,
            'kh' => (float) ($k->akg_kh ?? 0) * $persen,
            'akg_energi' => (float) $k->akg_energi,
            'akg_protein' => (float) $k->akg_protein,
            'akg_lemak' => (float) ($k->akg_lemak ?? 0),
            'akg_kh' => (float) ($k->akg_kh ?? 0),
        ];
    }

    public static function status(float $pct): string
    {
        return match (true) {
            $pct < 75 => 'kurang',
            $pct < 90 => 'waspada',
            $pct <= 115 => 'aman',
            $pct <= 130 => 'lebih',
            default => 'tinggi',
        };
    }
}
