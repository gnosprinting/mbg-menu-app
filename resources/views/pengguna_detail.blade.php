@extends('layouts.shell')
@section('title','Detail Pengguna')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if($errors->any())<div class="alert bad">⚠ {{ $errors->first() }}</div>@endif

<div class="page-top">
  <div><h1>Detail Pengguna</h1><p>{{ $u->name }} · {{ $u->email }}</p></div>
  <div class="actions"><a class="btn ghost" href="/pengguna">← Kembali</a></div>
</div>

<div class="card">
  <div class="card-head"><div><h3>Ubah data</h3><div class="sub">Kosongkan kata sandi bila tidak diubah</div></div></div>
  <form method="post" action="/pengguna/{{ $u->id }}">
    @csrf
    <div class="formgrid">
      <label class="field">Nama<input name="name" value="{{ $u->name }}" required></label>
      <label class="field">Email<input type="email" name="email" value="{{ $u->email }}" required></label>
      <label class="field">Peran
        <select name="peran">
          @foreach(['admin'=>'Admin SPPG','gizi'=>'Ahli gizi','dapur'=>'Petugas dapur','pengawas'=>'Pengawas','akuntan'=>'Akuntan'] as $k=>$v)
            <option value="{{ $k }}" @selected($u->peran===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </label>
      <label class="field">Lokasi
        <select name="lokasi_id">
          <option value="">Semua lokasi</option>
          @foreach($lokasi as $l)<option value="{{ $l->id }}" @selected($u->lokasi_id==$l->id)>{{ $l->nama }}</option>@endforeach
        </select>
      </label>
      <label class="field">Kata sandi baru<input type="password" name="password" minlength="6" placeholder="(opsional)"></label>
      <label style="display:flex;align-items:center;gap:7px;font-size:12px;margin-top:18px">
        <input type="checkbox" name="aktif" value="1" @checked($u->aktif)> Aktif
      </label>
    </div>
    <button class="btn" style="margin-top:12px">Simpan perubahan</button>
  </form>
</div>

@endsection
