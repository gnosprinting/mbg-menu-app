<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<style>
  @page { margin: 16mm 13mm; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #1b1b1b; }
  h1 { font-size: 15pt; color: #4A25C9; margin: 0 0 2mm; }
  .sub { font-size: 9pt; color: #555; margin: 0 0 5mm; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #5B2BE0; color: #fff; font-size: 7.5pt; text-align: left; padding: 2mm 1.5mm; }
  td { border-bottom: .3mm solid #ddd; padding: 1.7mm 1.5mm; font-size: 8pt; }
  .bad { color: #C2273C; font-weight: bold; }
  .ok { color: #087F5B; font-weight: bold; }
  .note { margin-top: 5mm; font-size: 7.5pt; color: #666; }
</style></head>
<body>
  <h1>Laporan Deteksi Alergi</h1>
  <p class="sub">Hari {{ $hari }} · {{ $menu }} · {{ count($rows) }} PM alergi · dicetak {{ now()->translatedFormat('j F Y H:i') }}</p>
  <table>
    <thead><tr><th>Nama</th><th>Kelas</th><th>Kategori PM</th><th>Lokasi</th><th>Alergen</th><th>Bahan terpicu</th><th>Status</th></tr></thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r['nama'] }}</td>
          <td>{{ $r['kelas'] ?: '—' }}</td>
          <td>{{ $r['kategori'] }}</td>
          <td>{{ $r['lokasi'] }}</td>
          <td>{{ $r['alergen'] }}</td>
          <td>{{ $r['bahan'] ?: '—' }}</td>
          <td class="{{ $r['status'] === 'TERPICU' ? 'bad' : 'ok' }}">{{ $r['status'] }}</td>
        </tr>
      @empty
        <tr><td colspan="7">Belum ada PM alergi.</td></tr>
      @endforelse
    </tbody>
  </table>
  <p class="note">Pemeriksaan mencocokkan bahan menu dengan alergen tiap PM. Bukan pengganti pemeriksaan medis.</p>
  <p class="note">Menu MBG · SPPG Wareg</p>
</body></html>
