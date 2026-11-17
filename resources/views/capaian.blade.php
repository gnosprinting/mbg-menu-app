@extends('layouts.shell')
@section('title','Capaian')
@section('content')
@php
  $fmt = fn ($v, $d = 1) => number_format((float) $v, $d, ',', '.');
  $warna = ['pokok'=>'#C58A12','hewani'=>'#FF5C6C','nabati'=>'#7B4DFF','sayur'=>'#12B981','buah'=>'#FF63B0','minyak'=>'#FFB020','bumbu'=>'#78819C'];
  $label = ['pokok'=>'Makanan pokok','hewani'=>'Lauk hewani','nabati'=>'Lauk nabati','sayur'=>'Sayuran','buah'=>'Buah','minyak'=>'Minyak/lemak','bumbu'=>'Bumbu'];
  $kelompok = collect($data['rows'])->groupBy('peran');
@endphp

<div class="page-top">
  <div><h1>Capaian</h1><p>Kebijakan MBG 25–30% AKG · {{ $kat->nama }} · hari {{ $hari }}</p></div>
  <div class="actions">
    <form method="get" action="/capaian" style="display:flex;gap:8px">
      <select name="kategori" onchange="this.form.submit()" style="border:1px solid var(--line);border-radius:10px;padding:7px 10px;font:inherit;font-size:11.8px;background:#fff">
        @foreach($kategori as $k)
          <option value="{{ $k->id }}" @selected($k->id == $kat->id)>{{ $k->nama }}</option>
        @endforeach
      </select>
      <select name="hari" onchange="this.form.submit()" style="border:1px solid var(--line);border-radius:10px;padding:7px 10px;font:inherit;font-size:11.8px;background:#fff">
        @for($h = 1; $h <= $jumlah; $h++)<option value="{{ $h }}" @selected($h == $hari)>Hari {{ $h }}</option>@endfor
      </select>
    </form>
    <a class="btn" href="/export/capaian.pdf?kategori={{ $kat->id }}&hari={{ $hari }}">Unduh laporan</a>
  </div>
</div>

<div class="kpis">
  @foreach([['Energi','energi','kkal','fa-bolt'],['Protein','protein','g','fa-drumstick-bite'],['Lemak','lemak','g','fa-droplet'],['Karbohidrat','kh','g','fa-wheat-awn']] as [$lbl,$key,$unit,$ico])
    @php $pct = $data['pct'][$key]; $tint = $pct > 130 ? 'var(--skybg)' : ($pct < 90 ? 'var(--lemon)' : 'var(--mint)'); $acc = $pct > 130 ? 'var(--sky)' : ($pct < 90 ? 'var(--amber)' : 'var(--emerald)'); @endphp
    <div class="kpi" style="--tint:{{ $tint }};--accent:{{ $acc }}">
      <div class="top"><span class="lbl">{{ $lbl }}</span></div>
      <div class="val">{{ round($pct) }}%</div>
      <div class="meta">{{ $fmt($data['total'][$key], $key === 'energi' ? 0 : 1) }} / {{ $fmt($data['target'][$key], $key === 'energi' ? 0 : 1) }} {{ $unit }}</div>
      <div class="ico-r"><i class="fa-solid {{ $ico }}"></i></div>
    </div>
  @endforeach
</div>

