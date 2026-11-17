<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Menu MBG') · SPPG</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/css/ui.css?v=8">
<script>document.documentElement.dataset.theme = localStorage.getItem("mbg-theme") || "light";</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="/js/search-select.js?v=2" defer></script>
<script src="/js/offline-queue.js" defer></script>
</head>
<body>
@php
$I = [
 'home'     => 'fa-house',
 'calendar' => 'fa-calendar-days',
 'leaf'     => 'fa-leaf',
 'target'   => 'fa-bullseye',
 'cart'     => 'fa-cart-shopping',
 'alert'    => 'fa-triangle-exclamation',
 'shield'   => 'fa-shield-halved',
 'box'      => 'fa-box',
 'trash'    => 'fa-trash-can',
 'users'    => 'fa-users',
 'flask'    => 'fa-flask',
 'file'     => 'fa-file-lines',
 'gear'     => 'fa-gear',
 'search'   => 'fa-magnifying-glass',
 'bell'     => 'fa-bell',
 'plus'     => 'fa-plus',
 'download' => 'fa-download',
 'logout'   => 'fa-arrow-right-from-bracket',
];
$nav = fn ($path) => request()->is($path) ? 'on' : '';
@endphp

<div class="app">
  <aside class="side">
    <div class="brand">
      @php $logo = DB::table('pengaturan')->where('kunci','logo')->value('nilai'); @endphp
      @if($logo)
        <img src="{{ Storage::url($logo) }}" alt="Logo" style="width:36px;height:36px;border-radius:12px;object-fit:cover">
      @else
        <div class="logo">M</div>
      @endif
      <div><b>Menu MBG</b><small>{{ DB::table('pengaturan')->where('kunci','sppg_nama')->value('nilai') ?? 'SPPG' }}</small></div>
    </div>

    <div class="nav-label">Utama</div>
    <a class="nav {{ $nav('/') }}" href="/"><i class="fa-solid {{ $I['home'] }}"></i> Beranda</a>
    <a class="nav {{ $nav('siklus') }}" href="/siklus"><i class="fa-solid {{ $I['calendar'] }}"></i> Siklus Menu</a>
    <a class="nav {{ $nav('komposisi') }}" href="/komposisi"><i class="fa-solid {{ $I['leaf'] }}"></i> Komposisi &amp; Gizi</a>
    <a class="nav {{ $nav('capaian') }}" href="/capaian"><i class="fa-solid {{ $I['target'] }}"></i> Capaian</a>
    <a class="nav {{ $nav('kebutuhan') }}" href="/kebutuhan"><i class="fa-solid {{ $I['cart'] }}"></i> Kebutuhan Bahan</a>

    <div class="nav-label">Operasional</div>
    <a class="nav {{ $nav('alergi') }}{{ request()->is('varian') ? ' on' : '' }}" href="/alergi"><i class="fa-solid {{ $I['alert'] }}"></i> Alergi &amp; Varian <span class="count">2</span></a>
    <a class="nav {{ $nav('stok') }}" href="/stok"><i class="fa-solid {{ $I['box'] }}"></i> Stok</a>
    <a class="nav {{ $nav('haccp') }}" href="/haccp"><i class="fa-solid {{ $I['shield'] }}"></i> HACCP</a>
    <a class="nav {{ $nav('sisa') }}" href="/sisa"><i class="fa-solid {{ $I['trash'] }}"></i> Sisa Makanan</a>

    <div class="nav-label">Data &amp; sistem</div>
    <a class="nav {{ $nav('master-penerima') }}" href="/master-penerima"><i class="fa-solid {{ $I['users'] }}"></i> Master Penerima</a>
    <a class="nav {{ $nav('master-bahan') }}" href="/master-bahan"><i class="fa-solid {{ $I['leaf'] }}"></i> Master Bahan</a>
    <a class="nav {{ $nav('master-kategori') }}" href="/master-kategori"><i class="fa-solid {{ $I['target'] }}"></i> Master Kategori</a>
    <a class="nav {{ $nav('master-alergen') }}" href="/master-alergen"><i class="fa-solid {{ $I['alert'] }}"></i> Master Alergen</a>
    <a class="nav {{ $nav('sumber') }}" href="/sumber"><i class="fa-solid {{ $I['file'] }}"></i> Data Sumber</a>
    <a class="nav {{ $nav('laporan') }}" href="/laporan"><i class="fa-solid {{ $I['file'] }}"></i> Laporan</a>
    @if(in_array(auth()->user()->peran, ['admin','akuntan']))
      <a class="nav {{ $nav('audit') }}" href="/audit"><i class="fa-solid fa-clipboard-list"></i> Audit Log</a>
    @endif
    <a class="nav {{ $nav('pengguna') }}" href="/pengguna"><i class="fa-solid {{ $I['users'] }}"></i> Pengguna</a>
    <a class="nav {{ $nav('pengaturan') }}" href="/pengaturan"><i class="fa-solid {{ $I['gear'] }}"></i> Pengaturan</a>

    <div class="spacer"></div>
    <div class="profile">
      @php
        $u = auth()->user();
        $ini = collect(explode(' ', $u->name ?? 'MB'))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $role = ['admin'=>'Admin SPPG','gizi'=>'Ahli gizi','dapur'=>'Petugas dapur','pengawas'=>'Pengawas','akuntan'=>'Akuntan'][$u->peran ?? ''] ?? 'Pengguna';
      @endphp
      <div class="av">{{ strtoupper($ini) }}</div>
      <div><b>{{ $u->name ?? 'Pengguna' }}</b><small>{{ $role }} · SPPG Wareg</small></div>
    </div>
  </aside>
  <div class="nav-backdrop" id="nav-backdrop"></div>

  <div class="main">
    <header class="head">
      <button class="icon-btn menu-btn" id="menu-btn" aria-label="Buka menu" aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="m-brand"><div class="logo">M</div></div>
      <div class="search">
        <i class="fa-solid {{ $I['search'] }}"></i>
        <input placeholder="Cari menu, bahan, atau laporan...">
        <kbd>⌘K</kbd>
      </div>
      <div class="right">
        <button class="icon-btn"><i class="fa-solid {{ $I['bell'] }}"></i><span class="dot"></span></button>
        <button class="icon-btn hide-sm" id="theme-btn" title="Tema gelap/terang"><i class="fa-solid fa-moon"></i></button>
        <button class="icon-btn hide-sm"><i class="fa-solid {{ $I['gear'] }}"></i></button>
        <a class="btn" href="/siklus"><i class="fa-solid {{ $I['plus'] }}"></i> <span class="btn-label">Buat Menu</span></a>
        <form method="post" action="/logout" style="margin:0">
          @csrf
          <button class="icon-btn" title="Keluar"><i class="fa-solid {{ $I['logout'] }}"></i></button>
        </form>
        <div class="avatar">{{ strtoupper($ini ?? 'M') }}</div>
      </div>
    </header>

    <main class="content">
      @yield('content')
    </main>
  </div>
</div>
<script>
  (function () {
    var btn = document.getElementById('theme-btn');
    if (!btn) return;
    var icon = btn.querySelector('i');
    function paint() {
      var dark = document.documentElement.dataset.theme === 'dark';
      icon.className = 'fa-solid ' + (dark ? 'fa-sun' : 'fa-moon');
    }
    btn.addEventListener('click', function () {
      var dark = document.documentElement.dataset.theme === 'dark';
      document.documentElement.dataset.theme = dark ? 'light' : 'dark';
      localStorage.setItem('mbg-theme', dark ? 'light' : 'dark');
      paint();
    });
    paint();
  })();
</script>
<script>
  (function () {
    var btn = document.getElementById('menu-btn');
    var side = document.querySelector('.side');
    var bd = document.getElementById('nav-backdrop');
    if (!btn || !side || !bd) return;
    function setNav(open) {
      side.classList.toggle('open', open);
      bd.classList.toggle('show', open);
      document.body.classList.toggle('nav-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    btn.addEventListener('click', function () { setNav(!side.classList.contains('open')); });
    bd.addEventListener('click', function () { setNav(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setNav(false); });
    side.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { setNav(false); });
    });
  })();
</script>
</body>
</html>
