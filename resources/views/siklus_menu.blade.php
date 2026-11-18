@extends('layouts.shell')
@section('title','Siklus Menu')
@section('content')
@php
  $warna = ['Hewani'=>'#FF5C6C','Nabati'=>'#7B4DFF','Sayur'=>'#12B981','Buah'=>'#FF63B0'];
  $boleh = fn (string $fitur) => \App\Services\Akses::boleh($fitur);
@endphp

@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Siklus Menu</h1><p>{{ $siklus->nama }} · pola {{ $pola }} · tampil {{ $jumlah }} hari</p></div>
  <div class="actions">
    @if($boleh('pengaturan.tulis'))
      <div class="seg">
        @foreach($opsi as $o)
          <form method="post" action="/pengaturan/siklus" style="display:contents">
            @csrf
            <input type="hidden" name="pola_siklus" value="{{ $pola }}">
            <input type="hidden" name="panjang_siklus" value="{{ $o }}">
            <button type="submit" class="{{ $jumlah == $o ? 'on' : '' }}">{{ $o }} hari</button>
          </form>
        @endforeach
      </div>
    @else
      <span class="chip violet">{{ $jumlah }} hari</span>
    @endif
    @if($boleh('menu.tulis'))
      <button class="btn" onclick="document.getElementById('form-menu').scrollIntoView({behavior:'smooth'})">+ Tambah menu</button>
    @endif
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

