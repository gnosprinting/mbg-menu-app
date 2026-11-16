@extends('layouts.shell')
@section('title','Master Penerima')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Master Penerima Manfaat</h1><p>Jumlah penerima per lokasi dan kategori · tanpa porsi kecil/besar</p></div>
  <div class="actions"><button class="btn" onclick="document.getElementById('form-pm').scrollIntoView({behavior:'smooth'})">+ Tambah penerima</button></div>
</div>

<div class="kpis">
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)"><div class="top"><span class="lbl">Lokasi</span></div><div class="val">{{ $lokasi->count() }}</div><div class="meta">{{ $lokasi->where('jenis','sekolah')->count() }} sekolah · {{ $lokasi->where('jenis','posyandu')->count() }} posyandu</div><div class="ico-r"><i class="fa-solid fa-location-dot"></i></div></div>
  <div class="kpi" style="--tint:var(--skybg);--accent:var(--sky)"><div class="top"><span class="lbl">Total penerima</span></div><div class="val">{{ number_format($total['total'], 0, ',', '.') }}</div><div class="meta">{{ $total['per_kategori']->count() }} kategori terpakai</div><div class="ico-r"><i class="fa-solid fa-users"></i></div></div>
  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)"><div class="top"><span class="lbl">Kategori</span></div><div class="val">{{ $kategori->count() }}</div><div class="meta">master kategori</div><div class="ico-r"><i class="fa-solid fa-layer-group"></i></div></div>
  <div class="kpi" style="--tint:var(--lemon);--accent:var(--amber)"><div class="top"><span class="lbl">PM alergi</span></div><div class="val">{{ $lokasi->sum('alergi') }}</div><div class="meta">perlu dicek harian</div><div class="ico-r"><i class="fa-solid fa-triangle-exclamation"></i></div></div>
</div>

<div class="card">
  <div class="card-head"><div><h3>Daftar lokasi &amp; penerima</h3><div class="sub">Setiap baris = satu kategori pada satu lokasi</div></div><div class="right"><span class="chip violet">{{ $lokasi->count() }} lokasi</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Lokasi</th><th>Kategori PM</th><th class="num">Jumlah</th><th class="num">PM alergi</th><th>Aksi</th></tr></thead>
    <tbody>
      @foreach($lokasi as $l)
        <tr style="background:#FAFBFF">
          <td class="strong" rowspan="{{ max($l->baris->count(),1) + 1 }}">{{ $l->nama }}<div class="muted" style="font-weight:400">{{ ucfirst($l->jenis) }} · pagu Rp {{ number_format($l->pagu_harian, 0, ',', '.') }}</div></td>
          <td colspan="4" class="muted">Total {{ number_format($l->total, 0, ',', '.') }} porsi · {{ $l->baris->count() }} kategori</td>
        </tr>
        @forelse($l->baris as $b)
          <tr>
            <td>{{ $b->kategori }}</td>
            <td class="num">{{ number_format($b->jumlah, 0, ',', '.') }}</td>
            <td class="num">{{ $l->alergi }}</td>
            <td><div class="act-row">
              <x-act type="view" :href="'/master-penerima/'.$b->id" />
              <x-act type="edit" :href="'/master-penerima/'.$b->id" />
              <x-act type="delete" :action="'/penerima-kategori/'.$b->id.'/hapus'" confirm="Hapus baris penerima ini?" />
            </div></td>
          </tr>
        @empty
          <tr><td colspan="4" class="empty">Belum ada penerima.</td></tr>
        @endforelse
      @endforeach
    </tbody>
  </table></div>
  <div class="footnote">Hapus hanya berhasil bila tidak ada PM alergi yang terkait pada lokasi &amp; kategori tersebut.</div>
</div>

<div class="card" id="form-pm" style="margin-top:14px">
  <div class="card-head"><div><h3>Tambah / ubah penerima</h3><div class="sub">Kombinasi lokasi + kategori yang sudah ada akan diperbarui</div></div></div>
  <form method="post" action="/penerima-kategori">
    @csrf
    <div class="formgrid">
      <label class="field">Lokasi
        <select name="lokasi_id" required>
          @foreach($lokasi as $l)<option value="{{ $l->id }}">{{ $l->nama }}</option>@endforeach
        </select>
      </label>
      <label class="field">Kategori PM
        <select name="kategori_id" required>
          @foreach($kategori as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
        </select>
      </label>
      <label class="field">Jumlah<input type="number" name="jumlah" min="0" value="0" required></label>
      <div class="field" style="justify-content:flex-end"><button class="btn" style="justify-content:center">Simpan</button></div>
    </div>
  </form>
</div>

@endsection
