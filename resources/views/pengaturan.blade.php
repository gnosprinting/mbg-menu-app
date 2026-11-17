@extends('layouts.shell')
@section('title','Pengaturan')
@section('content')
@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif
@if($errors->any())<div class="alert bad">⚠ {{ $errors->first() }}</div>@endif

<div class="page-top">
  <div><h1>Pengaturan</h1><p>Identitas SPPG · logo · ambang perhitungan · lokasi</p></div>
</div>

<div class="grid g-2">
  <form method="post" action="/pengaturan">
    @csrf
    <div class="card">
      <div class="card-head"><div><h3>Kop surat</h3><div class="sub">Dipakai pada surat, berita acara, PO, laporan</div></div></div>
      <div class="formgrid" style="grid-template-columns:1fr 1fr">
        <label class="field">Nama SPPG<input name="sppg_nama" value="{{ $settings['sppg_nama'] ?? '' }}"></label>
        <label class="field">Nama yayasan<input name="sppg_yayasan" value="{{ $settings['sppg_yayasan'] ?? '' }}"></label>
        <label class="field">Telepon<input name="sppg_telepon" value="{{ $settings['sppg_telepon'] ?? '' }}"></label>
        <label class="field">Email<input name="sppg_email" value="{{ $settings['sppg_email'] ?? '' }}"></label>
        <label class="field" style="grid-column:span 2">Alamat<input name="sppg_alamat" value="{{ $settings['sppg_alamat'] ?? '' }}"></label>
      </div>
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Ambang perhitungan</h3><div class="sub">Berlaku semua kategori</div></div></div>
      <div class="formgrid" style="grid-template-columns:1fr 1fr">
        <label class="field">Target capaian (% AKG)<input type="number" name="target_persen" value="{{ $settings['target_persen'] ?? 30 }}"></label>
        <label class="field">Buffer belanja (%)<input type="number" name="buffer_belanja" value="{{ $settings['buffer_belanja'] ?? 10 }}"></label>
        <label class="field">Batas natrium preeklampsia (mg)<input type="number" name="natrium_max" value="{{ $settings['natrium_max'] ?? 600 }}"></label>
      </div>
      <button class="btn" style="margin-top:12px">Simpan pengaturan</button>
    </div>
  </form>

  <div>
    <div class="card">
      <div class="card-head"><div><h3>Logo SPPG</h3><div class="sub">Tampil di sidebar dan halaman login</div></div></div>
      <div style="display:flex;align-items:center;gap:14px">
        @if(!empty($settings['logo']))
          <img src="{{ Storage::url($settings['logo']) }}" alt="Logo" style="width:64px;height:64px;border-radius:16px;object-fit:cover;border:1px solid var(--line)">
        @else
          <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#8B5CFF,#4F46E5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800">M</div>
        @endif
        <div class="muted" style="font-size:11.5px">PNG/JPG, maksimal 2 MB. Disarankan rasio 1:1.</div>
      </div>
      <form method="post" action="/pengaturan/logo" enctype="multipart/form-data" style="margin-top:12px">
        @csrf
        <input type="file" name="logo" accept="image/*" required style="font-size:11.5px">
        <button class="btn" style="margin-top:10px">Unggah logo</button>
      </form>
    </div>

    <div class="card" style="margin-top:14px">
      <div class="card-head"><div><h3>Lokasi</h3><div class="sub">Pagu harian per lokasi</div></div></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Lokasi</th><th>Jenis</th><th class="num">Pagu/hari</th></tr></thead>
        <tbody>
          @foreach($lokasi as $l)
            <tr><td>{{ $l->nama }}</td><td><span class="chip {{ $l->jenis === 'sekolah' ? 'violet' : 'sky' }}">{{ $l->jenis }}</span></td><td class="num">Rp {{ number_format($l->pagu_harian, 0, ',', '.') }}</td></tr>
          @endforeach
        </tbody>
      </table></div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head">
    <div><h3>Pola siklus menu</h3><div class="sub">Menentukan keluarga siklus dan panjang hari yang dipakai seluruh perhitungan</div></div>
    <div class="right"><span class="chip violet">Aktif: {{ $pola }} · {{ $panjang }} hari</span></div>
  </div>
  <form method="post" action="/pengaturan/siklus">
    @csrf
    <div class="formgrid" style="grid-template-columns:1fr 1fr">
      <label class="field">Pola siklus
        <select name="pola_siklus" id="pola-sel">
          <option value="5/10" {{ $pola === '5/10' ? 'selected' : '' }}>5/10 — 5 hari pelayanan/minggu (rekomendasi)</option>
          <option value="6/12" {{ $pola === '6/12' ? 'selected' : '' }}>6/12 — pola lama 6 hari pelayanan</option>
        </select>
      </label>
      <label class="field">Panjang siklus aktif
        <select name="panjang_siklus" id="panjang-sel">
          @foreach([5, 10, 6, 12] as $o)
            <option value="{{ $o }}" data-pola="{{ in_array($o, [5, 10], true) ? '5/10' : '6/12' }}" {{ $panjang === $o ? 'selected' : '' }}>{{ $o }} hari{{ $o === 5 || $o === 6 ? ' (mingguan)' : ' (dua mingguan)' }}</option>
          @endforeach
        </select>
      </label>
    </div>
    <div class="footnote" style="margin-top:8px">
      Pola 5/10 selaras dengan 5 hari pelayanan efektif per minggu (Peraturan BGN 13/2026). Pola 6/12 dipertahankan untuk dapur dengan 6 hari pelayanan; menu hari ke-6/12 akan bergeser bila hanya 5 hari pelayanan.
      Siklus aktif sekarang: <b>{{ $siklusAktif->nama }}</b>.
    </div>
    <button class="btn" style="margin-top:12px">Simpan pola siklus</button>
  </form>
</div>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Kategori &amp; target</h3><div class="sub">{{ $kategori->count() }} kategori · ubah di Master Kategori</div></div><div class="right"><a class="link" href="/master-kategori">Buka →</a></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Kategori</th><th class="num">AKG energi</th><th class="num">AKG protein</th><th class="num">Target energi</th><th class="num">Target protein</th></tr></thead>
    <tbody>
      @foreach($kategori as $k)
        <tr>
          <td>{{ $k->nama }}</td>
          <td class="num">{{ number_format($k->akg_energi, 0, ',', '.') }}</td>
          <td class="num">{{ number_format($k->akg_protein, 1, ',', '.') }}</td>
          <td class="num">{{ $k->target_energi ? number_format($k->target_energi, 0, ',', '.') : round($k->akg_energi * (($settings['target_persen'] ?? 30) / 100)) }}</td>
          <td class="num">{{ $k->target_protein ? number_format($k->target_protein, 1, ',', '.') : number_format($k->akg_protein * (($settings['target_persen'] ?? 30) / 100), 1, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>


<script>
(() => {
  const pola = document.getElementById('pola-sel');
  const panjang = document.getElementById('panjang-sel');
  if (!pola || !panjang) return;
  const sync = () => {
    const p = pola.value;
    let first = null;
    [...panjang.options].forEach(o => {
      const cocok = o.dataset.pola === p;
      o.hidden = !cocok;
      o.disabled = !cocok;
      if (cocok && !first) first = o;
    });
    if (panjang.selectedOptions[0]?.disabled && first) panjang.value = first.value;
  };
  pola.addEventListener('change', sync);
  sync();
})();
</script>

@endsection
