<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · Menu MBG</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/css/ui.css">
<style>
  body{display:flex;min-height:100vh;align-items:center;justify-content:center;background:
    radial-gradient(900px 400px at 15% 5%, #EDE7FF 0, transparent 60%),
    radial-gradient(700px 380px at 90% 90%, #E7FBF4 0, transparent 55%), var(--bg)}
  .auth{width:920px;max-width:94vw;display:grid;grid-template-columns:1.05fr .95fr;background:#fff;border:1px solid var(--line);border-radius:26px;overflow:hidden;box-shadow:0 24px 60px rgba(79,70,229,.14)}
  .auth .left{padding:38px 36px;background:linear-gradient(150deg,#7B4DFF 0%,#5B3BE8 55%,#22A6FF 130%);color:#fff;display:flex;flex-direction:column}
  .auth .left h1{font-size:26px;margin:18px 0 8px;letter-spacing:-.6px}
  .auth .left p{color:rgba(255,255,255,.86);font-size:12.5px;line-height:1.65;margin:0}
  .auth .logo{width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px}
  .auth .badges{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto;padding-top:24px}
  .auth .badges span{background:rgba(255,255,255,.16);border-radius:99px;padding:4px 10px;font-size:10.5px}
  .auth .right{padding:34px 32px}
  .auth .right h2{margin:0 0 4px;font-size:18px}
  .auth .right .sub{margin:0 0 18px;color:var(--muted);font-size:12px}
  .auth .field input{width:100%}
  .auth button{width:100%;justify-content:center;margin-top:14px}
  .demo{margin-top:18px;border-top:1px solid var(--line);padding-top:12px;font-size:11px;color:var(--muted);line-height:1.9}
  .demo code{background:#F1EBFF;color:#5B2BE0;border-radius:6px;padding:1px 6px;font-size:10.5px}
  .roles{margin-top:14px;border-top:1px solid var(--line);padding-top:12px;display:flex;flex-direction:column;gap:7px}
  .roles .role{display:flex;align-items:flex-start;gap:8px;font-size:10.5px;line-height:1.4}
  .roles .role b{flex:0 0 78px;color:var(--ink);font-size:10.5px}
  .roles .role span{color:var(--muted);min-width:0;max-width:100%;overflow-wrap:anywhere}
  .auth .right{min-width:0}
  .roles{min-width:0;max-width:100%}
  .roles .role{min-width:0;max-width:100%}
  .demo{overflow-wrap:anywhere}
  @media(max-width:760px){
    .auth{grid-template-columns:1fr}
    .auth .left{display:none}
    .auth .right{padding:26px 20px}
    .roles .role{flex-direction:column;gap:1px}
    .roles .role b{flex:none}
  }
</style>
</head>
<body>
  <div class="auth">
    <div class="left">
      <div class="logo">M</div>
      <h1>Menu MBG</h1>
      <p>Perencanaan menu, capaian gizi, kebutuhan bahan, deteksi alergi, dan stok dalam satu alur kerja SPPG.</p>
      <div class="badges"><span>TKPI 2018</span><span>AKG 2019</span><span>DBMP ed. 5</span><span>Siklus 5/10 · 6/12</span></div>
    </div>
    <div class="right">
      <h2>Masuk</h2>
      <p class="sub">Gunakan akun SPPG Anda.</p>

      @if($errors->any())
        <div class="alert bad">⚠ {{ $errors->first() }}</div>
      @endif

      <form method="post" action="/login">
        @csrf
        <label class="field">Email
          <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>
        <label class="field" style="margin-top:10px">Kata sandi
          <input type="password" name="password" required>
        </label>
        <label style="display:flex;align-items:center;gap:7px;margin-top:10px;font-size:11.5px;color:#39405A">
          <input type="checkbox" name="ingat" value="1"> Ingat saya
        </label>
        <button class="btn">Masuk</button>
      </form>

      <div class="demo">
        <b>Akun demo</b> — kata sandi semua: <code>password</code><br>
        <code>admin@wareg.id</code> — Admin SPPG<br>
        <code>rina@wareg.id</code> — Ahli Gizi<br>
        <code>sari@wareg.id</code> — Petugas Dapur<br>
        <code>haccp@wareg.id</code> — Pengawas<br>
        <code>akuntan@wareg.id</code> — Akuntan
      </div>

      <div class="roles">
        <div class="role"><b>Admin SPPG</b><span>Akses penuh: pengguna, pengaturan, menu, master data, stok, PO, dan audit.</span></div>
        <div class="role"><b>Ahli Gizi</b><span>Menyusun menu, bahan, kategori, varian, komposisi, dan capaian gizi.</span></div>
        <div class="role"><b>Petugas Dapur</b><span>Mengelola stok, PO, HACCP, dan sisa makanan; tidak mengubah menu atau master.</span></div>
        <div class="role"><b>Pengawas</b><span>Memantau menu, gizi, HACCP, stok, dan laporan; hanya baca.</span></div>
        <div class="role"><b>Akuntan</b><span>Mengelola belanja/PO, menyetujui PO, dan membuka audit log; tidak mengubah menu.</span></div>
      </div>
    </div>
  </div>
</body>
</html>
