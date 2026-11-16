@extends('layouts.shell')
@section('title','Kebutuhan Bahan')
@section('content')
@php $rp = fn ($v) => number_format((float) $v, 0, ',', '.'); @endphp

<div class="page-top">
  <div><h1>Kebutuhan Bahan</h1><p>{{ $kat->nama }} · {{ $porsi }} porsi · hari {{ $hari }} · buffer {{ (int) (DB::table('pengaturan')->where('kunci','buffer_belanja')->value('nilai') ?? 10) }}%</p></div>
  <div class="actions"><a class="btn ghost" href="/export/kebutuhan.xlsx?kategori={{ $kat->id }}&hari={{ $hari }}&porsi={{ $porsi }}">Excel</a><a class="btn" href="/export/kebutuhan.pdf?kategori={{ $kat->id }}&hari={{ $hari }}&porsi={{ $porsi }}">PDF</a></div>
</div>

<form method="get" action="/kebutuhan" class="toolbar">
  <select name="kategori" onchange="this.form.submit()">
    @foreach($kategori as $k)<option value="{{ $k->id }}" @selected($k->id == $kat->id)>{{ $k->nama }}</option>@endforeach
  </select>
  <select name="hari" onchange="this.form.submit()">
    @for($h = 1; $h <= 12; $h++)<option value="{{ $h }}" @selected($h == $hari)>Hari {{ $h }}</option>@endfor
  </select>
  <select name="porsi" onchange="this.form.submit()">
    @foreach([100, 300, 500, 1000] as $p)<option value="{{ $p }}" @selected($p == $porsi)>{{ $p }} porsi</option>@endforeach
  </select>
</form>

<div class="kpis">
  <div class="kpi" style="--tint:var(--skybg);--accent:var(--sky)"><div class="top"><span class="lbl">Pagu {{ $porsi }} porsi</span></div><div class="val">Rp {{ $rp($pagu) }}</div><div class="meta">Rp 10.000 per porsi</div><div class="ico-r"><i class="fa-solid fa-money-bill-wave"></i></div></div>
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)"><div class="top"><span class="lbl">Total belanja</span></div><div class="val">Rp {{ $rp($keb['total']) }}</div><div class="meta">{{ count($keb['rows']) }} bahan · sudah +buffer</div><div class="ico-r"><i class="fa-solid fa-cart-shopping"></i></div></div>
  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)"><div class="top"><span class="lbl">Sisa pagu</span></div><div class="val">Rp {{ $rp(max($pagu - $keb['total'], 0)) }}</div><div class="meta">{{ $pagu > 0 ? round((1 - $keb['total'] / $pagu) * 100) : 0 }}% tersedia</div><div class="ico-r"><i class="fa-solid fa-piggy-bank"></i></div></div>
  <div class="kpi" style="--tint:var(--lemon);--accent:var(--amber)"><div class="top"><span class="lbl">Penerima terdaftar</span></div><div class="val">{{ $rp($penerima['total']) }}</div><div class="meta">{{ $penerima['per_kategori']->count() }} kategori</div><div class="ico-r"><i class="fa-solid fa-users"></i></div></div>
</div>

<div class="grid g-2-1">
  <div class="card">
    <div class="card-head"><div><h3>Belanja bahan</h3><div class="sub">Nasi→beras ÷2,6 · koreksi BDD · buffer</div></div><div class="right"><span class="chip violet">{{ $porsi }} porsi</span></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Bahan</th><th class="num">GRAM/PORSI</th><th class="num">Kebutuhan</th><th class="num">Harga satuan</th><th class="num">HARGA/PORSI</th><th class="num">Subtotal</th></tr></thead>
      <tbody>
        @foreach($keb['rows'] as $r)
          <tr>
            <td>{{ $r['nama'] }}</td>
            <td class="num">{{ number_format($r['gram_porsi'], 1, ',', '.') }} g</td>
            <td class="num">{{ number_format($r['qty'], 2, ',', '.') }} {{ $r['satuan'] }}</td>
            <td class="num">Rp {{ $rp($r['harga']) }}</td>
            <td class="num">Rp {{ number_format($r['harga_porsi'], 0, ',', '.') }}</td>
            <td class="num">Rp {{ $rp($r['subtotal']) }}</td>
          </tr>
        @endforeach
        <tr style="background:#F7F4FF"><td colspan="5"><b>Total belanja</b></td><td class="num"><b>Rp {{ $rp($keb['total']) }}</b></td></tr>
      </tbody>
    </table></div>
    <div class="footnote">Harga/porsi = harga satuan × gram per porsi (termasuk koreksi BDD dan buffer belanja).</div>
    <div class="alert {{ $keb['total'] <= $pagu ? 'ok' : 'bad' }}" style="margin-top:12px">
      {{ $keb['total'] <= $pagu ? '✓' : '⚠' }} Total belanja {{ $pagu > 0 ? round($keb['total'] / $pagu * 100) : 0 }}% dari pagu.
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><div><h3>Pagu vs belanja</h3><div class="sub">Semua kategori</div></div></div>
      <div>Hari {{ $hari }} — Rp {{ number_format($semua['total'] / 1000000, 2, ',', '.') }} jt
        <div class="bar"><i style="width:{{ min(100, $semua['total'] / max($pagu * 13, 1) * 100) }}%;background:#12B981"></i></div>
      </div>
      <div class="footnote">Total dihitung dari jumlah penerima per kategori (fallback 100 porsi bila kosong).</div>
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Belanja per kategori</h3><div class="sub">Hari {{ $hari }}</div></div></div>
      @php $maxB = collect($semua['rows'])->max('belanja') ?: 1; @endphp
      @foreach(collect($semua['rows'])->sortByDesc('belanja')->take(6) as $r)
        <div style="margin-top:9px">{{ $r['kategori'] }} <span class="muted">· {{ $r['porsi'] }} porsi · Rp {{ number_format($r['belanja'] / 1000, 0, ',', '.') }} rb</span>
          <div class="bar"><i style="width:{{ max(4, round($r['belanja'] / $maxB * 100)) }}%;background:#7B4DFF"></i></div>
        </div>
      @endforeach
    </div>
  </div>
</div>

@endsection
