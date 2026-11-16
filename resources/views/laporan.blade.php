@extends('layouts.shell')
@section('title','Laporan')
@section('content')

<div class="page-top">
  <div><h1>Laporan</h1><p>Semua laporan operasional per periode · PDF dan Excel</p></div>
  <div class="actions">
    <div class="seg"><a class="on" href="#">Bulan ini</a><a href="#">Pekan</a><a href="#">Kustom</a></div>
    <button class="btn">Unduh semua</button>
  </div>
</div>

<div class="card" style="margin-bottom:14px">
  <div class="card-head"><div><h3>Ekspor langsung dari database</h3><div class="sub">Berkas dibuat saat diklik, bukan contoh</div></div></div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a class="btn ghost sm" href="/export/komposisi.pdf?kategori={{ DB::table('kategori')->where('kode','SD13')->value('id') }}&hari=1">Komposisi PDF</a>
    <a class="btn ghost sm" href="/export/komposisi.xlsx?kategori={{ DB::table('kategori')->where('kode','SD13')->value('id') }}&hari=1">Komposisi Excel</a>
    <a class="btn ghost sm" href="/export/capaian.pdf?kategori={{ DB::table('kategori')->where('kode','SD13')->value('id') }}&hari=1">Capaian PDF</a>
    <a class="btn ghost sm" href="/export/kebutuhan.pdf?kategori={{ DB::table('kategori')->where('kode','SD13')->value('id') }}&hari=1&porsi=100">Kebutuhan PDF</a>
    <a class="btn ghost sm" href="/export/kebutuhan.xlsx?kategori={{ DB::table('kategori')->where('kode','SD13')->value('id') }}&hari=1&porsi=100">Kebutuhan Excel</a>
  </div>
</div>

<div class="grid g-3">
  <div class="card">
    <div class="card-head"><div><h3>Capaian gizi</h3><div class="sub">Per kategori, menu, dan hari</div></div></div>
    <p style="margin:0;color:#39405A;font-size:11.8px;line-height:1.55">3 tabel per menu: komposisi kelompok, kecukupan 30% AKG, dan capaian.</p>
    <div style="display:flex;gap:8px;margin-top:12px"><button class="btn sm">PDF</button><button class="btn ghost sm">Excel</button></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>Belanja &amp; pagu</h3><div class="sub">Rekap bahan, harga, selisih</div></div></div>
    <p style="margin:0;color:#39405A;font-size:11.8px;line-height:1.55">Kebutuhan per kategori, belanja lengkap, dan perbandingan terhadap pagu harian.</p>
    <div style="display:flex;gap:8px;margin-top:12px"><button class="btn sm">PDF</button><button class="btn ghost sm">Excel</button></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>Deteksi alergi</h3><div class="sub">A4 per hari/pekan/bulan</div></div></div>
    <p style="margin:0;color:#39405A;font-size:11.8px;line-height:1.55">Daftar PM terpicu, menu pengganti, dan tanda tangan pengawas.</p>
    <div style="display:flex;gap:8px;margin-top:12px"><button class="btn sm">PDF</button><button class="btn ghost sm">Excel</button></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>Stok &amp; PO</h3><div class="sub">Kartu stok dan mutasi</div></div></div>
    <p style="margin:0;color:#39405A;font-size:11.8px;line-height:1.55">Mutasi masuk/keluar, opname, dan riwayat purchase order.</p>
    <div style="display:flex;gap:8px;margin-top:12px"><button class="btn sm">PDF</button><button class="btn ghost sm">Excel</button></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>HACCP</h3><div class="sub">Log harian &amp; deviasi</div></div></div>
    <p style="margin:0;color:#39405A;font-size:11.8px;line-height:1.55">Checklist titik kendali, tindakan korektif, dan rekap kepatuhan.</p>
    <div style="display:flex;gap:8px;margin-top:12px"><button class="btn sm">PDF</button><button class="btn ghost sm">Excel</button></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>Sisa makanan</h3><div class="sub">Sisa per kelompok &amp; tren</div></div></div>
    <p style="margin:0;color:#39405A;font-size:11.8px;line-height:1.55">Persentase sisa, peringkat menu, dan rekomendasi perbaikan.</p>
    <div style="display:flex;gap:8px;margin-top:12px"><button class="btn sm">PDF</button><button class="btn ghost sm">Excel</button></div>
  </div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Riwayat unduhan</h3><div class="sub">30 hari terakhir</div></div><div class="right"><a class="link" href="#">Bersihkan →</a></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Berkas</th><th>Jenis</th><th>Periode</th><th>Dibuat</th><th>Oleh</th><th class="num">Ukuran</th></tr></thead>
    <tbody>
      <tr><td class="strong">Capaian_SD1-3_Nov2026.pdf</td><td><span class="chip violet">PDF</span></td><td>1–4 Nov 2026</td><td>4 Nov 09.12</td><td>Rina</td><td class="num">812 KB</td></tr>
      <tr><td class="strong">Belanja_Lengkap_Nov2026.xlsx</td><td><span class="chip green">Excel</span></td><td>November 2026</td><td>4 Nov 08.45</td><td>Budi</td><td class="num">1,2 MB</td></tr>
      <tr><td class="strong">Deteksi_Alergi_4Nov2026.pdf</td><td><span class="chip violet">PDF</span></td><td>4 Nov 2026</td><td>4 Nov 07.55</td><td>Rina</td><td class="num">246 KB</td></tr>
      <tr><td class="strong">HACCP_Log_Nov2026.pdf</td><td><span class="chip violet">PDF</span></td><td>November 2026</td><td>3 Nov 18.20</td><td>Pengawas</td><td class="num">508 KB</td></tr>
    </tbody>
  </table></div>
</div>

@endsection
