@extends('layouts.shell')
@section('title','Master Alergen')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Master Alergen</h1><p>Daftar alergen dipakai deteksi menu dan data PM</p></div>
  <div class="actions"><button class="btn" onclick="document.getElementById('form-alergen').scrollIntoView({behavior:'smooth'})">+ Tambah alergen</button></div>
</div>

<div class="grid g-2">
  <div class="card">
    <div class="card-head"><div><h3>Daftar alergen</h3><div class="sub">{{ $alergen->count() }} alergen</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Nama</th><th class="num">PM terkait</th><th class="num">Bahan terkait</th><th>Aksi</th></tr></thead>
      <tbody>
        @foreach($alergen as $a)
          <tr>
            <td class="strong">{{ $a->nama }}</td>
            <td class="num">{{ $a->dipakai }}</td>
            <td class="num">{{ $a->bahan }}</td>
            <td>
              <div class="act-row" style="position:relative">
                <details class="act-pop">
                  <summary class="act act-edit" title="Ubah nama" aria-label="Ubah nama"><i class="fa-solid fa-pen"></i></summary>
                  <form method="post" action="/alergen/{{ $a->id }}" class="act-panel" style="width:300px">
                    @csrf
                    <label class="field">Nama alergen<input name="nama" value="{{ $a->nama }}" required maxlength="40"></label>
                    <button class="btn sm" style="margin-top:8px">Simpan</button>
                  </form>
                </details>
                <x-act type="delete" :action="'/alergen/'.$a->id.'/hapus'" :disabled="$a->dipakai || $a->bahan" confirm="Hapus alergen ini?" title="{{ ($a->dipakai || $a->bahan) ? 'Tidak bisa dihapus: masih dipakai' : 'Hapus' }}" />
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
    <div class="footnote">Alergen hanya bisa dihapus bila belum terhubung ke PM atau bahan.</div>
  </div>

  <div class="card" id="form-alergen">
    <div class="card-head"><div><h3>Tambah alergen</h3><div class="sub">Nama unik, singkat</div></div></div>
    <form method="post" action="/alergen">
      @csrf
      <label class="field">Nama alergen<input name="nama" placeholder="mis. Kacang tanah" required></label>
      <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Simpan</button>
    </form>
    <div class="footnote">Setelah ditambahkan, alergen muncul otomatis di form PM alergi.</div>
  </div>
</div>

@endsection
