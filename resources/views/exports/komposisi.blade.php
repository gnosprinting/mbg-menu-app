<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<style>
  @page { margin: 18mm 14mm; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #1b1b1b; }
  h1 { font-size: 14pt; color: #4A25C9; margin: 0 0 2mm; }
  .sub { font-size: 9pt; color: #555; margin: 0 0 6mm; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #5B2BE0; color: #fff; font-size: 7.5pt; text-align: left; padding: 2mm 1.5mm; }
  td { border-bottom: .3mm solid #ddd; padding: 1.6mm 1.5mm; font-size: 8pt; }
  td.r, th.r { text-align: right; }
  tr.total td { background: #F1EBFF; font-weight: bold; }
  .note { margin-top: 5mm; font-size: 7.5pt; color: #666; }
  .badge { display: inline-block; padding: 1mm 3mm; border-radius: 3mm; background: #E7FBF2; color: #087F5B; font-size: 7.5pt; }
</style></head>
<body>
  <h1>Komposisi &amp; Gizi — {{ $kat->nama }}</h1>
  <p class="sub">Hari {{ $hari }} · {{ $menu }} · tabel 9 · sumber TKPI 2018 &amp; DBMP edisi 5</p>
  <table>
    <thead><tr><th>Bahan</th><th>Kelompok</th><th>SP</th><th>URT</th><th class="r">Berat (g)</th><th class="r">Energi</th><th class="r">Protein</th><th class="r">Lemak</th><th class="r">KH</th><th class="r">Serat</th><th class="r">Vit A</th><th class="r">Vit B</th><th class="r">Natrium</th></tr></thead>
    <tbody>
      @foreach($data['rows'] as $b)
        <tr>
          <td>{{ $b['nama'] }}</td><td>{{ $b['kelompok'] }}</td><td>{{ $b['sp'] }}</td><td>{{ $b['urt'] }}</td>
          <td class="r">{{ number_format($b['gram'], 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['energi'], 0, ',', '.') }}</td>
          <td class="r">{{ number_format($b['protein'], 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['lemak'], 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['kh'], 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['serat'] ?? 0, 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['vit_a'] ?? 0, 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['vit_b'] ?? 0, 2, ',', '.') }}</td>
          <td class="r">{{ number_format($b['natrium'], 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total">
        <td colspan="4">Sub total</td>
        <td class="r">{{ number_format($data['total']['gram'], 1, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['energi'], 0, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['protein'], 1, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['lemak'], 1, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['kh'], 1, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['serat'] ?? 0, 1, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['vit_a'] ?? 0, 1, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['vit_b'] ?? 0, 2, ',', '.') }}</td>
        <td class="r">{{ number_format($data['total']['natrium'], 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  <p class="note">
    Target 30% AKG — energi {{ number_format($data['target']['energi'], 0, ',', '.') }} kkal
    (capaian {{ round($data['pct']['energi']) }}%), protein {{ number_format($data['target']['protein'], 1, ',', '.') }} g
    ({{ round($data['pct']['protein']) }}%), lemak {{ number_format($data['target']['lemak'], 1, ',', '.') }} g
    ({{ round($data['pct']['lemak']) }}%), KH {{ number_format($data['target']['kh'], 1, ',', '.') }} g
    ({{ round($data['pct']['kh']) }}%).
  </p>
  <p class="note">Dicetak {{ now()->translatedFormat('j F Y H:i') }} · Menu MBG SPPG Wareg.</p>
</body></html>
