<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<style>
  @page { margin: 18mm 14mm; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #1b1b1b; }
  h1 { font-size: 14pt; color: #4A25C9; margin: 0 0 2mm; }
  .sub { font-size: 9pt; color: #555; margin: 0 0 6mm; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
  th { background: #5B2BE0; color: #fff; font-size: 7.5pt; text-align: left; padding: 2mm 1.5mm; }
  td { border-bottom: .3mm solid #ddd; padding: 1.6mm 1.5mm; font-size: 8pt; }
  td.r, th.r { text-align: right; }
  .cards td { border: 0; padding: 0 2mm 0 0; }
  .box { border: .3mm solid #ddd; border-radius: 3mm; padding: 3mm; width: 42mm; }
  .box .lbl { font-size: 7pt; color: #666; }
  .box .val { font-size: 14pt; font-weight: bold; color: #4A25C9; }
  .note { font-size: 7.5pt; color: #666; }
</style></head>
<body>
  <h1>Capaian Gizi — {{ $kat->nama }}</h1>
  <p class="sub">Hari {{ $hari }} · kebijakan MBG 25–30% AKG · target dipakai 30%</p>

  <table class="cards"><tr>
    @foreach([['Energi','energi','kkal'],['Protein','protein','g'],['Lemak','lemak','g'],['Karbohidrat','kh','g']] as [$lbl,$k,$u])
      <td><div class="box">
        <div class="lbl">{{ $lbl }}</div>
        <div class="val">{{ round($data['pct'][$k]) }}%</div>
        <div class="lbl">{{ number_format($data['total'][$k], $k === 'energi' ? 0 : 1, ',', '.') }} / {{ number_format($data['target'][$k], $k === 'energi' ? 0 : 1, ',', '.') }} {{ $u }}</div>
      </div></td>
    @endforeach
  </tr></table>

  <table>
    <thead><tr><th>Zat gizi</th><th class="r">AKG/hari</th><th class="r">Target 30%</th><th class="r">Hasil</th><th class="r">Capaian</th></tr></thead>
    <tbody>
      @foreach([['Energi','energi','akg_energi',0],['Protein','protein','akg_protein',1],['Lemak','lemak','akg_lemak',1],['Karbohidrat','kh','akg_kh',1]] as [$lbl,$k,$akg,$d])
        <tr>
          <td>{{ $lbl }}</td>
          <td class="r">{{ number_format($data['target'][$akg], $d, ',', '.') }}</td>
          <td class="r">{{ number_format($data['target'][$k], $d, ',', '.') }}</td>
          <td class="r">{{ number_format($data['total'][$k], $d, ',', '.') }}</td>
          <td class="r">{{ round($data['pct'][$k]) }}%</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table>
    <thead><tr><th>Kategori</th><th class="r">Energi</th><th class="r">Capaian</th><th class="r">Protein</th><th>Status</th></tr></thead>
    <tbody>
      @foreach($semua as $k)
        <tr><td>{{ $k->nama }}</td><td class="r">{{ number_format($k->energi, 0, ',', '.') }}</td>
          <td class="r">{{ round($k->pct) }}%</td><td class="r">{{ number_format($k->protein, 1, ',', '.') }}</td>
          <td>{{ ['aman'=>'aman','waspada'=>'waspada','kurang'=>'kurang','lebih'=>'lebih','tinggi'=>'tinggi'][$k->status] ?? $k->status }}</td></tr>
      @endforeach
    </tbody>
  </table>

  <p class="note">Dicetak {{ now()->translatedFormat('j F Y H:i') }} · Menu MBG SPPG Wareg.</p>
</body></html>
