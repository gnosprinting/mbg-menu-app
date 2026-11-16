@extends('layouts.shell')
@section('title','Audit Log')
@section('content')
@php
  $roleLabel = ['admin'=>'Admin','gizi'=>'Ahli gizi','dapur'=>'Dapur','pengawas'=>'Pengawas','akuntan'=>'Akuntan','tamu'=>'Tamu'];
  $roleChip = ['admin'=>'violet','gizi'=>'green','dapur'=>'sky','pengawas'=>'orange','akuntan'=>'violet','tamu'=>'red'];
@endphp

<div class="page-top">
  <div><h1>Audit Log</h1><p>Jejak aksi tulis: siapa, kapan, modul apa, dan data yang dikirim</p></div>
</div>

<div class="kpis">
  <div class="kpi" style="--accent:#7B4DFF"><div class="top"><span class="lbl">{{ $filter ? 'Hasil filter' : 'Total entri' }}</span></div><div class="val">{{ number_format($ringkas['total'], 0, ',', '.') }}</div><div class="meta">{{ $filter ? 'dari '.number_format($ringkas['semua'], 0, ',', '.').' entri' : 'tersimpan di audit_log' }}</div><div class="ico-r"><i class="fa-solid fa-clipboard-list"></i></div></div>
  <div class="kpi" style="--accent:#22A6FF"><div class="top"><span class="lbl">Hari ini</span></div><div class="val">{{ number_format($ringkas['hari'], 0, ',', '.') }}</div><div class="meta">aksi tercatat</div><div class="ico-r"><i class="fa-solid fa-calendar-day"></i></div></div>
  <div class="kpi" style="--accent:#12B981"><div class="top"><span class="lbl">Pengguna unik</span></div><div class="val">{{ $ringkas['user'] }}</div><div class="meta">pelaku tercatat</div><div class="ico-r"><i class="fa-solid fa-users"></i></div></div>
  <div class="kpi" style="--accent:#FFB020"><div class="top"><span class="lbl">Halaman</span></div><div class="val">{{ $log->currentPage() }} / {{ $log->lastPage() }}</div><div class="meta">{{ $log->perPage() }} per halaman</div><div class="ico-r"><i class="fa-solid fa-file-lines"></i></div></div>
</div>

<form method="get" action="/audit" class="toolbar">
  <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari pengguna, peran, modul, target, atau detail…" style="min-width:260px">
  <select name="peran" onchange="this.form.submit()">
    <option value="">Semua peran</option>
    @foreach($roleLabel as $k => $v)<option value="{{ $k }}" @selected(request('peran') === $k)>{{ $v }}</option>@endforeach
  </select>
  <button class="btn ghost sm">Filter</button>
  <a class="btn ghost sm" href="/audit">Reset</a>
</form>

<div class="card">
  <div class="card-head"><div><h3>Jejak aksi</h3><div class="sub">POST/PUT/DELETE otomatis dicatat middleware AuditLog</div></div>
    <div class="right">@if($filter)<span class="chip violet">{{ $log->total() }} hasil</span>@endif</div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Waktu</th><th>Pengguna</th><th>Peran</th><th>Modul</th><th>Target</th><th>Detail</th><th>IP</th></tr></thead>
    <tbody>
      @forelse($log as $l)
        <tr>
          <td style="white-space:nowrap">{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('d M H:i:s') }}</td>
          <td class="strong">{{ $l->user_nama ?? '—' }}</td>
          <td><span class="chip {{ $roleChip[$l->peran] ?? 'violet' }}">{{ $roleLabel[$l->peran] ?? $l->peran }}</span></td>
          <td><span class="muted" style="font-size:10.5px">{{ $l->modul }}</span></td>
          <td><span class="chip sky">{{ $l->target }}</span></td>
          <td style="max-width:340px"><span class="muted" style="font-size:10.5px">{{ \Illuminate\Support\Str::limit($l->detail, 120) }}</span></td>
          <td class="muted" style="font-size:10px">{{ $l->ip }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="empty">Belum ada aktivitas tercatat.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  @if($log->hasPages())
    <div style="display:flex;gap:8px;align-items:center;margin-top:12px;flex-wrap:wrap">
      @if(!$log->onFirstPage())<a class="btn ghost sm" href="{{ $log->previousPageUrl() }}">← Sebelumnya</a>@endif
      <span class="muted" style="font-size:11.5px">Halaman {{ $log->currentPage() }} dari {{ $log->lastPage() }}</span>
      @if($log->hasMorePages())<a class="btn ghost sm" href="{{ $log->nextPageUrl() }}">Berikutnya →</a>@endif
    </div>
  @endif
</div>

@endsection
