@extends('layouts.shell')
@section('title','Beranda')
@section('content')
@php
  $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
  $jam = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][now()->dayOfWeek];
@endphp

@if(session('ok'))
  <div class="alert ok">✓ {{ session('ok') }}</div>
@endif

<div class="page-top">
  <div>
    <h1>Beranda</h1>
    <p>Ringkasan operasional {{ DB::table('pengaturan')->where('kunci','sppg_nama')->value('nilai') }} ·
       {{ $jam }}, {{ now()->translatedFormat('j F Y') }} · siklus {{ $siklus->jumlah_hari }} hari (hari {{ $hari }})</p>
  </div>
  <div class="actions">
    <div class="seg"><a class="on" href="#">Hari ini</a><a href="#">Pekan</a><a href="#">Bulan</a></div>
    <button class="btn ghost">Ekspor</button>
  </div>
</div>

<div class="kpis">
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)">
    <div class="top"><span class="lbl">Penerima manfaat</span><span class="ico"><i class="fa-solid fa-users"></i></span></div>
    <div class="val">{{ $rp($penerima['total']) }} <small>porsi</small></div>
    <div class="meta">{{ $penerima['per_kategori']->count() }} kategori · {{ $penerima['per_kategori']->sum('jumlah') }} porsi terdaftar</div>
    <svg class="spark" viewBox="0 0 120 32" preserveAspectRatio="none"><polyline points="0,25 20,21 40,23 60,15 80,17 100,9 120,11" fill="none" stroke="#7B4DFF" stroke-width="2.2"/></svg>
  </div>

  <div class="kpi" style="--tint:var(--skybg);--accent:var(--sky)">
    <div class="top"><span class="lbl">Pagu hari ini</span><span class="ico"><i class="fa-solid fa-wallet"></i></span></div>
    <div class="val">Rp {{ $rp($pagu / 1000000) }} <small>juta</small></div>
    <div class="meta"><span class="badge info">{{ $pagu > 0 ? round($belanja['total'] / $pagu * 100) : 0 }}%</span> terpakai untuk bahan</div>
    <svg class="spark" viewBox="0 0 120 32" preserveAspectRatio="none"><polyline points="0,11 20,13 40,10 60,14 80,12 100,15 120,13" fill="none" stroke="#22A6FF" stroke-width="2.2"/></svg>
  </div>

  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)">
    <div class="top"><span class="lbl">Belanja bahan</span><span class="ico"><i class="fa-solid fa-cart-shopping"></i></span></div>
    <div class="val">Rp {{ number_format($belanja['total'] / 1000000, 1, ',', '.') }} <small>juta</small></div>
    <div class="meta"><span class="badge up">estimasi</span> 13 kategori × 100 porsi</div>
    <svg class="spark" viewBox="0 0 120 32" preserveAspectRatio="none"><polyline points="0,19 20,15 40,21 60,13 80,18 100,12 120,16" fill="none" stroke="#12B981" stroke-width="2.2"/></svg>
  </div>

  <div class="kpi" style="--tint:var(--lemon);--accent:var(--amber)">
    <div class="top"><span class="lbl">Capaian gizi</span><span class="ico"><i class="fa-solid fa-bullseye"></i></span></div>
    <div class="val">{{ round($avg) }} <small>% AKG</small></div>
    <div class="meta">{{ $pmTerpicu ? count($pmTerpicu).' PM alergi terpicu' : 'tidak ada alergi terpicu' }}</div>
    <svg class="spark" viewBox="0 0 120 32" preserveAspectRatio="none"><polyline points="0,17 20,19 40,14 60,16 80,11 100,14 120,10" fill="none" stroke="#FFB020" stroke-width="2.2"/></svg>
  </div>
</div>