<div class="grid g-3">
  <div class="card">
    <div class="card-head"><div><h3>Tabel 1 · Komposisi kelompok</h3><div class="sub">Gram dan energi per kelompok</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Kelompok</th><th class="num">Gram</th><th class="num">Energi</th><th class="num">Protein</th></tr></thead>
      <tbody>
        @foreach($label as $peran => $nama)
          @php $g = $kelompok->get($peran, collect()); @endphp
          <tr>
            <td><span class="tbl-name"><i class="dotc" style="background:{{ $warna[$peran] }}"></i>{{ $nama }}</span></td>
            <td class="num">{{ $fmt($g->sum('gram')) }}</td>
            <td class="num">{{ $fmt($g->sum('energi'), 0) }}</td>
            <td class="num">{{ $fmt($g->sum('protein')) }}</td>
          </tr>
        @endforeach
        <tr style="background:#F7F4FF"><td><b>Total</b></td><td class="num"><b>{{ $fmt($data['total']['gram']) }}</b></td><td class="num"><b>{{ $fmt($data['total']['energi'], 0) }}</b></td><td class="num"><b>{{ $fmt($data['total']['protein']) }}</b></td></tr>
      </tbody>
    </table></div>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Tabel 2 · Kecukupan 30% AKG</h3><div class="sub">AKG 2019 · {{ $kat->nama }}</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Zat gizi</th><th class="num">AKG/hari</th><th class="num">30%</th></tr></thead>
      <tbody>
        <tr><td>Energi</td><td class="num">{{ $fmt($data['target']['akg_energi'], 0) }} kkal</td><td class="num">{{ $fmt($data['target']['energi'], 0) }}</td></tr>
        <tr><td>Protein</td><td class="num">{{ $fmt($data['target']['akg_protein']) }} g</td><td class="num">{{ $fmt($data['target']['protein']) }}</td></tr>
        <tr><td>Lemak</td><td class="num">{{ $fmt($data['target']['akg_lemak']) }} g</td><td class="num">{{ $fmt($data['target']['lemak']) }}</td></tr>
        <tr><td>Karbohidrat</td><td class="num">{{ $fmt($data['target']['akg_kh']) }} g</td><td class="num">{{ $fmt($data['target']['kh']) }}</td></tr>
      </tbody>
    </table></div>
    <div class="alert info" style="margin-top:12px">Kebijakan 25–30% AKG; aplikasi memakai 30% sebagai target dan menandai hasil di luar 90–115%.</div>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Tabel 3 · Capaian</h3><div class="sub">Target, hasil, persentase</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Zat gizi</th><th class="num">Target</th><th class="num">Hasil</th><th class="num">%</th></tr></thead>
      <tbody>
        @foreach([['Energi','energi',0],['Protein','protein',1],['Lemak','lemak',1],['Karbohidrat','kh',1]] as [$lbl,$key,$d])
          @php $pct = $data['pct'][$key]; $cls = $pct > 130 ? 'sky' : ($pct < 90 ? 'orange' : 'green'); @endphp
          <tr><td>{{ $lbl }}</td><td class="num">{{ $fmt($data['target'][$key], $d) }}</td><td class="num">{{ $fmt($data['total'][$key], $d) }}</td><td class="num"><span class="chip {{ $cls }}">{{ round($pct) }}</span></td></tr>
        @endforeach
      </tbody>
    </table></div>
    <div class="footnote">Protein di atas 130% bukan kesalahan: menu dirancang 40–50% AKG protein. Yang dinilai ketat adalah distribusi energi.</div>
  </div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Status per kategori</h3><div class="sub">Hari {{ $hari }} · seluruh kategori</div></div>
    <div class="right"><span class="chip green">{{ $semua->where('status','aman')->count() }} dari {{ $semua->count() }} dalam rentang</span></div>
  </div>
  <div class="table-wrap"><table>
    <thead><tr><th>Kategori</th><th class="num">Energi</th><th class="num">Capaian</th><th class="num">Protein</th><th>Status</th></tr></thead>
    <tbody>
      @foreach($semua as $k)
        <tr>
          <td>{{ $k->nama }}</td>
          <td class="num">{{ $fmt($k->energi, 0) }}</td>
          <td class="num">{{ round($k->pct) }}%</td>
          <td class="num">{{ $fmt($k->protein) }}</td>
          <td>
            @php $cls = ['aman'=>'green','waspada'=>'orange','kurang'=>'red','lebih'=>'sky','tinggi'=>'sky'][$k->status] ?? 'green'; @endphp
            <span class="chip {{ $cls }}">{{ ['aman'=>'✓ aman','waspada'=>'! waspada','kurang'=>'× kurang','lebih'=>'↑ lebih','tinggi'=>'↑ tinggi'][$k->status] ?? $k->status }}</span>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>

@endsection
