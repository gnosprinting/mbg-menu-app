@extends('layouts.shell')
@section('title','Pengguna')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif
@if($errors->any())<div class="alert bad">⚠ {{ $errors->first() }}</div>@endif

@php $label = ['admin'=>'Admin SPPG','gizi'=>'Ahli gizi','dapur'=>'Petugas dapur','pengawas'=>'Pengawas','akuntan'=>'Akuntan']; @endphp

<div class="page-top">
  <div><h1>Pengguna &amp; Peran</h1><p>{{ $users->count() }} akun · detail, ubah, aktif/nonaktif</p></div>
  @if(auth()->user()->peran === 'admin')
    <div class="actions"><button class="btn" onclick="document.getElementById('form-user').scrollIntoView({behavior:'smooth'})">+ Undang pengguna</button></div>
  @endif
</div>

<div class="card">
  <div class="card-head"><div><h3>Daftar pengguna</h3><div class="sub">Status nonaktif tidak bisa masuk</div></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      @foreach($users as $u)
        <tr>
          <td class="strong">{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td><span class="chip {{ $u->peran==='admin'?'violet':($u->peran==='gizi'?'green':($u->peran==='dapur'?'sky':'red')) }}">{{ $label[$u->peran] ?? $u->peran }}</span></td>
          <td>{{ $u->lokasi ?? 'Semua lokasi' }}</td>
          <td><span class="chip {{ $u->aktif ? 'green' : 'orange' }}">{{ $u->aktif ? 'aktif' : 'nonaktif' }}</span></td>
          <td><div class="act-row">
            <x-act type="view" :href="'/pengguna/'.$u->id" />
            <x-act type="edit" :href="'/pengguna/'.$u->id" />
            @if(auth()->user()->peran === 'admin' && $u->id !== auth()->id())
              <x-act type="toggle" :action="'/pengguna/'.$u->id.'/toggle'" :confirm="$u->aktif ? 'Nonaktifkan akun ini?' : 'Aktifkan akun ini?'" :title="$u->aktif ? 'Nonaktifkan' : 'Aktifkan'" />
            @endif
          </div></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>

@if(auth()->user()->peran === 'admin')
<div class="card" id="form-user" style="margin-top:14px">
  <div class="card-head"><div><h3>Tambah pengguna</h3><div class="sub">Kata sandi awal minimal 6 karakter</div></div></div>
  <form method="post" action="/pengguna">
    @csrf
    <div class="formgrid">
      <label class="field">Nama<input name="name" required></label>
      <label class="field">Email<input type="email" name="email" required></label>
      <label class="field">Peran
        <select name="peran"><option value="gizi">Ahli gizi</option><option value="dapur">Petugas dapur</option><option value="pengawas">Pengawas</option><option value="akuntan">Akuntan</option><option value="admin">Admin SPPG</option></select>
      </label>
      <label class="field">Lokasi
        <select name="lokasi_id">
          <option value="">Semua lokasi</option>
          @foreach(DB::table('lokasi')->orderBy('nama')->get() as $l)<option value="{{ $l->id }}">{{ $l->nama }}</option>@endforeach
        </select>
      </label>
      <label class="field">Kata sandi<input type="password" name="password" minlength="6" required></label>
      <div class="field" style="justify-content:flex-end"><button class="btn" style="justify-content:center">Simpan</button></div>
    </div>
  </form>
</div>
@endif

@endsection
