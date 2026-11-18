@extends('layouts.shell')
@section('title','Master Bahan')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Master Bahan</h1><p>{{ number_format($total, 0, ',', '.') }} bahan · nilai per 100 g BDD · impor TKPI bahan mentah</p></div>
  <div class="actions"><a class="btn ghost" href="/sumber">Data sumber</a><button class="btn" onclick="document.getElementById('form-bahan').scrollIntoView({behavior:'smooth'})">+ Tambah bahan</button></div>
</div>

<form method="get" action="/master-bahan" class="toolbar">
  <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari bahan..." style="min-width:240px">
  <select name="kelompok" onchange="this.form.submit()">
    <option value="">Semua kelompok</option>
    @foreach($kelompokList as $k)<option value="{{ $k }}" @selected(request('kelompok') === $k)>{{ $k }}</option>@endforeach
  </select>
  <button class="btn ghost sm">Cari</button>
  <div class="grow"></div>
  <span class="chip violet">{{ $bahan->total() }} hasil</span>
</form>

<div class="card">
  <div class="table-wrap"><table>
    <thead><tr><th>Bahan</th><th>Kelompok</th><th class="num">Energi</th><th class="num">Protein</th><th class="num">Lemak</th><th class="num">KH</th><th class="num">Serat</th><th class="num">Vit A</th><th class="num">Vit B</th><th class="num">BDD</th><th class="num">Harga</th><th></th></tr></thead>
    <tbody>
      @forelse($bahan as $b)
        @php $w = ['Pokok'=>'#C58A12','Hewani'=>'#FF5C6C','Nabati'=>'#7B4DFF','Sayur'=>'#12B981','Buah'=>'#FF63B0','Minyak'=>'#FFB020','Bumbu'=>'#78819C'][$b->kelompok] ?? '#78819C'; @endphp
        <tr style="box-shadow:inset 4px 0 0 {{ $w }}">
          <td class="strong">{{ $b->nama }} @if($b->mentah)<span class="chip green">mentah</span>@endif</td>
          <td>{{ $b->kelompok }}</td>
          <td class="num">{{ number_format($b->energi, 0, ',', '.') }}</td>
          <td class="num">{{ number_format($b->protein, 1, ',', '.') }}</td>
          <td class="num">{{ number_format($b->lemak, 1, ',', '.') }}</td>
          <td class="num">{{ number_format($b->kh, 1, ',', '.') }}</td>
          <td class="num">{{ number_format($b->serat, 1, ',', '.') }}</td>
          <td class="num">{{ number_format($b->vit_a, 1, ',', '.') }}</td>
          <td class="num">{{ number_format($b->vit_b, 2, ',', '.') }}</td>
          <td class="num">{{ $b->bdd }}</td>
          <td class="num">{{ $b->harga ? number_format($b->harga, 0, ',', '.') : '—' }}</td>
          <td><div class="act-row">
            <x-act type="view" :href="'/master-bahan/'.$b->id" />
            <x-act type="edit" :href="'/master-bahan/'.$b->id" fitur="master.tulis" />
            <x-act type="delete" :action="'/bahan/'.$b->id.'/hapus'" :disabled="$b->dipakai > 0" :title="$b->dipakai > 0 ? 'Dipakai di '.$b->dipakai.' baris menu' : 'Hapus'" confirm="Hapus bahan ini? Riwayat periodenya ikut terhapus." fitur="master.tulis" />
          </div></td>
        </tr>
      @empty
        <tr><td colspan="12" class="empty">Tidak ada bahan yang cocok.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  <div style="margin-top:12px">
    @if($bahan->hasPages())
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        @if(!$bahan->onFirstPage())
          <a class="btn ghost sm" href="{{ $bahan->previousPageUrl() }}">← Sebelumnya</a>
        @endif
        <span class="muted" style="font-size:11.5px">Halaman {{ $bahan->currentPage() }} dari {{ $bahan->lastPage() }}</span>
        @if($bahan->hasMorePages())
          <a class="btn ghost sm" href="{{ $bahan->nextPageUrl() }}">Berikutnya →</a>
        @endif
      </div>
    @endif
  </div>
</div>

<div class="card" id="form-bahan" style="margin-top:14px">
  <div class="card-head"><div><h3>Tambah bahan</h3><div class="sub">Nilai per 100 g bagian dapat dimakan</div></div></div>
  <form method="post" action="/bahan">
    @csrf
    <div class="formgrid">
      <label class="field">Nama<input name="nama" required></label>
      <label class="field">Kelompok
        <select name="kelompok">@foreach(['Pokok','Hewani','Nabati','Sayur','Buah','Minyak','Bumbu','Lainnya'] as $k)<option>{{ $k }}</option>@endforeach</select>
      </label>
      <label class="field">Energi (kkal)<input type="number" step="0.1" name="energi" required></label>
      <label class="field">Protein (g)<input type="number" step="0.1" name="protein" required></label>
      <label class="field">Lemak (g)<input type="number" step="0.1" name="lemak" required></label>
      <label class="field">KH (g)<input type="number" step="0.1" name="kh" required></label>
      <label class="field">Serat (g)<input type="number" step="0.1" name="serat" value="0"></label>
      <label class="field">Vitamin A (µg)<input type="number" step="0.1" name="vit_a" value="0"></label>
      <label class="field">Vitamin B1 (mg)<input type="number" step="0.01" name="vit_b" value="0"></label>
      <label class="field">Natrium (mg)<input type="number" step="0.1" name="natrium" value="0"></label>
      <label class="field">BDD (%)<input type="number" name="bdd" value="100" min="1" max="100"></label>
      <label class="field">Harga<input type="number" name="harga" value="0"></label>
    </div>
    <button class="btn" style="margin-top:12px">Simpan bahan</button>
  </form>
</div>

@endsection
