@extends('layouts.shell')
@section('title','Detail Menu')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif

<div class="page-top">
  <div><h1>Detail Menu — Hari {{ $m->hari }}</h1><p>{{ $m->nama }} · {{ $m->tanggal ? \Illuminate\Support\Carbon::parse($m->tanggal)->translatedFormat('l, j F Y') : 'tanggal belum diisi' }}</p></div>
  <div class="actions"><a class="btn ghost" href="/siklus">← Kembali</a></div>
</div>

<div class="grid g-2-1">
  <div class="card">
    <div class="card-head"><div><h3>Bahan pada menu</h3><div class="sub">Gram basis {{ $sd->nama }}</div></div><div class="right"><span class="chip violet">{{ $items->count() }} bahan</span></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Bahan</th><th>Peran</th><th class="num">Gram</th><th class="num">Energi</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($items as $i)
          <tr>
            <td class="strong">{{ $i->nama }}</td>
            <td>{{ ucfirst($i->peran) }}</td>
            <td class="num">{{ number_format($i->gram, 1, ',', '.') }}</td>
            <td class="num">{{ number_format($i->gram / 100 * $i->energi, 0, ',', '.') }}</td>
            <td><x-act type="delete" :action="'/menu-bahan/'.$i->id.'/hapus'" confirm="Keluarkan bahan ini dari menu?" /></td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Menu ini belum punya bahan.</td></tr>
        @endforelse
      </tbody>
    </table></div>
    <div class="footnote">Komposisi {{ $sd->nama }}: {{ round($komp['total']['energi']) }} kkal ({{ round($komp['pct']['energi']) }}% target), protein {{ number_format($komp['total']['protein'], 1, ',', '.') }} g.</div>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><div><h3>Ubah menu</h3><div class="sub">Nama, tanggal, metode</div></div></div>
      <form method="post" action="/menu/{{ $m->id }}">
        @csrf
        <label class="field">Nama menu<input name="nama" value="{{ $m->nama }}" required></label>
        <label class="field" style="margin-top:8px">Tanggal<input type="date" name="tanggal" value="{{ $m->tanggal }}"></label>
        <label class="field" style="margin-top:8px">Metode<input name="metode" value="{{ $m->metode }}"></label>
        <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Simpan perubahan</button>
      </form>
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Hapus menu</h3><div class="sub">Bahan ikut terhapus</div></div></div>
      <p style="font-size:11.8px;color:#39405A">Menghapus menu hari {{ $m->hari }} juga menghapus {{ $items->count() }} baris bahan di dalamnya.</p>
      <form method="post" action="/menu/{{ $m->id }}/hapus" onsubmit="return confirm('Hapus menu ini beserta bahannya?')">
        @csrf
        <button class="act act-del" style="margin-top:8px" title="Hapus menu" aria-label="Hapus menu"><i class="fa-solid fa-trash-can"></i></button>
      </form>
    </div>
  </div>
</div>

@endsection
