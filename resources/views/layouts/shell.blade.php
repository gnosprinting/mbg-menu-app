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
<link rel="stylesheet" href="/css/ui.css?v=11">
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
$boleh = fn (string $fitur) => \App\Services\Akses::boleh($fitur);
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
    @if($boleh('master.lihat'))
      <a class="nav {{ $nav('master-penerima') }}" href="/master-penerima"><i class="fa-solid {{ $I['users'] }}"></i> Master Penerima</a>
      <a class="nav {{ $nav('master-bahan') }}" href="/master-bahan"><i class="fa-solid {{ $I['leaf'] }}"></i> Master Bahan</a>
      <a class="nav {{ $nav('master-kategori') }}" href="/master-kategori"><i class="fa-solid {{ $I['target'] }}"></i> Master Kategori</a>
      <a class="nav {{ $nav('master-alergen') }}" href="/master-alergen"><i class="fa-solid {{ $I['alert'] }}"></i> Master Alergen</a>
      <a class="nav {{ $nav('sumber') }}" href="/sumber"><i class="fa-solid {{ $I['file'] }}"></i> Data Sumber</a>
    @endif
    <a class="nav {{ $nav('laporan') }}" href="/laporan"><i class="fa-solid {{ $I['file'] }}"></i> Laporan</a>
    @if($boleh('audit.lihat'))
      <a class="nav {{ $nav('audit') }}" href="/audit"><i class="fa-solid fa-clipboard-list"></i> Audit Log</a>
    @endif
    @if($boleh('pengguna.kelola'))
      <a class="nav {{ $nav('pengguna') }}" href="/pengguna"><i class="fa-solid {{ $I['users'] }}"></i> Pengguna</a>
    @endif
    @if($boleh('pengaturan.lihat'))
      <a class="nav {{ $nav('pengaturan') }}" href="/pengaturan"><i class="fa-solid {{ $I['gear'] }}"></i> Pengaturan</a>
    @endif

    <div class="spacer"></div>
    <div class="profile">
      @php
        $u = auth()->user();
        $ini = collect(explode(' ', $u->name ?? 'MB'))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $role = \App\Services\Akses::label($u->peran ?? null);
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
        @if($boleh('pengaturan.lihat'))
          <a class="icon-btn hide-sm" href="/pengaturan" title="Pengaturan"><i class="fa-solid {{ $I['gear'] }}"></i></a>
        @endif
        @if($boleh('menu.tulis'))
          <a class="btn" href="/siklus"><i class="fa-solid {{ $I['plus'] }}"></i> <span class="btn-label">Buat Menu</span></a>
        @endif
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
<script>
  // Tandai tabel yang lebih lebar dari layar dan tampilkan petunjuk geser.
  // Tabel tetap dapat digeser horizontal; petunjuk hanya muncul di mobile.
  (function () {
    function markTables() {
      document.querySelectorAll('.table-wrap').forEach(function (wrap) {
        var scrollable = wrap.scrollWidth > wrap.clientWidth + 4;
        wrap.classList.toggle('is-scrollable', scrollable);
        var prev = wrap.previousElementSibling;
        if (scrollable && (!prev || !prev.classList.contains('table-hint'))) {
          var hint = document.createElement('div');
          hint.className = 'table-hint';
          hint.innerHTML = '<i class="fa-solid fa-arrows-left-right"></i> Geser tabel untuk melihat kolom lain';
          wrap.parentNode.insertBefore(hint, wrap);
        } else if (!scrollable && prev && prev.classList.contains('table-hint')) {
          prev.remove();
        }
      });
    }
    document.addEventListener('DOMContentLoaded', function () {
      markTables();
      setTimeout(markTables, 250);
    });
    window.addEventListener('resize', markTables);
  })();
</script>
</body>
</html>
