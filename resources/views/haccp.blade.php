@extends('layouts.shell')
@section('title','HACCP')
@section('content')
@php $aman = $logs->where('status','aman')->count(); $total = max($logs->count(),1); @endphp

<div class="page-top">
  <div><h1>HACCP</h1><p>Checklist harian titik kendali kritis · {{ now()->translatedFormat('l, j F Y') }}</p></div>
  <div class="actions"><button class="btn ghost">Riwayat</button><button class="btn">Isi checklist</button></div>
</div>

<div class="kpis">
  <div class="kpi" style="--tint:var(--lilac);--accent:var(--violet)"><div class="top"><span class="lbl">Titik kontrol</span></div><div class="val">{{ $logs->count() }}</div><div class="meta">suhu, waktu, kebersihan</div></div>
  <div class="kpi" style="--tint:var(--mint);--accent:var(--emerald)"><div class="top"><span class="lbl">Sesuai batas</span></div><div class="val">{{ $aman }}</div><div class="meta">{{ round($aman / $total * 100) }}% checklist</div></div>
  <div class="kpi" style="--tint:var(--rosebg);--accent:var(--rose)"><div class="top"><span class="lbl">Deviasi</span></div><div class="val">{{ $deviasi }}</div><div class="meta">perlu tindakan</div></div>
  <div class="kpi" style="--tint:var(--skybg);--accent:var(--sky)"><div class="top"><span class="lbl">Suhu masak</span></div><div class="val">{{ $logs->firstWhere('titik_kontrol','Suhu masak inti')->nilai ?? '—' }}</div><div class="meta">batas minimal 70 °C</div></div>
</div>

@if($deviasi)
  <div class="alert bad">⚠ {{ $deviasi }} deviasi belum ditutup. Lengkapi tindakan korektif sebelum layanan berikutnya.</div>
@endif

<div class="card">
  <div class="card-head"><div><h3>Checklist hari ini</h3><div class="sub">Diisi petugas dapur · diverifikasi pengawas</div></div><div class="right"><span class="chip green">{{ $aman }} dari {{ $logs->count() }}</span></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Titik kontrol</th><th>Batas</th><th>Nilai</th><th>Status</th><th>Tindakan</th></tr></thead>
    <tbody>
      @foreach($logs as $l)
        <tr>
          <td class="strong">{{ $l->titik_kontrol }}</td>
          <td>{{ $l->batas }}</td>
          <td>{{ $l->nilai }}</td>
          <td><span class="chip {{ $l->status === 'aman' ? 'green' : 'red' }}">{{ $l->status === 'aman' ? '✓ aman' : '× deviasi' }}</span></td>
          <td class="muted">{{ $l->tindakan ?: '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>

@if(session('ok'))<div class="alert ok">✓ {{ session('ok') }}</div>@endif

@php $dev = $logs->where('status','deviasi'); @endphp

<div class="grid g-2" style="margin-top:14px">
  <div class="card">
    <div class="card-head"><div><h3>Catat checklist</h3><div class="sub">Satu baris per titik kontrol</div></div></div>
    <form method="post" action="/haccp" data-offline>
      @csrf
      <div class="formgrid" style="grid-template-columns:1fr 1fr">
        <label class="field">Titik kontrol<input name="titik_kontrol" placeholder="Suhu chiller" required></label>
        <label class="field">Batas<input name="batas" placeholder="≤ 4 °C" required></label>
        <label class="field">Nilai<input name="nilai" placeholder="3,0 °C" required></label>
        <label class="field">Status
          <select name="status"><option value="aman">Aman</option><option value="deviasi">Deviasi</option></select>
        </label>
      </div>
      <label class="field" style="margin-top:8px">Tindakan (wajib bila deviasi)<input name="tindakan"></label>
      <button class="btn" style="width:100%;justify-content:center;margin-top:12px">Simpan checklist</button>
    </form>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Tutup deviasi</h3><div class="sub">{{ $dev->count() }} deviasi terbuka</div></div></div>
    @forelse($dev as $d)
      <div style="border-top:1px solid var(--line-2);padding-top:10px;margin-top:10px">
        <b style="font-size:12px">{{ $d->titik_kontrol }}</b> <span class="muted">· {{ $d->nilai }}</span>
        <form method="post" action="/haccp/{{ $d->id }}/tutup" style="display:flex;gap:8px;margin-top:8px">
          @csrf
          <input name="tindakan" placeholder="Tindakan korektif" style="flex:1;border:1px solid var(--line);border-radius:10px;padding:7px 9px;font:inherit;font-size:11.8px" required>
          <button class="btn sm">Tutup</button>
        </form>
      </div>
    @empty
      <div class="empty">Tidak ada deviasi terbuka.</div>
    @endforelse
  </div>
</div>

@endsection
