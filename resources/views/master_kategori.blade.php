@extends('layouts.shell')
@section('title','Master Kategori')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Master Kategori</h1><p>AKG, target 30%, dan profil gram per kategori penerima</p></div>
  <div class="actions"><button class="btn" onclick="document.getElementById('form-kat').scrollIntoView({behavior:'smooth'})">+ Tambah kategori</button></div>
</div>

<div class="card">
  <div class="card-head"><div><h3>Daftar kategori</h3><div class="sub">{{ $kategori->count() }} kategori · ikon pensil untuk menyunting, ikon sampah untuk menghapus</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Kode</th><th>Kategori</th><th class="num">AKG energi</th><th class="num">AKG protein</th><th class="num">Target energi</th><th class="num">Penerima</th><th>Gram (nasi/hewani/nabati/sayur/buah)</th><th>Aksi</th></tr></thead>
    <tbody>
      @foreach($kategori as $k)
        <tr>
          <td><span class="chip violet">{{ $k->kode }}</span></td>
          <td class="strong">{{ $k->nama }} @if($k->khusus)<span class="chip orange">khusus</span>@endif</td>
          <td class="num">{{ number_format($k->akg_energi, 0, ',', '.') }}</td>
          <td class="num">{{ number_format($k->akg_protein, 1, ',', '.') }}</td>
          <td class="num">{{ $k->target_energi ? number_format($k->target_energi, 0, ',', '.') : round($k->akg_energi * 0.3) }}</td>
          <td class="num">{{ number_format($k->penerima, 0, ',', '.') }}</td>
          <td style="white-space:nowrap">
            @if($k->profil)
              <span class="gchip" style="background:#FFF3D1;color:#8A6100">{{ rtrim(rtrim(number_format($k->profil->nasi, 0, ',', '.'), '0'), ',') }}</span>
              <span class="gchip" style="background:#FFE0E5;color:#C2273C">{{ rtrim(rtrim(number_format($k->profil->hewani, 0, ',', '.'), '0'), ',') }}</span>
              <span class="gchip" style="background:#F1EBFF;color:#5B2BE0">{{ rtrim(rtrim(number_format($k->profil->nabati, 0, ',', '.'), '0'), ',') }}</span>
              <span class="gchip" style="background:#E7FBF2;color:#0E8F63">{{ rtrim(rtrim(number_format($k->profil->sayur, 0, ',', '.'), '0'), ',') }}</span>
              <span class="gchip" style="background:#FFE9F4;color:#C0267B">{{ rtrim(rtrim(number_format($k->profil->buah, 0, ',', '.'), '0'), ',') }}</span>
            @else — @endif
          </td>
          <td>
            <div class="act-row" style="position:relative">
              <details class="act-pop">
                <summary class="act act-edit" title="Ubah" aria-label="Ubah"><i class="fa-solid fa-pen"></i></summary>
                <form method="post" action="/kategori/{{ $k->id }}" class="act-panel">
                  @csrf
                  <div class="formgrid" style="grid-template-columns:1fr 1fr;gap:6px">
                    <label class="field">Nama<input name="nama" value="{{ $k->nama }}" required></label>
                    <label class="field">AKG energi<input type="number" name="akg_energi" value="{{ $k->akg_energi }}" required></label>
                    <label class="field">AKG protein<input type="number" step="0.1" name="akg_protein" value="{{ $k->akg_protein }}" required></label>
                    <label class="field">Target energi<input type="number" step="0.1" name="target_energi" value="{{ $k->target_energi }}"></label>
                    <label class="field">Target protein<input type="number" step="0.1" name="target_protein" value="{{ $k->target_protein }}"></label>
                    <label class="field">Nasi (g)<input type="number" step="0.1" name="nasi" value="{{ $k->profil->nasi ?? 0 }}"></label>
                    <label class="field">Hewani (g)<input type="number" step="0.1" name="hewani" value="{{ $k->profil->hewani ?? 0 }}"></label>
                    <label class="field">Nabati (g)<input type="number" step="0.1" name="nabati" value="{{ $k->profil->nabati ?? 0 }}"></label>
                    <label class="field">Sayur (g)<input type="number" step="0.1" name="sayur" value="{{ $k->profil->sayur ?? 0 }}"></label>
                    <label class="field">Buah (g)<input type="number" step="0.1" name="buah" value="{{ $k->profil->buah ?? 0 }}"></label>
                    <label class="field">Minyak (g)<input type="number" step="0.1" name="minyak" value="{{ $k->profil->minyak ?? 0 }}"></label>
                    <label class="field">Garam (g)<input type="number" step="0.1" name="garam" value="{{ $k->profil->garam ?? 0 }}"></label>
                  </div>
                  <button class="btn sm" style="margin-top:8px">Simpan</button>
                </form>
              </details>
              <x-act type="delete" :action="'/kategori/'.$k->id.'/hapus'" confirm="Hapus kategori ini?" fitur="master.tulis" />
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>

<div class="card" id="form-kat" style="margin-top:14px">
  <div class="card-head"><div><h3>Tambah kategori</h3><div class="sub">Profil gram awal menyalin SD kelas 1–3</div></div></div>
  <form method="post" action="/kategori">
    @csrf
    <div class="formgrid">
      <label class="field">Kode<input name="kode" placeholder="MISAL-SD7" required></label>
      <label class="field">Nama<input name="nama" required></label>
      <label class="field">AKG energi<input type="number" name="akg_energi" required></label>
      <label class="field">AKG protein<input type="number" step="0.1" name="akg_protein" required></label>
      <label class="field">AKG lemak<input type="number" step="0.1" name="akg_lemak"></label>
      <label class="field">AKG KH<input type="number" step="0.1" name="akg_kh"></label>
      <label class="field">Target energi<input type="number" step="0.1" name="target_energi"></label>
      <label class="field">Target protein<input type="number" step="0.1" name="target_protein"></label>
    </div>
    <button class="btn" style="margin-top:12px">Simpan kategori</button>
  </form>
</div>

@endsection
