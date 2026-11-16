@extends('layouts.shell')
@section('title','Stok')
@section('content')
@php $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ','); @endphp

@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif
@if(session('galat'))<div class="alert bad">⚠ {{ session('galat') }}</div>@endif

<div class="page-top">
  <div><h1>Stok</h1><p>Dipotong otomatis dari menu hari 3 · {{ $penerima['total'] }} porsi · PO dari selisih kebutuhan</p></div>
  <div class="actions"><button class="btn ghost">Unduh laporan</button><button class="btn" onclick="document.getElementById('form-mutasi').scrollIntoView({behavior:'smooth'})">+ Mutasi stok</button></div>
</div>

<div class="kpis">
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)"><div class="top"><span class="lbl">Total item</span></div><div class="val">{{ $items->count() }}</div><div class="meta">bahan &amp; operasional</div><div class="ico-r"><i class="fa-solid fa-boxes-stacked"></i></div></div>
  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)"><div class="top"><span class="lbl">Stok aman</span></div><div class="val">{{ $items->filter(fn ($i) => $i->stok >= $i->minimum)->count() }}</div><div class="meta">di atas minimum</div><div class="ico-r"><i class="fa-solid fa-circle-check"></i></div></div>
  <div class="kpi" style="--tint:var(--lemon);--accent:var(--amber)"><div class="top"><span class="lbl">Perlu perhatian</span></div><div class="val">{{ $items->filter(fn ($i) => $i->stok < $i->minimum && $i->stok > 0)->count() }}</div><div class="meta">menipis</div><div class="ico-r"><i class="fa-solid fa-triangle-exclamation"></i></div></div>
  <div class="kpi" style="--tint:var(--rosebg);--accent:var(--rose)"><div class="top"><span class="lbl">Kritis / habis</span></div><div class="val">{{ $items->filter(fn ($i) => $i->stok <= 0)->count() }}</div><div class="meta">segera dibeli</div><div class="ico-r"><i class="fa-solid fa-circle-xmark"></i></div></div>
</div>