<div class="grid g-2-1">
  <div class="card">
    <div class="card-head">
      <div><h3>Capaian gizi harian</h3><div class="sub">Energi dan karbohidrat · {{ $siklus->jumlah_hari }} hari siklus</div></div>
      <div class="right"><span class="chip violet">Siklus {{ $siklus->jumlah_hari }} hari</span></div>
    </div>
    <svg viewBox="0 0 720 230" style="width:100%;height:225px">
      <line class="gridline" x1="40" y1="18" x2="700" y2="18"/>
      <line class="gridline" x1="40" y1="75.3" x2="700" y2="75.3"/>
      <line class="gridline" x1="40" y1="132.7" x2="700" y2="132.7"/>
      <line class="gridline" x1="40" y1="190" x2="700" y2="190"/>
      <text class="axis" x="6" y="22">120%</text><text class="axis" x="6" y="79">110%</text>
      <text class="axis" x="6" y="136">100%</text><text class="axis" x="10" y="194">90%</text>
      <line x1="40" y1="132.7" x2="700" y2="132.7" stroke="#C9BDF5" stroke-width="1.4" stroke-dasharray="5 5"/>
      <text class="axis" x="648" y="126" style="fill:#8C84B8">target</text>
      <defs><linearGradient id="g1" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#A78BFA" stop-opacity=".55"/><stop offset="100%" stop-color="#A78BFA" stop-opacity="0"/></linearGradient></defs>
      <polygon points="40,109.7 172,126.9 304,115.5 436,98.3 568,144.1 700,109.7 700,190 40,190" fill="url(#g1)"/>
      <polyline points="40,63.9 172,86.8 304,75.3 436,52.4 568,104 700,69.6" fill="none" stroke="#22A6FF" stroke-width="2.4" stroke-linecap="round"/>
      <polyline points="40,109.7 172,126.9 304,115.5 436,98.3 568,144.1 700,109.7" fill="none" stroke="#7B4DFF" stroke-width="2.8" stroke-linecap="round"/>
      <circle cx="304" cy="115.5" r="4.6" fill="#fff" stroke="#7B4DFF" stroke-width="2.6"/>
      <line x1="304" y1="18" x2="304" y2="190" stroke="#C9BDF5" stroke-width="1.2" stroke-dasharray="3 4"/>
      <rect x="392" y="26" width="112" height="26" rx="8" fill="#fff" stroke="#E7EBFA"/>
      <text x="402" y="43" style="font:11px Inter,Arial;fill:#39405A">Hari ini · {{ round($c3['pct']['energi']) }}%</text>
      <text class="axis" x="30" y="212">Hari 1</text><text class="axis" x="162" y="212">Hari 2</text>
      <text class="axis" x="294" y="212">Hari 3</text><text class="axis" x="426" y="212">Hari 4</text>
      <text class="axis" x="558" y="212">Hari 5</text><text class="axis" x="690" y="212">Hari 6</text>
    </svg>
    <div class="legend-row"><span><i style="background:#7B4DFF"></i>Energi</span><span><i style="background:#22A6FF"></i>Karbohidrat</span><span><i style="background:#C9BDF5"></i>Batas bawah 90%</span></div>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Distribusi energi</h3><div class="sub">Menu hari {{ $hari }} · SD kelas 1–3</div></div></div>
    <div style="display:flex;align-items:center;gap:14px">
      @php
        $circ = 2 * M_PI * 54;
        $khD = $dist['kh'] / 100 * $circ;
        $lD  = $dist['lemak'] / 100 * $circ;
        $pD  = $dist['protein'] / 100 * $circ;
      @endphp
      <svg viewBox="0 0 180 180" style="width:168px;height:168px;flex:none">
        <circle cx="90" cy="90" r="54" fill="none" stroke="#F1EEFB" stroke-width="16"/>
        <g transform="rotate(-90 90 90)">
          <circle cx="90" cy="90" r="54" fill="none" stroke="#7B4DFF" stroke-width="16" stroke-dasharray="{{ $khD }} {{ $circ }}"/>
          <circle cx="90" cy="90" r="54" fill="none" stroke="#22A6FF" stroke-width="16" stroke-dasharray="{{ $lD }} {{ $circ }}" stroke-dashoffset="-{{ $khD }}"/>
          <circle cx="90" cy="90" r="54" fill="none" stroke="#FFB020" stroke-width="16" stroke-dasharray="{{ $pD }} {{ $circ }}" stroke-dashoffset="-{{ $khD + $lD }}"/>
        </g>
        <text x="90" y="86" text-anchor="middle" style="font:800 24px Inter,Arial;fill:#0B1020">{{ round($c3['pct']['energi']) }}%</text>
        <text x="90" y="104" text-anchor="middle" style="font:10px Inter,Arial;fill:#78819C">capaian energi</text>
      </svg>
      <div style="flex:1">
        <div class="item"><span class="dotc" style="background:#7B4DFF"></span><div><b>Karbohidrat</b><small>{{ round($dist['kh']) }}% energi</small></div><div class="right"><span class="badge {{ $dist['kh'] >= 55 && $dist['kh'] <= 65 ? 'up' : 'warn' }}">{{ $dist['kh'] >= 55 && $dist['kh'] <= 65 ? '✓ ideal' : 'cek' }}</span></div></div>
        <div class="item"><span class="dotc" style="background:#22A6FF"></span><div><b>Lemak</b><small>{{ round($dist['lemak']) }}% energi</small></div><div class="right"><span class="badge {{ $dist['lemak'] >= 20 && $dist['lemak'] <= 30 ? 'up' : 'warn' }}">{{ $dist['lemak'] >= 20 && $dist['lemak'] <= 30 ? '✓ ideal' : 'cek' }}</span></div></div>
        <div class="item"><span class="dotc" style="background:#FFB020"></span><div><b>Protein</b><small>{{ round($dist['protein']) }}% energi</small></div><div class="right"><span class="badge {{ $dist['protein'] >= 15 && $dist['protein'] <= 20 ? 'up' : 'warn' }}">{{ $dist['protein'] >= 15 && $dist['protein'] <= 20 ? '✓ ideal' : 'cek' }}</span></div></div>
      </div>
    </div>
    <div class="footnote">Protein {{ number_format($c3['total']['protein'], 1, ',', '.') }} g ({{ round($c3['pct']['protein']) }}% target 30% AKG) — menu dirancang 40–50% AKG untuk pencegahan stunting.</div>
  </div>
