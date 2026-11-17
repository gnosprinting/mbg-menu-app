@extends('layouts.shell')
@section('title','Komposisi & Gizi')
@section('content')
@php
  $warna = ['Pokok'=>'#C58A12','Hewani'=>'#FF5C6C','Nabati'=>'#7B4DFF','Sayur'=>'#12B981','Buah'=>'#FF63B0','Minyak'=>'#FFB020','Bumbu'=>'#78819C'];
  $fmt = fn ($v, $d = 1) => number_format((float) $v, $d, ',', '.');
@endphp

<div class="page-top">
  <div><h1>Komposisi &amp; Gizi</h1><p>Tabel 9 · {{ $kat->nama }} · hari {{ $hari }} · {{ $menuNama }}</p></div>
  <div class="actions">
    <a class="btn ghost" href="/export/komposisi.xlsx?kategori={{ $kat->id }}&hari={{ $hari }}">Excel</a>
    <a class="btn" href="/export/komposisi.pdf?kategori={{ $kat->id }}&hari={{ $hari }}">PDF</a>
  </div>
</div>

<form method="get" action="/komposisi" class="toolbar">
  <select name="kategori" onchange="this.form.submit()">
    @foreach($kategori as $k)<option value="{{ $k->id }}" @selected($k->id == $kat->id)>{{ $k->nama }}</option>@endforeach
  </select>
  <select name="hari" onchange="this.form.submit()">
    @for($h = 1; $h <= 12; $h++)<option value="{{ $h }}" @selected($h == $hari)>Hari {{ $h }}</option>@endfor
  </select>
</form>

<div class="legend">
  @foreach(['Pokok','Hewani','Nabati','Sayur','Buah','Minyak'] as $g)
    <span class="lg"><i class="sw" style="background:{{ $warna[$g] }}"></i>{{ $g }}</span>
  @endforeach
</div>

<div class="grid g-2-1">
  <div class="card">
    <div class="table-wrap"><table>
      <thead><tr><th>Peran</th><th>SP</th><th>Bahan</th><th>URT</th><th class="num">Berat</th><th class="num">Energi</th><th class="num">Protein</th><th class="num">Lemak</th><th class="num">KH</th><th class="num">Serat</th><th class="num">Vit A</th><th class="num">Vit B</th><th class="num">Natrium</th></tr></thead>
      <tbody>
        @forelse($data['rows'] as $r)
          <tr style="box-shadow:inset 4px 0 0 {{ $warna[$r['kelompok']] ?? '#78819C' }}">
            @php
              $labelPeran = ['pokok'=>'Pokok','hewani'=>'Hewani','nabati'=>'Nabati','sayur'=>'Sayur','buah'=>'Buah','minyak'=>'Minyak','bumbu'=>'Bumbu'][$r['peran']] ?? ucfirst($r['peran']);
              $warnaPeran = $warna[$labelPeran] ?? '#78819C';
            @endphp
            <td><span class="chip" style="background:{{ $warnaPeran }}1A;color:{{ $warnaPeran }}">{{ $labelPeran }}</span></td>
            <td>{{ $r['sp'] ?? '—' }}</td>
            <td>{{ $r['nama'] }}</td>
            <td>{{ $r['urt'] ?? '—' }}</td>
            <td class="num">{{ $fmt($r['gram']) }}</td>
            <td class="num">{{ $fmt($r['energi'], 0) }}</td>
            <td class="num">{{ $fmt($r['protein']) }}</td>
            <td class="num">{{ $fmt($r['lemak']) }}</td>
            <td class="num">{{ $fmt($r['kh']) }}</td>
            <td class="num">{{ $fmt($r['serat'] ?? 0) }}</td>
            <td class="num">{{ $fmt($r['vit_a'] ?? 0) }}</td>
            <td class="num">{{ $fmt($r['vit_b'] ?? 0, 2) }}</td>
            <td class="num">{{ $fmt($r['natrium'], 0) }}</td>
          </tr>
        @empty
          <tr><td colspan="13" class="empty">Menu hari ini belum punya bahan.</td></tr>
        @endforelse
        <tr style="background:#F7F4FF">
          <td colspan="4"><b>Sub total</b></td>
          <td class="num"><b>{{ $fmt($data['total']['gram']) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['energi'], 0) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['protein']) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['lemak']) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['kh']) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['serat'] ?? 0) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['vit_a'] ?? 0) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['vit_b'] ?? 0, 2) }}</b></td>
          <td class="num"><b>{{ $fmt($data['total']['natrium'], 0) }}</b></td>
        </tr>
      </tbody>
    </table></div>
    <div class="footnote">Serat dalam gram, vitamin A dalam µg, vitamin B1 dalam mg. Nilai per 100 g BDD dari TKPI.</div>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><div><h3>Ringkasan menu</h3><div class="sub">Terhadap target {{ (int) (DB::table('pengaturan')->where('kunci','target_persen')->value('nilai') ?? 30) }}% AKG</div></div></div>
      <div class="list">
        @foreach([['Energi','energi','kkal',0],['Protein','protein','g',1],['Lemak','lemak','g',1],['Karbohidrat','kh','g',1]] as [$lbl,$key,$unit,$d])
          @php $pct = $data['pct'][$key]; $cls = $pct > 130 ? 'info' : ($pct < 90 ? 'warn' : 'up'); @endphp
          <div class="item">
            <div><b>{{ $lbl }}</b><small>target {{ $fmt($data['target'][$key], $d) }} {{ $unit }}</small></div>
            <div class="right"><span class="badge {{ $cls }}">{{ $fmt($data['total'][$key], $d) }} {{ $unit }} · {{ round($pct) }}%</span></div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Distribusi energi</h3></div></div>
      @php
        $e = max($data['total']['energi'], 1);
        $dp = $data['total']['protein'] * 4 / $e * 100;
        $dl = $data['total']['lemak'] * 9 / $e * 100;
        $dk = $data['total']['kh'] * 4 / $e * 100;
      @endphp
      <div>Protein {{ round($dp) }}%<div class="bar"><i style="width:{{ $dp }}%;background:#FFB020"></i></div></div>
      <div style="margin-top:10px">Lemak {{ round($dl) }}%<div class="bar"><i style="width:{{ $dl }}%;background:#22A6FF"></i></div></div>
      <div style="margin-top:10px">Karbohidrat {{ round($dk) }}%<div class="bar"><i style="width:{{ $dk }}%;background:#7B4DFF"></i></div></div>
      <div class="footnote">Rentang ideal 15–20% protein, 20–30% lemak, 55–65% karbohidrat.</div>
    </div>
  </div>
</div>

@endsection
