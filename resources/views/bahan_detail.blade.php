@extends('layouts.shell')
@section('title','Detail Bahan')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif

<div class="page-top">
  <div><h1>Detail Bahan</h1><p>{{ $b->nama }} · {{ $b->kelompok }} · {{ $b->sumber }}</p></div>
  <div class="actions"><a class="btn ghost" href="/master-bahan">← Kembali</a></div>
</div>

<div class="grid g-2-1">
  <div class="card">
    <div class="card-head"><div><h3>Ubah data</h3><div class="sub">Nilai per 100 g BDD</div></div></div>
    <form method="post" action="/bahan/{{ $b->id }}">
      @csrf
      <div class="formgrid">
        <label class="field">Nama<input name="nama" value="{{ $b->nama }}" required></label>
        <label class="field">Kelompok
          <select name="kelompok">@foreach(['Pokok','Hewani','Nabati','Sayur','Buah','Minyak','Bumbu','Lainnya'] as $k)<option @selected($b->kelompok===$k)>{{ $k }}</option>@endforeach</select>
        </label>
        <label class="field">Energi (kkal)<input type="number" step="0.1" name="energi" value="{{ $b->energi }}" required></label>
        <label class="field">Protein (g)<input type="number" step="0.1" name="protein" value="{{ $b->protein }}" required></label>
        <label class="field">Lemak (g)<input type="number" step="0.1" name="lemak" value="{{ $b->lemak }}" required></label>
        <label class="field">KH (g)<input type="number" step="0.1" name="kh" value="{{ $b->kh }}" required></label>
        <label class="field">Serat (g)<input type="number" step="0.1" name="serat" value="{{ $b->serat }}"></label>
        <label class="field">Vitamin A (µg)<input type="number" step="0.1" name="vit_a" value="{{ $b->vit_a }}"></label>
        <label class="field">Vitamin B1 (mg)<input type="number" step="0.01" name="vit_b" value="{{ $b->vit_b }}"></label>
        <label class="field">Natrium (mg)<input type="number" step="0.1" name="natrium" value="{{ $b->natrium }}"></label>
        <label class="field">BDD (%)<input type="number" name="bdd" value="{{ $b->bdd }}" min="1" max="100" required></label>
        <label class="field">Harga (Rp/{{ $b->satuan }})<input type="number" name="harga" value="{{ $b->harga }}"></label>
        <label class="field">Satuan<input name="satuan" value="{{ $b->satuan }}"></label>
        <label class="field">SP (DBMP)<input name="sp_label" value="{{ $b->sp_label }}"></label>
        <label class="field">URT<input name="urt_label" value="{{ $b->urt_label }}"></label>
      </div>
      <div class="footnote">Harga terisi otomatis dari periode aktif dan menjadi harga default periode berjalan. Mengubah harga/nilai gizi membuka periode baru; menu lama tetap memakai periode sebelumnya.</div>
      <button class="btn" style="margin-top:12px">Simpan perubahan</button>
    </form>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><div><h3>Ringkasan</h3><div class="sub">Dipakai di menu</div></div></div>
      <div class="list">
        <div class="item"><div><b>Dipakai pada menu</b><small>baris resep</small></div><div class="right"><span class="badge info">{{ $dipakai }}</span></div></div>
        <div class="item"><div><b>Sumber</b><small>asal nilai gizi</small></div><div class="right"><span class="chip violet">{{ $b->sumber }}</span></div></div>
        <div class="item"><div><b>Status bahan</b><small>mentah / olahan</small></div><div class="right"><span class="chip {{ $b->mentah ? 'green' : 'orange' }}">{{ $b->mentah ? 'mentah' : 'olahan' }}</span></div></div>
        <div class="item"><div><b>Periode aktif</b><small>{{ $riwayat->whereNull('berlaku_sampai')->count() }} periode berjalan</small></div><div class="right"><span class="chip sky">{{ $riwayat->count() }} periode</span></div></div>
      </div>
      @if($alergen->count())
        <div class="footnote">Alergen: @foreach($alergen as $a)<span class="chip red">{{ $a }}</span> @endforeach</div>
      @endif
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Hapus bahan</h3><div class="sub">Hanya bila belum terafiliasi</div></div></div>
      <p style="font-size:11.6px;color:#39405A">Dipakai di {{ $dipakai }} baris menu dan {{ $alergen->count() }} relasi alergen.</p>
      <form method="post" action="/bahan/{{ $b->id }}/hapus" onsubmit="return confirm('Hapus bahan ini beserta riwayat periodenya?')">
        @csrf
        <button class="act act-del" title="Hapus bahan" aria-label="Hapus bahan" @if($dipakai > 0 || $alergen->count() > 0) disabled @endif><i class="fa-solid fa-trash-can"></i></button>
      </form>
    </div>
  </div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Riwayat periode</h3><div class="sub">Menu menunjuk periode saat menu dibuat — data lama tidak berubah</div></div><div class="right"><span class="chip violet">{{ $riwayat->count() }} periode</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Berlaku dari</th><th>Berlaku sampai</th><th class="num">Energi</th><th class="num">Protein</th><th class="num">Harga</th><th>Status</th></tr></thead>
    <tbody>
      @foreach($riwayat as $v)
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($v->berlaku_dari)->format('d M Y') }}</td>
          <td>{{ $v->berlaku_sampai ? \Illuminate\Support\Carbon::parse($v->berlaku_sampai)->format('d M Y') : '—' }}</td>
          <td class="num">{{ number_format($v->energi, 1, ',', '.') }} kkal</td>
          <td class="num">{{ number_format($v->protein, 1, ',', '.') }} g</td>
          <td class="num">Rp {{ number_format($v->harga, 0, ',', '.') }}</td>
          <td>@if($v->berlaku_sampai)<span class="chip orange">arsip</span>@else<span class="chip green">aktif</span>@endif</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="footnote">Dipakai di {{ $terpakaiMenu->count() }} baris menu. Contoh: hari {{ $terpakaiMenu->first()->hari ?? '—' }} — {{ $terpakaiMenu->first()->menu ?? 'belum dipakai' }}.</div>
</div>

@endsection
