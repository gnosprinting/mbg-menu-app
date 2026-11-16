<?php

namespace App\Http\Controllers;

use App\Services\Belanja;
use App\Services\Nutrition;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    private function ctx(Request $r): array
    {
        $kat = DB::table('kategori')->find($r->kategori ?? DB::table('kategori')->where('kode', 'SD13')->value('id'));
        $hari = (int) ($r->hari ?: 1);
        $porsi = (int) ($r->porsi ?: 100);

        return [$kat, $hari, $porsi];
    }

    private function pdf(string $view, array $data, string $filename, string $orientasi = 'landscape')
    {
        $html = view($view, $data)->render();
        $pdf = new Dompdf(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', $orientasi);
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function komposisiPdf(Request $r)
    {
        [$kat, $hari, $porsi] = $this->ctx($r);
        $data = Nutrition::komposisi($kat->id, $hari);
        $menu = DB::table('menu_hari')->where('hari', $hari)->value('nama');

        return $this->pdf('exports.komposisi', compact('kat', 'hari', 'data', 'menu'),
            'Komposisi_'.$kat->kode.'_H'.$hari.'.pdf');
    }

    public function capaianPdf(Request $r)
    {
        [$kat, $hari] = $this->ctx($r);
        $data = Nutrition::komposisi($kat->id, $hari);
        $semua = DB::table('kategori')->orderBy('urutan')->get()->map(function ($k) use ($hari) {
            $c = Nutrition::komposisi($k->id, $hari);
            $k->energi = $c['total']['energi'];
            $k->protein = $c['total']['protein'];
            $k->pct = $c['pct']['energi'];
            $k->status = Nutrition::status($c['pct']['energi']);

            return $k;
        });

        return $this->pdf('exports.capaian', compact('kat', 'hari', 'data', 'semua'),
            'Capaian_'.$kat->kode.'_H'.$hari.'.pdf');
    }

    public function kebutuhanPdf(Request $r)
    {
        [$kat, $hari, $porsi] = $this->ctx($r);
        $keb = Belanja::kebutuhan($kat->id, $hari, $porsi);
        $pagu = $porsi * 10000;

        return $this->pdf('exports.kebutuhan', compact('kat', 'hari', 'porsi', 'keb', 'pagu'),
            'Kebutuhan_'.$kat->kode.'_H'.$hari.'.pdf');
    }

    public function komposisiXlsx(Request $r)
    {
        [$kat, $hari] = $this->ctx($r);
        $data = Nutrition::komposisi($kat->id, $hari);
        $menu = DB::table('menu_hari')->where('hari', $hari)->value('nama');

        $s = new Spreadsheet();
        $sh = $s->getActiveSheet();
        $sh->setTitle('Komposisi');
        $sh->fromArray(['Komposisi & Gizi — '.$kat->nama.' — Hari '.$hari.' ('.$menu.')'], null, 'A1');
        $sh->mergeCells('A1:M1');
        $sh->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $head = ['Bahan', 'Kelompok', 'SP', 'URT', 'Berat (g)', 'Energi (kkal)', 'Protein (g)', 'Lemak (g)', 'KH (g)', 'Serat (g)', 'Vit A (µg)', 'Vit B (mg)', 'Natrium (mg)'];
        $sh->fromArray($head, null, 'A3');
        $sh->getStyle('A3:M3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sh->getStyle('A3:M3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('5B2BE0');

        $row = 4;
        foreach ($data['rows'] as $b) {
            $sh->fromArray([$b['nama'], $b['kelompok'], $b['sp'], $b['urt'], round($b['gram'], 1),
                round($b['energi'], 0), round($b['protein'], 1), round($b['lemak'], 1),
                round($b['kh'], 1), round($b['serat'] ?? 0, 1), round($b['vit_a'] ?? 0, 1),
                round($b['vit_b'] ?? 0, 2), round($b['natrium'], 0)], null, 'A'.$row);
            $row++;
        }
        $sh->fromArray(['Sub total', '', '', '', round($data['total']['gram'], 1), round($data['total']['energi'], 0),
            round($data['total']['protein'], 1), round($data['total']['lemak'], 1),
            round($data['total']['kh'], 1), round($data['total']['serat'] ?? 0, 1),
            round($data['total']['vit_a'] ?? 0, 1), round($data['total']['vit_b'] ?? 0, 2),
            round($data['total']['natrium'], 0)], null, 'A'.$row);
        $sh->getStyle('A'.$row.':M'.$row)->getFont()->setBold(true);
        $sh->getStyle('A'.$row.':M'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1EBFF');

        $row += 2;
        $sh->fromArray(['Target 30% AKG', 'Energi', 'Protein', 'Lemak', 'KH'], null, 'A'.$row);
        $sh->fromArray(['Target', round($data['target']['energi']), round($data['target']['protein'], 1),
            round($data['target']['lemak'], 1), round($data['target']['kh'], 1)], null, 'A'.($row + 1));
        $sh->fromArray(['Capaian %', round($data['pct']['energi']), round($data['pct']['protein']),
            round($data['pct']['lemak']), round($data['pct']['kh'])], null, 'A'.($row + 2));
        $sh->getStyle('A'.$row.':E'.$row)->getFont()->setBold(true);

        foreach (range('A', 'J') as $col) {
            $sh->getColumnDimension($col)->setAutoSize(true);
        }
        $sh->getStyle('E4:J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $file = storage_path('app/komposisi_'.$kat->kode.'_H'.$hari.'.xlsx');
        (new Xlsx($s))->save($file);

        return response()->download($file, 'Komposisi_'.$kat->kode.'_H'.$hari.'.xlsx')->deleteFileAfterSend(true);
    }

    public function kebutuhanXlsx(Request $r)
    {
        [$kat, $hari, $porsi] = $this->ctx($r);
        $keb = Belanja::kebutuhan($kat->id, $hari, $porsi);

        $s = new Spreadsheet();
        $sh = $s->getActiveSheet();
        $sh->setTitle('Belanja');
        $sh->fromArray(['Kebutuhan Bahan — '.$kat->nama.' — Hari '.$hari.' — '.$porsi.' porsi'], null, 'A1');
        $sh->mergeCells('A1:G1');
        $sh->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sh->fromArray(['Bahan', 'Gram/porsi', 'Kebutuhan', 'Satuan', 'Harga satuan', 'Harga/porsi', 'Subtotal'], null, 'A3');
        $sh->getStyle('A3:G3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sh->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('5B2BE0');

        $row = 4;
        foreach ($keb['rows'] as $b) {
            $sh->fromArray([$b['nama'], round($b['gram_porsi'], 1), round($b['qty'], 2), $b['satuan'],
                $b['harga'], round($b['harga_porsi'], 0), round($b['subtotal'], 0)], null, 'A'.$row);
            $row++;
        }
        $sh->fromArray(['Total', '', '', '', '', '', round($keb['total'], 0)], null, 'A'.$row);
        $sh->getStyle('A'.$row.':G'.$row)->getFont()->setBold(true);
        $sh->getStyle('A'.$row.':G'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1EBFF');
        $sh->fromArray(['Pagu', $porsi * 10000], null, 'A'.($row + 2));

        foreach (range('A', 'G') as $col) {
            $sh->getColumnDimension($col)->setAutoSize(true);
        }
        $sh->getStyle('B4:G'.($row + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $file = storage_path('app/belanja_'.$kat->kode.'_H'.$hari.'.xlsx');
        (new Xlsx($s))->save($file);

        return response()->download($file, 'Kebutuhan_'.$kat->kode.'_H'.$hari.'.xlsx')->deleteFileAfterSend(true);
    }

    private function alergiRows(int $hari): array
    {
        $menu = DB::table('menu_hari')->where('hari', $hari)->first();
        $bahanIds = $menu ? DB::table('menu_bahan')->where('menu_hari_id', $menu->id)->pluck('bahan_id') : collect();
        $alergenBahan = DB::table('alergen_bahan')->whereIn('bahan_id', $bahanIds)
            ->join('alergen as a', 'a.id', '=', 'alergen_bahan.alergen_id')
            ->join('bahan as b', 'b.id', '=', 'alergen_bahan.bahan_id')
            ->get(['a.id as alergen_id', 'a.nama as alergen', 'b.nama as bahan']);

        $rows = [];
        foreach (DB::table('pm_alergi as p')->leftJoin('kategori as k', 'k.id', '=', 'p.kategori_id')
            ->join('lokasi as l', 'l.id', '=', 'p.lokasi_id')->orderBy('p.nama')
            ->get(['p.*', 'k.nama as kategori', 'l.nama as lokasi']) as $pm) {
            $ids = DB::table('alergen_pm')->where('pm_alergi_id', $pm->id)->pluck('alergen_id');
            $bentrok = $alergenBahan->whereIn('alergen_id', $ids);
            $rows[] = [
                'nama' => $pm->nama, 'kelas' => $pm->kelas, 'kategori' => $pm->kategori,
                'lokasi' => $pm->lokasi,
                'alergen' => DB::table('alergen')->whereIn('id', $ids)->pluck('nama')->implode(', '),
                'bahan' => $bentrok->pluck('bahan')->unique()->implode(', '),
                'status' => $bentrok->isNotEmpty() ? 'TERPICU' : 'AMAN',
            ];
        }

        return $rows;
    }

    public function alergiPdf(Request $r)
    {
        $hari = (int) ($r->hari ?: 3);
        $rows = $this->alergiRows($hari);
        $menu = DB::table('menu_hari')->where('hari', $hari)->value('nama') ?? '—';

        return $this->pdf('exports.alergi', compact('rows', 'hari', 'menu'), 'Laporan_Alergi_H'.$hari.'.pdf');
    }

    public function alergiXlsx(Request $r)
    {
        $hari = (int) ($r->hari ?: 3);
        $rows = $this->alergiRows($hari);
        $menu = DB::table('menu_hari')->where('hari', $hari)->value('nama') ?? '—';

        $s = new Spreadsheet();
        $sh = $s->getActiveSheet();
        $sh->setTitle('Alergi');
        $sh->fromArray(['Laporan Deteksi Alergi — Hari '.$hari.' — '.$menu], null, 'A1');
        $sh->mergeCells('A1:G1');
        $sh->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sh->fromArray(['Nama', 'Kelas', 'Kategori PM', 'Lokasi', 'Alergen', 'Bahan terpicu', 'Status'], null, 'A3');
        $sh->getStyle('A3:G3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sh->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('5B2BE0');

        $row = 4;
        foreach ($rows as $x) {
            $sh->fromArray([$x['nama'], $x['kelas'], $x['kategori'], $x['lokasi'], $x['alergen'], $x['bahan'], $x['status']], null, 'A'.$row);
            $row++;
        }
        foreach (range('A', 'G') as $col) {
            $sh->getColumnDimension($col)->setAutoSize(true);
        }

        $file = storage_path('app/alergi_H'.$hari.'.xlsx');
        (new Xlsx($s))->save($file);

        return response()->download($file, 'Laporan_Alergi_H'.$hari.'.xlsx')->deleteFileAfterSend(true);
    }
}
