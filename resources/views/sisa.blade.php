@extends('layouts.shell')
@section('title','Sisa Makanan')
@section('content')
@php
  $num = fn ($v) => number_format((float) $v, 1, ',', '.');
  $warna = ['Karbohidrat'=>'#C58A12','Protein hewani'=>'#FF5C6C','Protein nabati'=>'#7B4DFF','Sayur'=>'#12B981','Buah'=>'#FF63B0','Lainnya'=>'#78819C'];
@endphp

<div class="page-top">
  <div><h1>Sisa Makanan</h1><p>Catat sisa per kelompok pangan · {{ now()->translatedFormat('l, j F Y') }}</p></div>
  <div class="actions"><div class="seg"><a class="on" href="#">Hari ini</a><a href="#">14 hari</a><a href="#">30 hari</a></div><button class="btn ghost">Unduh laporan</button></div>
</div>

<div class="kpis">
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)"><div class="top"><span class="lbl">Total sisa</span></div><div class="val">{{ $num($totalSisa) }} <small>kg</small></div><div class="meta">dari {{ $num($totalSaji) }} kg disajikan</div></div>
  <div class="kpi" style="--tint:var(--lemon);--accent:var(--amber)"><div class="top"><span class="lbl">Persentase sisa</span></div><div class="val">{{ number_format($pct, 1, ',', '.') }}%</div><div class="meta">target di bawah 5%</div></div>
  @php $max = $rows->sortByDesc('sisa')->first(); @endphp
  <div class="kpi" style="--tint:var(--rosebg);--accent:var(--rose)"><div class="top"><span class="lbl">Sisa tertinggi</span></div><div class="val">{{ $max->kelompok ?? '—' }}</div><div class="meta">{{ $max ? $num($max->sisa).' kg' : '—' }}</div></div>
  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)"><div class="top"><span class="lbl">Kelompok dicatat</span></div><div class="val">{{ $rows->count() }}</div><div class="meta">dari 6 kelompok pangan</div></div>
</div>

<div class="card">
  <div class="card-head"><div><h3>Sisa per kelompok pangan</h3><div class="sub">Ditimbang setelah layanan</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Kelompok</th><th class="num">Disajikan</th><th class="num">Sisa (kg)</th><th class="num">Persen</th><th>Grafik</th></tr></thead>
    <tbody>
      @foreach($rows as $r)
        @php $p = $r->disajikan > 0 ? $r->sisa / $r->disajikan * 100 : 0; $w = $warna[$r->kelompok] ?? '#78819C'; @endphp
        <tr>
          <td><span class="tbl-name"><i class="dotc" style="background:{{ $w }}"></i>{{ $r->kelompok }}</span></td>
          <td class="num">{{ $num($r->disajikan) }}</td>
          <td class="num">{{ $num($r->sisa) }}</td>
          <td class="num">{{ number_format($p, 1, ',', '.') }}%</td>
          <td><div class="bar" style="width:110px"><i style="width:{{ min(100, $p * 6) }}%;background:{{ $w }}"></i></div></td>
        </tr>
      @endforeach
      <tr style="background:#F7F4FF"><td><b>Total</b></td><td class="num"><b>{{ $num($totalSaji) }}</b></td><td class="num"><b>{{ $num($totalSisa) }}</b></td><td class="num"><b>{{ number_format($pct, 1, ',', '.') }}%</b></td><td></td></tr>
    </tbody>
  </table></div>
  <div class="footnote">Sisa ditimbang setelah layanan. “Habis” dihitung 0 kg, bukan tidak dicatat.</div>
</div>

@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Catat sisa</h3><div class="sub">Menimpa baris kelompok yang sama pada tanggal itu</div></div></div>
  <form method="post" action="/sisa" data-offline>
    @csrf
    <div class="formgrid">
      <label class="field">Tanggal<input type="date" name="tanggal" value="{{ now()->toDateString() }}"></label>
      <label class="field">Kelompok
        <select name="kelompok">
          @foreach(['Karbohidrat','Protein hewani','Protein nabati','Sayur','Buah','Lainnya'] as $k)<option>{{ $k }}</option>@endforeach
        </select>
      </label>
      <label class="field">Disajikan (kg)<input type="number" step="0.1" name="disajikan" value="0" required></label>
      <label class="field">Sisa (kg)<input type="number" step="0.1" name="sisa" value="0" required></label>
      <div class="field" style="justify-content:flex-end"><button class="btn" style="justify-content:center">Simpan sisa</button></div>
    </div>
  </form>
</div>

@endsection