<div class="card">
  <div class="card-head"><div><h3>Stok bahan</h3><div class="sub">Sisa dihitung setelah pemakaian menu hari ini</div></div>
    <div class="right">
      <form method="post" action="/po" style="margin:0">
        @csrf
        <button class="btn sm">Buat PO</button>
      </form>
    </div>
  </div>
  <div class="table-wrap"><table>
    <thead><tr><th>Item</th><th>Jenis</th><th>Satuan</th><th class="num">Stok</th><th class="num">Minimum</th><th class="num">Dipakai hari ini</th><th class="num">Sisa</th><th>Status</th><th class="num">Harga</th></tr></thead>
    <tbody>
      @foreach($items as $i)
        @php
          $status = $i->stok <= 0 ? ['red','× habis'] : ($i->stok < $i->minimum ? ['orange','! menipis'] : ['green','✓ aman']);
        @endphp
        <tr>
          <td class="strong">{{ $i->nama }}</td>
          <td><span class="chip {{ $i->jenis === 'bahan' ? 'violet' : 'sky' }}">{{ $i->jenis }}</span></td>
          <td>{{ $i->satuan }}</td>
          <td class="num">{{ $num($i->stok) }}</td>
          <td class="num">{{ $num($i->minimum) }}</td>
          <td class="num">{{ $num($i->terpakai) }}</td>
          <td class="num" style="{{ $i->sisa < 0 ? 'color:#C2273C' : '' }}">{{ $num($i->sisa) }}</td>
          <td><span class="chip {{ $status[0] }}">{{ $status[1] }}</span></td>
          <td class="num">{{ number_format($i->harga, 0, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="footnote">Sisa negatif berarti stok tidak cukup untuk menu hari ini — aplikasi mengusulkan PO dari selisih tersebut.</div>
</div>

<div class="grid g-2" style="margin-top:14px">
  <div class="card" id="form-mutasi">
    <div class="card-head"><div><h3>Catat mutasi</h3><div class="sub">Masuk, keluar, atau opname</div></div></div>
    <form method="post" action="/stok/mutasi">
      @csrf
      <label class="field">Item
        <select name="stok_item_id" required>
          @foreach($items as $i)<option value="{{ $i->id }}">{{ $i->nama }} ({{ $num($i->stok) }} {{ $i->satuan }})</option>@endforeach
        </select>
      </label>
      <div class="formgrid" style="grid-template-columns:1fr 1fr;margin-top:8px">
        <label class="field">Jenis
          <select name="jenis"><option value="masuk">Masuk</option><option value="keluar">Keluar</option><option value="opname">Opname (set stok)</option></select>
        </label>
        <label class="field">Qty<input type="number" name="qty" step="0.01" required></label>
      </div>
      <label class="field" style="margin-top:8px">Keterangan<input type="text" name="keterangan" placeholder="mis. PO-2604-011"></label>
      <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Simpan mutasi</button>
    </form>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Mutasi terakhir</h3><div class="sub">8 transaksi</div></div></div>
    <div class="table-wrap"><table>
      <thead><tr><th>Tanggal</th><th>Item</th><th>Jenis</th><th class="num">Qty</th><th>Keterangan</th></tr></thead>
      <tbody>
        @forelse($mutasi as $m)
          <tr>
            <td>{{ \Illuminate\Support\Carbon::parse($m->tanggal)->format('d M') }}</td>
            <td>{{ $m->nama }}</td>
            <td><span class="chip {{ $m->jenis === 'masuk' ? 'green' : ($m->jenis === 'keluar' ? 'orange' : 'violet') }}">{{ $m->jenis }}</span></td>
            <td class="num">{{ $num($m->qty) }} {{ $m->satuan }}</td>
            <td class="muted">{{ $m->keterangan ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Belum ada mutasi.</td></tr>
        @endforelse
      </tbody>
    </table></div>
  </div>
</div>

<div class="card" style="margin-top:14px" id="po-manual">
  <div class="card-head"><div><h3>PO manual — nilai bisa diubah</h3><div class="sub">Nama, qty, satuan, dan harga bebas diisi; pilih item stok untuk mengisi otomatis</div></div></div>
  <form method="post" action="/po/manual">
    @csrf
    <div class="table-wrap"><table>
      <thead><tr><th>Item stok (opsional)</th><th>Nama barang</th><th class="num">Qty</th><th>Satuan</th><th class="num">Harga satuan</th><th class="num">Subtotal</th></tr></thead>
      <tbody>
        @for($i = 0; $i < 2; $i++)
          <tr>
            <td>
              <select name="items[{{ $i }}][stok_item_id]" class="po-src" data-i="{{ $i }}" style="min-width:150px">
                <option value="">— bebas —</option>
                @foreach($items as $it)
                  <option value="{{ $it->id }}" data-nama="{{ $it->nama }}" data-satuan="{{ $it->satuan }}" data-harga="{{ $it->harga }}">{{ $it->nama }}</option>
                @endforeach
              </select>
            </td>
            <td><input name="items[{{ $i }}][nama]" class="po-nama" data-i="{{ $i }}" placeholder="mis. Beras medium"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][qty]" class="po-qty" data-i="{{ $i }}" value="{{ $i === 0 ? 1 : '' }}"></td>
            <td><input name="items[{{ $i }}][satuan]" class="po-satuan" data-i="{{ $i }}" value="kg" style="width:64px"></td>
            <td><input type="number" name="items[{{ $i }}][harga]" class="po-harga" data-i="{{ $i }}" placeholder="0"></td>
            <td class="num"><span class="po-sub" data-i="{{ $i }}">Rp 0</span></td>
          </tr>
        @endfor
      </tbody>
    </table></div>
    <label class="field" style="margin-top:10px">Catatan PO<input name="catatan" placeholder="mis. belanja pasar tradisional, termin 7 hari"></label>
    <div style="display:flex;align-items:center;gap:10px;margin-top:10px">
      <button class="btn">Buat PO manual</button>
      <span class="muted" style="font-size:11px">Total: <b id="po-total">Rp 0</b></span>
    </div>
  </form>
  <div class="footnote">Baris kosong diabaikan. Harga yang diisi di sini tidak mengubah harga master bahan.</div>
</div>

<script>
  (function () {
    function rp(n) { return 'Rp ' + (n || 0).toLocaleString('id-ID'); }
    function recalc() {
      var total = 0;
      document.querySelectorAll('.po-harga').forEach(function (h) {
        var i = h.dataset.i;
        var qty = parseFloat(document.querySelector('.po-qty[data-i="' + i + '"]').value) || 0;
        var harga = parseFloat(h.value) || 0;
        var sub = Math.round(qty * harga);
        total += sub;
        document.querySelector('.po-sub[data-i="' + i + '"]').textContent = rp(sub);
      });
      document.getElementById('po-total').textContent = rp(total);
    }
    document.querySelectorAll('.po-src').forEach(function (sel) {
      sel.addEventListener('change', function () {
        var i = sel.dataset.i, o = sel.options[sel.selectedIndex];
        if (!o.value) return;
        document.querySelector('.po-nama[data-i="' + i + '"]').value = o.dataset.nama || '';
        document.querySelector('.po-satuan[data-i="' + i + '"]').value = o.dataset.satuan || 'kg';
        document.querySelector('.po-harga[data-i="' + i + '"]').value = o.dataset.harga || 0;
        recalc();
      });
    });
    document.querySelectorAll('.po-qty,.po-harga').forEach(function (el) { el.addEventListener('input', recalc); });
  })();
</script>

<div class="card" style="margin-top:14px">
  <div class="card-head"><div><h3>Purchase order</h3><div class="sub">Alur: menunggu → disetujui → diterima. Stok bertambah saat barang diterima.</div></div><div class="right"><span class="chip violet">{{ $po->count() }} PO</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Nomor</th><th>Tanggal</th><th>Item</th><th class="num">Total</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      @php($peran = auth()->user()->peran ?? '')
      @forelse($po as $p)
        <tr>
          <td class="strong">{{ $p->nomor }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
          <td>
            @foreach(($poItems[$p->id] ?? collect()) as $it)
              <div style="font-size:11.4px">{{ $it->nama }} {{ rtrim(rtrim(number_format($it->qty, 2, ',', '.'), '0'), ',') }} {{ $it->satuan }}</div>
            @endforeach
          </td>
          <td class="num">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
          <td>
            @php($warna = ['menunggu' => 'orange', 'disetujui' => 'sky', 'diterima' => 'green', 'ditolak' => 'red'][$p->status] ?? 'violet')
            <span class="chip {{ $warna }}">{{ $p->status }}</span>
            @if($p->disetujui_pada)
              <div class="sub" style="margin-top:3px;font-size:10px">
                {{ $p->status === 'ditolak' ? 'ditolak' : 'disetujui' }} oleh {{ $poUsers[$p->disetujui_oleh] ?? '—' }}
                · {{ \Illuminate\Support\Carbon::parse($p->disetujui_pada)->format('d/m H:i') }}
              </div>
            @endif
            @if($p->diterima_pada)
              <div class="sub" style="font-size:10px">diterima oleh {{ $poUsers[$p->diterima_oleh] ?? '—' }}
                · {{ \Illuminate\Support\Carbon::parse($p->diterima_pada)->format('d/m H:i') }}</div>
            @endif
            @if($p->alasan_tolak)
              <div class="sub" style="font-size:10px;color:#C2273C">{{ $p->alasan_tolak }}</div>
            @endif
          </td>
          <td style="white-space:nowrap">
            @if($p->status === 'menunggu' && in_array($peran, ['admin', 'akuntan'], true))
              <x-act type="approve" :action="route('po.setujui', $p->id)" confirm="Setujui PO {{ $p->nomor }}?" />
              <form method="post" action="{{ route('po.tolak', $p->id) }}" style="display:inline"
                    onsubmit="var a=prompt('Alasan menolak {{ $p->nomor }}?'); if(!a||!a.trim()) return false; this.alasan_tolak.value=a.trim(); return true;">
                @csrf
                <input type="hidden" name="alasan_tolak">
                <button type="submit" class="act act-del" title="Tolak PO" aria-label="Tolak PO"><i class="fa-solid fa-ban"></i></button>
              </form>
            @elseif($p->status === 'disetujui' && in_array($peran, ['admin', 'dapur'], true))
              <x-act type="receive" :action="route('po.terima', $p->id)" confirm="Terima barang PO {{ $p->nomor }}? Stok akan bertambah." />
            @else
              <span class="sub">—</span>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="empty">Belum ada PO. Tekan “Buat PO” untuk membuat dari selisih stok.</td></tr>
      @endforelse
    </tbody>
  </table></div>
</div>

@endsection