</div>

<div class="grid g-1-1-1" style="margin-top:14px">
  <div class="card">
    <div class="card-head"><div><h3>Siklus menu {{ $siklus->jumlah_hari }} hari</h3><div class="sub">13 kategori memakai menu induk yang sama</div></div><div class="right"><a class="link" href="/siklus">Lihat semua →</a></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Hari</th><th>Menu</th><th class="num">Energi</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($menu as $m)
          @php $w = ['#FF5C6C','#FFB020','#12B981','#7B4DFF'][$loop->index % 4]; @endphp
          <tr>
            <td class="strong">{{ $m->hari }}</td>
            <td><span class="tbl-name"><i class="dotc" style="background:{{ $w }}"></i>{{ $m->nama }}</span></td>
            <td class="num">{{ round($m->energi) }} kkal</td>
            <td><span class="chip {{ $m->hari == $hari ? 'violet' : 'green' }}">{{ $m->hari == $hari ? 'Berjalan' : 'Disetujui' }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Belanja per kategori</h3><div class="sub">Hari {{ $hari }} · 100 porsi per kategori</div></div></div>
    @php $maxB = collect($belanja['rows'])->max('belanja') ?: 1; @endphp
    @foreach(collect($belanja['rows'])->sortByDesc('belanja')->take(6) as $r)
      <div style="margin-top:9px">{{ $r['kategori'] }} — Rp {{ number_format($r['belanja']/1000, 0, ',', '.') }} rb
        <div class="bar"><i style="width:{{ max(4, round($r['belanja'] / $maxB * 100)) }}%;background:#7B4DFF"></i></div>
      </div>
    @endforeach
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Stok perlu perhatian</h3><div class="sub">{{ $stokKritis->count() }} item di bawah minimum</div></div><div class="right"><a class="link" href="/stok">PO →</a></div></div>
    <div class="list">
      @forelse($stokKritis as $s)
        <div class="item">
          <div class="thumb">{{ strtoupper(substr($s->nama, 0, 1)) }}</div>
          <div><b>{{ $s->nama }}</b><small>{{ rtrim(rtrim(number_format($s->stok, 2, ',', '.'), '0'), ',') }} {{ $s->satuan }} · min {{ rtrim(rtrim(number_format($s->minimum, 2, ',', '.'), '0'), ',') }}</small></div>
          <div class="right"><span class="badge {{ $s->stok <= 0 ? 'down' : 'warn' }}">{{ $s->stok <= 0 ? 'habis' : ($s->stok < $s->minimum ? 'kritis' : 'aman') }}</span></div>
        </div>
      @empty
        <div class="empty">Semua stok di atas minimum.</div>
      @endforelse
    </div>
  </div>
</div>

<div class="banner">
  <div style="width:36px;height:36px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;border:1px solid #E0DAFF">
    <i class="fa-solid fa-download" style="font-size:15px;color:#7B4DFF"></i>
  </div>
  <div>
    <div class="big">{{ $pmTerpicu ? count($pmTerpicu).' PM alergi terpicu hari ini' : 'Tidak ada alergi terpicu hari ini' }}</div>
    <p>{{ $pmTerpicu ? 'Menu pengganti perlu diisi sebelum laporan diunduh.' : 'Menu hari ini aman untuk seluruh PM terdaftar.' }}</p>
  </div>
  <div class="right"><a class="btn" href="/alergi">Buka deteksi</a></div>
</div>

@endsection
