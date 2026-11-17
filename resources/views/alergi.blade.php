@extends('layouts.shell')
@section('title','Alergi & Varian')
@section('content')
@php
  $terpicuIds = collect($terpicu)->pluck('id')->all();
  $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
@endphp

@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Alergi &amp; Varian</h1><p>Satu sumber data: PM alergi, aturan per kategori, dan varian preeklampsia</p></div>
  <div class="actions">
    @if($tab === 'periksa')
      <form method="get" action="/alergi">
        <input type="hidden" name="tab" value="periksa">
        <select name="hari" onchange="this.form.submit()" style="border:1px solid var(--line);border-radius:10px;padding:7px 10px;font:inherit;font-size:11.8px;background:#fff">
          @for($h = 1; $h <= $jumlah; $h++)<option value="{{ $h }}" @selected($h == $hari)>Hari {{ $h }}</option>@endfor
        </select>
      </form>
    @endif
    <a class="btn ghost" href="/master-alergen">Master alergen</a>
    <a class="btn ghost" href="/export/alergi.xlsx?hari={{ $hari }}">Excel</a>
    <a class="btn" href="/export/alergi.pdf?hari={{ $hari }}">Unduh laporan</a>
  </div>
</div>

<div class="tabs">
  <a class="{{ $tab === 'periksa' ? 'on' : '' }}" href="/alergi?tab=periksa"><i class="fa-solid fa-clipboard-check"></i> Pemeriksaan hari ini</a>
  <a class="{{ $tab === 'aturan' ? 'on' : '' }}" href="/alergi?tab=aturan"><i class="fa-solid fa-shield-heart"></i> Aturan &amp; varian</a>
</div>

