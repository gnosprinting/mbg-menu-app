@extends('layouts.shell')
@section('title','Data Sumber')
@section('content')

@if(session('ok'))<div class="alert ok">✓ <span style="white-space:pre-line">{{ session('ok') }}</span></div>@endif
@if(session('galat'))<div class="alert bad">! <span style="white-space:pre-line">{{ session('galat') }}</span></div>@endif

<div class="page-top">
  <div><h1>Data Sumber</h1><p>Rujukan nilai gizi dan satuan penukar yang dipakai seluruh perhitungan</p></div>
</div>

<div class="kpis">
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)"><div class="top"><span class="lbl">Bahan di master</span></div><div class="val">{{ number_format($totalBahan, 0, ',', '.') }}</div><div class="meta">impor TKPI + bahan menu</div><div class="ico-r"><i class="fa-solid fa-book"></i></div></div>
  <div class="kpi" style="--tint:var(--skybg);--accent:var(--sky)"><div class="top"><span class="lbl">Sumber aktif</span></div><div class="val">{{ $sumber->count() }}</div><div class="meta">TKPI &amp; DBMP</div><div class="ico-r"><i class="fa-solid fa-database"></i></div></div>
  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)"><div class="top"><span class="lbl">Kelompok pangan</span></div><div class="val">{{ $perKelompok->count() }}</div><div class="meta">hasil pemetaan otomatis</div><div class="ico-r"><i class="fa-solid fa-layer-group"></i></div></div>
  <div class="kpi" style="--tint:var(--lemon);--accent:var(--amber)"><div class="top"><span class="lbl">Golongan DBMP</span></div><div class="val">8</div><div class="meta">satuan penukar</div><div class="ico-r"><i class="fa-solid fa-table-list"></i></div></div>
</div>

<div class="grid g-2">
  @foreach($sumber as $s)
    <div class="card">
      <div class="card-head"><div><h3>{{ $s->nama }}</h3><div class="sub">{{ $s->penerbit }} · {{ $s->tahun }}</div></div><div class="right"><span class="chip violet">{{ $s->kode }}</span></div></div>
      <p style="margin:0;font-size:11.8px;color:#39405A;line-height:1.6">{{ $s->keterangan }}</p>
      <div class="list" style="margin-top:10px">
        <div class="item"><div><b>Jumlah baris</b><small>tercatat di aplikasi</small></div><div class="right"><span class="badge info">{{ number_format($s->jumlah_baris, 0, ',', '.') }}</span></div></div>
        <div class="item"><div><b>Satuan nilai</b><small>per 100 g bagian dapat dimakan</small></div><div class="right"><span class="chip green">BDD</span></div></div>
      </div>
    </div>
  @endforeach
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Tabel TKPI — sebaran bahan</h3><div class="sub">Hasil impor bahan mentah</div></div><div class="right"><a class="link" href="/master-bahan">Buka master bahan →</a></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Kelompok</th><th class="num">Jumlah bahan</th><th>Grafik</th></tr></thead>
    <tbody>
      @php $maxK = $perKelompok->max('n') ?: 1; @endphp
      @foreach($perKelompok as $k)
        <tr><td class="strong">{{ $k->kelompok }}</td><td class="num">{{ number_format($k->n, 0, ',', '.') }}</td>
          <td><div class="bar" style="width:180px"><i style="width:{{ round($k->n / $maxK * 100) }}%;background:#7B4DFF"></i></div></td></tr>
      @endforeach
    </tbody>
  </table></div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Tabel DBMP — {{ $dbmpBahan->count() }} bahan penukar</h3><div class="sub">PERSAGI &amp; ASDI edisi 5 · 8 golongan</div></div><div class="right"><span class="chip violet">{{ $golongan->count() }} golongan</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Golongan</th><th>Bahan</th><th class="num">Gram / 1 SP</th><th>URT</th><th class="num">Gram / URT</th><th>Catatan</th></tr></thead>
    <tbody>
      @foreach($dbmpBahan as $d)
        <tr>
          <td class="strong">{{ $d->golongan }}</td>
          <td>{{ $d->nama }}</td>
          <td class="num">{{ $d->bebas ? 'bebas' : number_format($d->gram_sp, 0, ',', '.') }}</td>
          <td>{{ $d->urt ?? '—' }}</td>
          <td class="num">{{ $d->gram_urt ?: '—' }}</td>
          <td>@if($d->bebas)<span class="chip green">bebas</span>@else<span class="muted">—</span>@endif</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="footnote">1 SP tiap golongan mengikuti nilai acuan DBMP; URT dipakai dapur sebagai pembanding.</div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Harga acuan pasar tradisional</h3><div class="sub">Bahan pangan pokok, penting &amp; mentah · {{ $hargaAcuan->count() }} komoditas</div></div><div class="right"><span class="chip orange">{{ $bahanTanpaHarga }} bahan tanpa harga</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Komoditas</th><th>Kategori</th><th>Satuan</th><th class="num">Harga</th><th>Wilayah</th><th>Tanggal</th></tr></thead>
    <tbody>
      @foreach($hargaAcuan as $h)
        <tr>
          <td class="strong">{{ $h->komoditas }}</td>
          <td><span class="chip {{ $h->kategori === 'pokok' ? 'violet' : ($h->kategori === 'penting' ? 'orange' : 'green') }}">{{ $h->kategori }}</span></td>
          <td>{{ $h->satuan }}</td>
          <td class="num">Rp {{ number_format($h->harga, 0, ',', '.') }}</td>
          <td>{{ $h->wilayah }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($h->tanggal)->format('d M Y') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @if(in_array(auth()->user()->peran, ['admin', 'gizi', 'akuntan']))
    <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
      <form method="post" action="/harga-acuan/tarik" onsubmit="return confirm('Tarik harga pasar terbaru dari PIHPS Bank Indonesia?')">
        @csrf
        <button class="btn">Tarik harga pasar (PIHPS)</button>
      </form>
      <form method="post" action="/harga-acuan/apply" onsubmit="return confirm('Isi harga bahan yang masih 0 dari acuan pasar?')">
        @csrf
        <button class="btn">Isi harga bahan dari acuan</button>
      </form>
    </div>
    <span class="footnote" style="display:inline-block;margin-top:6px">Tarik harga: 11 komoditas strategis PIHPS (beras, daging, telur, cabai, bawang, minyak, gula). Sayur/buah diisi manual. Isi harga hanya mengisi bahan berharga 0; harga lama tidak ditimpa.</span>
  @endif
</div>

@endsection
