@extends('layouts.shell')
@section('title','Siklus Menu')
@section('content')
@php
  $warna = ['Hewani'=>'#FF5C6C','Nabati'=>'#7B4DFF','Sayur'=>'#12B981','Buah'=>'#FF63B0'];
@endphp

@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Siklus Menu</h1><p>{{ $siklus->nama }} · versi {{ $siklus->versi }} · tampil {{ $jumlah }} hari</p></div>
  <div class="actions">
    <div class="seg">
      <a class="{{ $jumlah == 6 ? 'on' : '' }}" href="/siklus?hari=6">6 hari</a>
      <a class="{{ $jumlah == 12 ? 'on' : '' }}" href="/siklus?hari=12">12 hari</a>
    </div>
    <button class="btn" onclick="document.getElementById('form-menu').scrollIntoView({behavior:'smooth'})">+ Tambah menu</button>
  </div>
</div>

<form method="get" action="/siklus" class="toolbar">
  <input type="hidden" name="hari" value="{{ $jumlah }}">
  <label class="field" style="font-size:10.5px">Dari tanggal
    <input type="date" name="dari" value="{{ request('dari') }}" style="border:1px solid var(--line);border-radius:10px;padding:6px 9px">
  </label>
  <label class="field" style="font-size:10.5px">Sampai
    <input type="date" name="sampai" value="{{ request('sampai') }}" style="border:1px solid var(--line);border-radius:10px;padding:6px 9px">
  </label>
  <button class="btn ghost sm" style="align-self:flex-end">Filter</button>
  <a class="btn ghost sm" href="/siklus?hari={{ $jumlah }}" style="align-self:flex-end">Reset</a>
  <div class="grow"></div>
  <span class="chip violet">{{ $menu->count() }} dari {{ $jumlah }} hari</span>
</form>

<div class="grid g-2-1">
  <div class="card">
    <div class="card-head"><div><h3>Daftar menu</h3><div class="sub">Detail · ubah · hapus per hari</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Hari</th><th>Tanggal</th><th>Menu</th><th>Hewani</th><th>Nabati</th><th>Sayur</th><th>Buah</th><th class="num">Energi</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($menu as $m)
          <tr>
            <td class="strong">{{ $m->hari }}</td>
            <td>{{ $m->tanggal ? \Illuminate\Support\Carbon::parse($m->tanggal)->format('d M Y') : '—' }}</td>
            <td class="strong">{{ $m->nama }}</td>
            @foreach(['hewani','nabati','sayur','buah'] as $peran)
              <td>{{ $m->items->where('peran', $peran)->pluck('nama')->implode(', ') ?: '—' }}</td>
            @endforeach
            <td class="num">{{ round($m->energi) }} kkal</td>
            <td>
              <div class="act-row">
                <x-act type="view" :href="'/siklus/'.$m->id" />
                <x-act type="delete" :action="'/menu/'.$m->id.'/hapus'" :confirm="'Hapus menu hari '.$m->hari.' beserta bahannya?'" />
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="empty">Belum ada menu pada rentang ini.</td></tr>
        @endforelse
        @for($h = $menu->count() + 1; $h <= $jumlah; $h++)
          <tr><td class="strong">{{ $h }}</td><td class="muted">—</td><td colspan="6" class="muted">Belum diisi</td><td><span class="chip orange">Belum</span></td></tr>
        @endfor
      </tbody>
    </table></div>
  </div>

  <div>
    <div class="card" id="form-menu">
      <div class="card-head"><div><h3>Tambah / ubah menu</h3><div class="sub">Hari yang sama akan diperbarui</div></div></div>
      <form method="post" action="/menu">
        @csrf
        <div class="formgrid" style="grid-template-columns:1fr 1fr">
          <label class="field">Hari ke<input type="number" name="hari" min="1" max="12" value="{{ min(12, $menu->count() + 1) }}" required></label>
          <label class="field">Tanggal<input type="date" name="tanggal" value="{{ now()->toDateString() }}"></label>
        </div>
        <label class="field" style="margin-top:8px">Nama menu<input name="nama" placeholder="mis. Ayam kecap" required></label>
        <label class="field" style="margin-top:8px">Metode<input name="metode" placeholder="tumis &amp; kukus"></label>
        <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Simpan menu</button>
      </form>
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Tambah bahan ke menu</h3><div class="sub">Gramasi ditentukan aplikasi per kategori</div></div></div>
      <form method="post" action="/menu-bahan">
        @csrf
        <label class="field">Menu
          <select name="menu_hari_id" required data-search data-placeholder="Cari menu…">
            @foreach($menu as $m)<option value="{{ $m->id }}">Hari {{ $m->hari }} · {{ $m->nama }}</option>@endforeach
          </select>
        </label>
        <label class="field" style="margin-top:8px">Bahan
          <select name="bahan_id" required data-search data-placeholder="Cari bahan (mis. ayam, tempe)…">
            @foreach($bahan as $b)<option value="{{ $b->id }}">{{ $b->nama }}{{ $b->mentah ? '' : ' (olahan)' }}</option>@endforeach
          </select>
        </label>
        <div class="formgrid" style="grid-template-columns:1fr 1fr;margin-top:8px">
          <label class="field">Peran
            <select name="peran" id="peran-sel">
              @foreach(['pokok','hewani','nabati','sayur','buah','minyak','bumbu'] as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select>
          </label>
          <label class="field">Gram (otomatis)<input type="number" step="0.1" id="gram-inp" value="{{ $base['pokok'] }}" disabled style="background:#F3F5FB;color:#6B7390;cursor:not-allowed">
            <input type="hidden" name="gram" id="gram-hidden" value="{{ $base['pokok'] }}">
          </label>
        </div>
        <div class="footnote" id="gram-note">Gram basis SD 1–3 · kategori lain mengikuti profil.</div>
        <div class="table-wrap" style="margin-top:8px;max-height:190px;overflow:auto">
          <table><thead><tr><th>Kategori</th><th class="num">Gram</th></tr></thead>
            <tbody id="gram-preview">
              @foreach($kategori as $k)
                <tr><td>{{ $k->nama }}</td><td class="num" data-kat="{{ $k->id }}">{{ $profiles[$k->id]['pokok'] ?? 0 }}</td></tr>
              @endforeach
            </tbody></table>
        </div>
        <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Tambah bahan</button>
      </form>
    </div>
  </div>
</div>

<script>
  const profiles = @json($profiles);
  const base = @json($base);
  const peranSel = document.getElementById('peran-sel');
  const gramInp = document.getElementById('gram-inp');
  function updateGram() {
    const p = peranSel.value;
    gramInp.value = base[p] ?? 0;
    document.getElementById('gram-hidden').value = base[p] ?? 0;
    document.querySelectorAll('#gram-preview td[data-kat]').forEach(td => {
      const id = td.getAttribute('data-kat');
      td.textContent = (profiles[id] && profiles[id][p] !== undefined) ? profiles[id][p] : 0;
    });
    document.getElementById('gram-note').textContent = 'Gram basis SD 1–3 = ' + (base[p] ?? 0) + ' g; kategori lain dihitung otomatis.';
  }
  peranSel.addEventListener('change', updateGram);
  updateGram();
</script>

@endsection