@if($tab === 'periksa')
  <div class="stepper">
    <div class="step done"><b>1 · Data PM alergi</b>{{ $pm->count() }} PM di {{ $pm->pluck('lokasi')->unique()->count() }} lokasi ✓</div>
    <div class="step on"><b>2 · Periksa menu</b>{{ count($terpicu) }} terpicu hari ini</div>
    <div class="step"><b>3 · Menu pengganti</b>belum diisi</div>
    <div class="step"><b>4 · Unduh laporan</b>{{ count($terpicu) ? 'belum siap' : 'siap' }}</div>
  </div>

  @if(count($terpicu))
    <div class="alert bad">⚠ {{ count($terpicu) }} PM terpicu menu hari {{ $hari }} — {{ $menu->nama ?? '—' }}.</div>
  @else
    <div class="alert ok">✓ Tidak ada PM terpicu pada menu hari {{ $hari }} — {{ $menu->nama ?? '—' }}.</div>
  @endif

  <div class="grid g-2-1">
    <div class="card">
      <div class="card-head"><div><h3>Penerima manfaat alergi</h3><div class="sub">Kategori PM dipakai sebagai dasar porsi</div></div></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Nama</th><th>Kelas</th><th>Kategori PM</th><th>Lokasi</th><th>Alergen</th><th>Status</th><th>Pengganti</th></tr></thead>
        <tbody>
          @forelse($pm as $p)
            <tr>
              <td class="strong">{{ $p->nama }}</td>
              <td>{{ $p->kelas ?: '—' }}</td>
              <td>{{ $p->kategori ?? '—' }}</td>
              <td>{{ $p->lokasi }}</td>
              <td>@foreach($p->alergen as $a)<span class="chip red">{{ $a }}</span> @endforeach</td>
              <td>
                @if(in_array($p->id, $terpicuIds))
                  <span class="chip red">× terpicu</span>
                @else
                  <span class="chip green">✓ aman</span>
                @endif
              </td>
              <td class="muted">{{ in_array($p->id, $terpicuIds) ? 'belum diisi' : '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">Belum ada PM alergi.</td></tr>
          @endforelse
        </tbody>
      </table></div>
      <div class="footnote">Pencocokan memakai nama bahan pada resep, bukan AI dan bukan pengganti pemeriksaan medis.</div>
    </div>

    <div>
      <div class="card">
        <div class="card-head"><div><h3>Tambah PM alergi</h3><div class="sub">Porsi mengikuti kategori PM</div></div></div>
        <form method="post" action="/pm-alergi">
          @csrf
          <label class="field">Nama<input name="nama" required></label>
          <div class="formgrid" style="grid-template-columns:1fr 1fr;margin-top:8px">
            <label class="field">Kelas<input name="kelas" placeholder="2 / VII / balita"></label>
            <label class="field">Kategori PM
              <select name="kategori_id" required>
                @foreach($kategori as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
              </select>
            </label>
          </div>
          <label class="field" style="margin-top:8px">Lokasi
            <select name="lokasi_id" required>
              @foreach($lokasi as $l)<option value="{{ $l->id }}">{{ $l->nama }}</option>@endforeach
            </select>
          </label>
          <div class="field" style="margin-top:10px">Alergen <span class="muted" style="font-weight:400">· pilih satu atau lebih</span></div>
          <div class="alergen-box {{ $alergen->count() > 10 ? 'is-long' : '' }}">
            @foreach($alergen as $a)
              <label class="alergen-item">
                <input type="checkbox" name="alergen[]" value="{{ $a->id }}">
                <span>{{ $a->nama }}</span>
              </label>
            @endforeach
          </div>
          @if($alergen->count() > 10)
            <button type="button" class="alergen-more" onclick="
              var box=document.querySelector('.alergen-box');
              var more=box.classList.toggle('open');
              this.textContent = more ? 'Sembunyikan sebagian' : 'Tampilkan semua ({{ $alergen->count() }})';
            ">Tampilkan semua ({{ $alergen->count() }})</button>
          @endif
          <button class="btn" style="width:100%;justify-content:center">Simpan PM</button>
        </form>
        <div class="footnote">Alergen baru bisa ditambahkan di menu Master Alergen.</div>
      </div>

      <div class="card" style="margin-top:14px">
        <div class="card-head"><div><h3>Hasil pemeriksaan</h3><div class="sub">Menu hari {{ $hari }}</div></div></div>
        @forelse($terpicu as $t)
          <div class="item"><div class="thumb">!</div><div><b>{{ $t->nama }}</b><small>{{ implode(', ', $t->bahan) }}</small></div><div class="right"><span class="badge down">terpicu</span></div></div>
        @empty
          <div class="empty">Tidak ada benturan bahan.</div>
        @endforelse
      </div>
    </div>
  </div>

@else
  <div class="alert ok" style="margin-bottom:14px">
    <b>Dari mana datanya?</b> Aturan alergi per kategori dihitung dari <b>Master Alergen → relasi alergen–bahan</b> dan <b>PM alergi</b> di tab Pemeriksaan.
    Menambah PM atau mengubah relasi alergen bahan langsung mengubah tabel ini.
  </div>

  <div class="grid g-2">
    <div class="card" style="border-left:4px solid #FF5C6C">
      <div class="card-head"><div><h3>Alergi per kategori</h3><div class="sub">Agregasi PM alergi → kategori → alergen</div></div><div class="right"><span class="chip red">{{ $aturan->pluck('kategori')->unique()->count() }} kategori</span></div></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Kategori</th><th>Alergen</th><th>Bahan dihindari</th><th class="num">PM</th></tr></thead>
        <tbody>
          @forelse($aturan as $a)
            <tr>
              <td class="strong">{{ $a->kategori }}</td>
              <td><span class="chip red">{{ $a->alergen }}</span></td>
              <td class="muted" style="font-size:11px">{{ $a->bahan ? implode(', ', $a->bahan) : 'belum dipetakan' }}</td>
              <td class="num">{{ $a->pm }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="empty">Belum ada PM alergi.</td></tr>
          @endforelse
        </tbody>
      </table></div>
      <div class="footnote">Bahan dihindari berasal dari pemetaan alergen → bahan di Master Alergen.</div>
    </div>

    <div class="card" style="border-left:4px solid #22A6FF">
      <div class="card-head"><div><h3>{{ $pre->nama ?? 'Preeklampsia' }}</h3><div class="sub">Batas natrium {{ $rp($natriumMax) }} mg per porsi</div></div><div class="right"><span class="chip sky">1 kategori</span></div></div>
      <div class="list">
        <div class="item"><div><b>Target energi</b><small>per porsi</small></div><div class="right"><span class="badge info">{{ $rp($pre->target_energi ?? 765) }} kkal</span></div></div>
        <div class="item"><div><b>Target protein</b><small>per porsi</small></div><div class="right"><span class="badge info">{{ number_format($pre->target_protein ?? 22.5, 1, ',', '.') }} g</span></div></div>
        <div class="item"><div><b>Natrium maksimum</b><small>ambang aplikasi</small></div><div class="right"><span class="badge warn">{{ $rp($natriumMax) }} mg</span></div></div>
        <div class="item"><div><b>Dilarang</b><small>garam meja, kecap, penyedap, ikan asin, kerupuk</small></div><div class="right"><span class="chip red">ketat</span></div></div>
      </div>
      <div class="alert warn" style="margin-top:12px">Menu siklus standar belum otomatis rendah natrium. Periksa tabel di bawah.</div>
    </div>
  </div>

  <div class="card" style="margin-top:14px">
    <div class="card-head"><div><h3>Pemeriksaan menu preeklampsia — {{ $jumlah }} hari</h3><div class="sub">Dihitung dari komposisi menu yang berlaku</div></div><div class="right"><a class="link" href="/komposisi?kategori={{ $pre->id ?? '' }}">Lihat komposisi →</a></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Hari</th><th>Menu</th><th class="num">Energi</th><th class="num">Natrium</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($menuPre as $m)
          <tr>
            <td class="strong">{{ $m['hari'] }}</td>
            <td>{{ $m['menu'] }}</td>
            <td class="num">{{ $rp($m['energi']) }} kkal</td>
            <td class="num">{{ $rp($m['natrium']) }} mg</td>
            <td>@if($m['aman'])<span class="chip green">✓ aman</span>@else<span class="chip red">× lewat batas</span>@endif</td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Belum ada menu untuk dihitung.</td></tr>
        @endforelse
      </tbody>
    </table></div>
    <div class="footnote">Status aman bila natrium per porsi ≤ {{ $rp($natriumMax) }} mg.</div>
  </div>

  <div class="card" style="margin-top:14px">
    <div class="card-head"><div><h3>Cara kerja dua lapis</h3><div class="sub">Pemeriksaan per PM vs aturan per kategori</div></div></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
      <div><b style="font-size:12px">Pemeriksaan hari ini</b><p style="font-size:11.6px;color:#39405A;line-height:1.65">Per <b>individu PM</b> dan per <b>hari menu</b>: bahan menu hari itu dicocokkan dengan alergen tiap PM, lalu ditandai siapa yang terpicu.</p></div>
      <div><b style="font-size:12px">Aturan &amp; varian</b><p style="font-size:11.6px;color:#39405A;line-height:1.65">Per <b>kategori</b>: alergen yang harus dihindari, bahan yang mengandung alergen itu, serta varian preeklampsia rendah natrium.</p></div>
      <div><b style="font-size:12px">Satu sumber data</b><p style="font-size:11.6px;color:#39405A;line-height:1.65">Keduanya memakai master alergen, relasi alergen–bahan, dan kategori PM yang sama — kini dalam satu halaman bertab.</p></div>
    </div>
  </div>
@endif

@endsection
