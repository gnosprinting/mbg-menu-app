@extends('layouts.shell')
@section('title','Detail Penerima')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Detail Penerima</h1><p>{{ $row->lokasi }} · {{ $row->kategori }}</p></div>
  <div class="actions"><a class="btn ghost" href="/master-penerima">← Kembali</a></div>
</div>

<div class="grid g-2">
  <div class="card">
    <div class="card-head"><div><h3>Ubah jumlah</h3><div class="sub">Jumlah porsi kategori ini di lokasi tersebut</div></div></div>
    <form method="post" action="/penerima-kategori/{{ $row->id }}">
      @csrf
      <label class="field">Lokasi<input value="{{ $row->lokasi }}" disabled></label>
      <label class="field" style="margin-top:8px">Kategori<input value="{{ $row->kategori }}" disabled></label>
      <label class="field" style="margin-top:8px">Jumlah<input type="number" name="jumlah" value="{{ $row->jumlah }}" min="0" required></label>
      <button class="btn" style="margin-top:12px">Simpan perubahan</button>
    </form>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Hapus</h3><div class="sub">Hanya bila tidak ada data terkait</div></div></div>
    <p style="font-size:11.8px;color:#39405A;line-height:1.6">
      Data ini terhubung ke <b>{{ $pmTerkait }}</b> PM alergi pada lokasi &amp; kategori yang sama.
      @if($pmTerkait > 0) Hapus dulu PM alergi tersebut sebelum menghapus penerima. @endif
    </p>
    <form method="post" action="/penerima-kategori/{{ $row->id }}/hapus" onsubmit="return confirm('Hapus penerima ini?')">
      @csrf
      <button class="act act-del" style="margin-top:8px" title="Hapus penerima" aria-label="Hapus penerima" @if($pmTerkait) disabled @endif><i class="fa-solid fa-trash-can"></i></button>
    </form>
  </div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Riwayat periode</h3><div class="sub">Perubahan jumlah disimpan sebagai periode — data lama tidak diubah</div></div><div class="right"><span class="chip violet">{{ $riwayat->count() }} periode</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Berlaku dari</th><th>Berlaku sampai</th><th class="num">Jumlah</th><th>Status</th></tr></thead>
    <tbody>
      @foreach($riwayat as $p)
        <tr>
          <td>{{ $p->berlaku_dari ? \Illuminate\Support\Carbon::parse($p->berlaku_dari)->format('d M Y') : '—' }}</td>
          <td>{{ $p->berlaku_sampai ? \Illuminate\Support\Carbon::parse($p->berlaku_sampai)->format('d M Y') : '—' }}</td>
          <td class="num">{{ number_format($p->jumlah, 0, ',', '.') }}</td>
          <td>@if($p->berlaku_sampai)<span class="chip orange">arsip</span>@else<span class="chip green">aktif</span>@endif</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>

@endsection