<div class="grid {{ $boleh('menu.tulis') ? 'g-2-1' : '' }}">
  <div class="card">
    <div class="card-head"><div><h3>Daftar menu</h3><div class="sub">Detail · ubah · hapus per hari</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Hari</th><th>Tanggal</th><th>Menu</th><th>Pokok</th><th>Hewani</th><th>Nabati</th><th>Sayur</th><th>Buah</th><th class="num">Energi</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($menu as $m)
          <tr>
            <td class="strong">{{ $m->hari }}</td>
            <td>{{ $m->tanggal ? \Illuminate\Support\Carbon::parse($m->tanggal)->format('d M Y') : '—' }}</td>
            <td class="strong">{{ $m->nama }}
              @php $tamHari = $tambahan->where('menu_hari_id', $m->id); @endphp
              @if($tamHari->count())
                <div style="margin-top:3px;display:flex;flex-direction:column;gap:1px">
                  @foreach($tamHari->groupBy('kategori_id') as $g)
                    <span style="font-size:9.5px;color:#5B2BE0;line-height:1.35">+ {{ $g->first()->kode === 'PRE' ? 'Preeklampsia' : ($g->first()->kode === 'HAMIL' ? 'Hamil' : 'Menyusui') }}: {{ $g->pluck('bahan')->implode(', ') }}</span>
                  @endforeach
                </div>
              @endif
            </td>
            @foreach(['pokok','hewani','nabati','sayur','buah'] as $peran)
              <td>{{ $m->items->where('peran', $peran)->pluck('nama')->implode(', ') ?: '—' }}</td>
            @endforeach
            <td class="num">{{ round($m->energi) }} kkal</td>
            <td>
              <div class="act-row">
                <x-act type="view" :href="'/siklus/'.$m->id" />
                <x-act type="delete" :action="'/menu/'.$m->id.'/hapus'" :confirm="'Hapus menu hari '.$m->hari.' beserta bahannya?'" fitur="menu.tulis" />
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="empty">Belum ada menu pada rentang ini.</td></tr>
        @endforelse
        @php $terisi = $menu->pluck('hari')->all(); @endphp
        @for($h = 1; $h <= $jumlah; $h++)
          @continue(in_array($h, $terisi))
          <tr><td class="strong">{{ $h }}</td><td class="muted">—</td><td colspan="7" class="muted">Belum diisi</td><td><span class="chip orange">Belum</span></td></tr>
        @endfor
      </tbody>
    </table></div>
  </div>

  @if($boleh('menu.tulis'))
  <div>
    <div class="card" id="form-menu">
      <div class="card-head"><div><h3>Tambah / ubah menu</h3><div class="sub">Hari yang sama akan diperbarui</div></div></div>
      <form method="post" action="/menu">
        @csrf
        <div class="formgrid" style="grid-template-columns:1fr 1fr">
          @php
            $terisiMenu = $menu->pluck('hari')->all();
            $hariBerikut = 1;
            while (in_array($hariBerikut, $terisiMenu)) { $hariBerikut++; }
          @endphp
          <label class="field">Hari ke<input type="number" name="hari" min="1" max="{{ $jumlah }}" value="{{ min($jumlah, $hariBerikut) }}" required></label>
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
          <label class="field">Gram
            <input type="number" step="0.1" min="0.1" name="gram" id="gram-inp" value="{{ $base['pokok'] }}">
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

    <div class="card" style="margin-top:14px" id="form-3b">
      <div class="card-head"><div><h3>Tambahan 3B</h3><div class="sub">Hamil · Menyusui · Preeklampsia — masuk hitungan gizi, belanja, dan alergi</div></div></div>
      <form method="post" action="/menu-tambahan-3b">
        @csrf
        <label class="field">Menu
          <select name="menu_hari_id" required data-search data-placeholder="Cari menu…">
            @foreach($menu as $m)<option value="{{ $m->id }}">Hari {{ $m->hari }} · {{ $m->nama }}</option>@endforeach
          </select>
        </label>
        <div class="formgrid" style="grid-template-columns:1fr 1fr;margin-top:8px">
          <label class="field">Kategori 3B
            <select name="kategori_id" required>
              @foreach($kategori3b as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
            </select>
          </label>
          <label class="field">Gram
            <input type="number" step="0.1" min="0.1" name="gram" value="55" required>
          </label>
        </div>
        <label class="field" style="margin-top:8px">Bahan tambahan
          <select name="bahan_id" required data-search data-placeholder="Cari bahan (mis. telur, ikan)…">
            @foreach($bahan as $b)<option value="{{ $b->id }}">{{ $b->nama }}{{ $b->mentah ? '' : ' (olahan)' }}</option>@endforeach
          </select>
        </label>
        <div class="formgrid" style="grid-template-columns:1fr 1fr;margin-top:8px">
          <label class="field">Metode<input name="metode" placeholder="rebus / kukus"></label>
          <label class="field">Catatan<input name="catatan" placeholder="mis. tanpa garam"></label>
        </div>
        <div class="footnote" style="margin-top:8px">Untuk preeklampsia, tulis “tanpa garam” di catatan. Tambahan ini hanya dihitung untuk kategori yang dipilih.</div>
        <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Tambah tambahan 3B</button>
      </form>

      @if($tambahan->count())
        <div class="table-wrap" style="margin-top:10px;max-height:220px;overflow:auto">
          <table>
            <thead><tr><th>Hari</th><th>Kategori</th><th>Bahan</th><th class="num">Gram</th><th>Metode</th><th>Aksi</th></tr></thead>
            <tbody>
              @foreach($tambahan as $t)
                <tr>
                  <td class="strong">H{{ $t->hari }}</td>
                  <td>{{ $t->kategori }}</td>
                  <td>{{ $t->bahan }}</td>
                  <td class="num">{{ rtrim(rtrim(number_format($t->gram, 1, ',', '.'), '0'), ',') }} g</td>
                  <td>{{ $t->metode ?: '—' }}@if($t->catatan)<div class="sub">{{ $t->catatan }}</div>@endif</td>
                  <td><x-act type="delete" :action="'/menu-tambahan-3b/'.$t->id.'/hapus'" :confirm="'Hapus tambahan '.$t->bahan.' untuk '.$t->kategori.'?'" fitur="menu.tulis" /></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
  @endif
</div>

<script>
  const profiles = @json($profiles);
  const base = @json($base);
  const peranSel = document.getElementById('peran-sel');
  const gramInp = document.getElementById('gram-inp');
  if (peranSel && gramInp) {
    const updateGram = () => {
      const p = peranSel.value;
      gramInp.value = base[p] ?? 0;
      document.querySelectorAll('#gram-preview td[data-kat]').forEach(td => {
        const id = td.getAttribute('data-kat');
        td.textContent = (profiles[id] && profiles[id][p] !== undefined) ? profiles[id][p] : 0;
      });
      document.getElementById('gram-note').textContent = 'Rekomendasi SD 1–3 = ' + (base[p] ?? 0) + ' g · bisa diubah manual; kategori lain mengikuti profil.';
    };
    peranSel.addEventListener('change', updateGram);
    updateGram();
  }
</script>

@endsection
