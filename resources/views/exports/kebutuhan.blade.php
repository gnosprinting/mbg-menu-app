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
</style></head>
<body>
  <h1>Kebutuhan Bahan — {{ $kat->nama }}</h1>
  <p class="sub">Hari {{ $hari }} · {{ $porsi }} porsi · buffer 10% · harga per {{ now()->translatedFormat('j F Y') }}</p>
  <table>
    <thead><tr><th>Bahan</th><th class="r">Gram/porsi</th><th class="r">Kebutuhan</th><th class="r">Harga satuan</th><th class="r">Harga/porsi</th><th class="r">Subtotal</th></tr></thead>
    <tbody>
      @foreach($keb['rows'] as $b)
        <tr>
          <td>{{ $b['nama'] }}</td>
          <td class="r">{{ number_format($b['gram_porsi'], 1, ',', '.') }}</td>
          <td class="r">{{ number_format($b['qty'], 2, ',', '.') }} {{ $b['satuan'] }}</td>
          <td class="r">Rp {{ number_format($b['harga'], 0, ',', '.') }}</td>
          <td class="r">Rp {{ number_format($b['harga_porsi'], 0, ',', '.') }}</td>
          <td class="r">Rp {{ number_format($b['subtotal'], 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total"><td colspan="5">Total belanja</td><td class="r">Rp {{ number_format($keb['total'], 0, ',', '.') }}</td></tr>
    </tbody>
  </table>
  <p class="note">Pagu {{ $porsi }} porsi: Rp {{ number_format($pagu, 0, ',', '.') }} · terpakai {{ $pagu > 0 ? round($keb['total'] / $pagu * 100) : 0 }}%.</p>
  <p class="note">Dicetak {{ now()->translatedFormat('j F Y H:i') }} · Menu MBG SPPG Wareg.</p>
</body></html>
