<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
  // Animasi sidebar hanya sekali per sesi login; dihapus lagi saat logout / membuka halaman login
  try { if (!sessionStorage.getItem("sbIntro")) { document.documentElement.classList.add("intro"); sessionStorage.setItem("sbIntro", "1"); } } catch (e) {}
</script>
<title>@yield('title', 'Dashboard') — DompetKu</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
{{-- Aspekta tidak ada di Google Fonts: Inter dipakai sebagai substitusi sesuai pedoman --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300..700&family=Geist+Mono:wght@350..700&display=swap" rel="stylesheet">

<style>
:root{
  --ink:#0a1217; --paper:#ffffff; --gray:#f1f1f1; --hair:#e6e7e8;
  --stone:#85898b; --graphite:#54595d; --lime:#cdfe00; --plan:#24476b;
  --font:'Aspekta','Inter',ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
  --mono:'Geist Mono',ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
  --ease:cubic-bezier(.2,.7,.2,1);
  --sb-w:264px; --gap:24px;

  /* Bootstrap -> Avoice */
  --bs-body-font-family:var(--font); --bs-body-color:var(--ink); --bs-body-bg:var(--paper);
  --bs-link-color-rgb:10,18,23; --bs-link-hover-color-rgb:10,18,23;
  --bs-border-color:var(--hair); --bs-secondary-color:var(--graphite);
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:var(--font);font-weight:350;background:var(--paper);color:var(--ink);-webkit-font-smoothing:antialiased}

/* ================= SIDEBAR ================= */
.sb{
  position:fixed;top:var(--gap);bottom:var(--gap);left:var(--gap);width:var(--sb-w);
  background:var(--ink);color:var(--paper);border-radius:24px;
  padding:24px 16px 16px;display:flex;flex-direction:column;z-index:1040;
}
.sb-logo{display:flex;align-items:center;gap:10px;padding:0 12px 24px;font-size:18px;font-weight:650;letter-spacing:-.2px;color:var(--paper);text-decoration:none}
.sb-mark{width:12px;height:12px;border-radius:3px;background:var(--lime);flex:none}
.sb-nav{flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:2px;margin:0 -4px;padding:0 4px;scrollbar-width:none}
.sb-nav::-webkit-scrollbar{display:none}
.sb-sec{padding:20px 12px 6px;font-size:11px;font-weight:450;letter-spacing:1.1px;text-transform:uppercase;color:rgba(255,255,255,.4)}
.sb-link{
  display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:999px;
  font-size:14px;font-weight:450;color:rgba(255,255,255,.72);text-decoration:none;
  transition:background .15s ease,color .15s ease,transform .15s ease;
}
.sb-link i{font-size:16px;width:18px;text-align:center;line-height:1}
.sb-link:hover{background:rgba(255,255,255,.08);color:#fff;transform:translateX(2px)}
.sb-link.is-active{background:var(--lime);color:var(--ink);font-weight:550}
.sb-link:focus-visible{outline:2px solid var(--lime);outline-offset:2px}

.sb-foot{margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.14)}
.sb-user{display:flex;align-items:center;gap:12px;padding:0 8px;margin-bottom:14px;min-width:0}
.sb-avatar{width:36px;height:36px;border-radius:50%;background:var(--lime);color:var(--ink);display:grid;place-content:center;font-size:14px;font-weight:650;flex:none}
.sb-user b{display:block;font-size:14px;font-weight:550;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-user span{font-family:var(--mono);font-size:12px;letter-spacing:-.36px;color:rgba(255,255,255,.5)}
.sb-out{
  width:100%;height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;
  border-radius:999px;background:transparent;color:var(--paper);border:1px solid rgba(255,255,255,.25);
  font-family:inherit;font-size:14px;font-weight:550;letter-spacing:.35px;cursor:pointer;
  transition:background .15s ease,color .15s ease,border-color .15s ease;
}
.sb-out:hover{background:var(--paper);color:var(--ink);border-color:var(--paper)}
.sb-out:focus-visible{outline:2px solid var(--lime);outline-offset:2px}

.backdrop{position:fixed;inset:0;background:rgba(10,18,23,.55);opacity:0;pointer-events:none;transition:opacity .25s ease;z-index:1035}
.backdrop.show{opacity:1;pointer-events:auto}

/* ================= MAIN ================= */
.main{margin-left:calc(var(--sb-w) + var(--gap)*2);padding:32px var(--gap) 48px 8px;min-height:100vh}
.main-inner{max-width:1400px}
.topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:28px;animation:rise .6s var(--ease) .15s both}
.topbar-l{display:flex;align-items:flex-start;gap:12px;min-width:0}
.crumb{font-family:var(--mono);font-size:12px;letter-spacing:-.36px;color:var(--stone);text-transform:uppercase;margin-bottom:6px}
.ttl{margin:0;font-size:32px;font-weight:650;line-height:1.05;letter-spacing:-1.2px}
.sub{margin:8px 0 0;font-size:16px;font-weight:350;color:var(--graphite)}
.menu-btn{
  display:none;width:44px;height:44px;flex:none;border-radius:999px;border:1px solid var(--hair);
  background:var(--gray);color:var(--ink);align-items:center;justify-content:center;font-size:20px;cursor:pointer;
}
.menu-btn:focus-visible{outline:2px solid var(--ink);outline-offset:2px}
.page{animation:fade .6s ease .25s both}

/* ================= ANIMASI ================= */
/* Sidebar: hanya tampil beranimasi saat <html class="intro"> (sekali setelah login) */
.intro .sb{animation:sb-in .7s var(--ease) both}
.intro .sb-mark{animation:pop .6s var(--ease) .45s both}
.intro .sb-nav > *{animation:rise .55s var(--ease) calc(var(--i,0)*45ms + 250ms) both}
.intro .sb-foot{animation:rise .55s var(--ease) .9s both}
@keyframes sb-in{from{opacity:0;transform:translateX(-32px) scale(.985)}to{opacity:1;transform:none}}
@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes fade{from{opacity:0}to{opacity:1}}
@keyframes pop{0%{opacity:0;transform:scale(0) rotate(-45deg)}70%{opacity:1;transform:scale(1.25) rotate(0)}100%{opacity:1;transform:scale(1)}}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}

/* ================= RESPONSIF ================= */
@media (max-width:991px){
  .sb{transform:translateX(calc(-100% - 32px));animation:none;transition:transform .3s var(--ease)}
  .sb.show{transform:none}
  .intro .sb{animation:none}
  .main{margin-left:0;padding:16px 16px 40px}
  .menu-btn{display:inline-flex}
  .ttl{font-size:28px;letter-spacing:-.9px}
}

/* ============ KOMPATIBILITAS: halaman lama (Bootstrap) ikut gaya Avoice ============ */
.card{background:var(--paper);border:1px solid var(--hair);border-radius:24px;box-shadow:none}
.form-label{font-size:12px;font-weight:550;letter-spacing:.96px;text-transform:uppercase;color:var(--ink);margin-bottom:6px}
.form-control,.form-select{
  min-height:44px;font-size:14px;font-weight:350;color:var(--ink);
  border:1px solid var(--hair);border-radius:8px;box-shadow:none;background-color:var(--paper);
}
.form-control::placeholder{color:var(--stone)}
.form-control:hover,.form-select:hover{border-color:var(--stone)}
.form-control:focus,.form-select:focus{border-color:var(--ink);box-shadow:0 0 0 3px var(--lime);background-color:var(--paper)}
.form-check-input:checked{background-color:var(--lime);border-color:var(--ink)}
.form-check-input:focus{box-shadow:0 0 0 3px var(--lime);border-color:var(--ink)}
.btn{border-radius:999px;font-size:14px;font-weight:550;letter-spacing:.35px;padding:.6rem 1.4rem;border-width:1px}
.btn-sm{padding:.4rem 1rem;font-size:13px}
.btn:focus-visible{outline:2px solid var(--ink);outline-offset:3px;box-shadow:none}
.btn-brand{background:var(--lime);color:var(--ink);border:0}
.btn-brand:hover,.btn-brand:focus{background:#c2f000;color:var(--ink)}
.btn-light{background:var(--gray);color:var(--ink);border:1px solid var(--hair)}
.btn-light:hover{background:var(--hair);color:var(--ink);border-color:var(--hair)}
.btn-dark{background:var(--ink);border-color:var(--ink)}
.table{--bs-table-border-color:var(--hair);--bs-table-hover-bg:#fafafa;font-size:14px}
.table>:not(caption)>*>*{padding:.85rem 1rem}
.table thead th{font-size:12px;font-weight:550;letter-spacing:.96px;text-transform:uppercase;color:var(--graphite);background:var(--paper);border-bottom:1px solid var(--hair)}
.badge{border-radius:999px;font-size:13px;font-weight:450;padding:3px 11px;line-height:1.5}
.badge-pemasukan{background:var(--lime);color:var(--ink)}
.badge-pengeluaran{background:var(--ink);color:var(--paper)}
.badge-tabungan{background:var(--plan);color:var(--paper)}
.amt-pemasukan,.amt-pengeluaran,.amt-tabungan{color:var(--ink)}
.stat{padding:1.25rem 1.4rem}
.stat .lbl{font-size:12px;font-weight:550;letter-spacing:.96px;text-transform:uppercase;color:var(--graphite)}
.stat .val{font-size:28px;font-weight:650;letter-spacing:-1px}
.stat .ico{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;font-size:1.2rem}
.alert{border-radius:12px;border:1px solid var(--hair)}
.text-secondary{color:var(--graphite)!important}
.pagination{gap:4px}
.page-link{color:var(--ink);border:1px solid var(--hair);border-radius:8px!important;font-size:14px}
.page-link:hover{background:var(--gray);color:var(--ink)}
.page-item.active .page-link{background:var(--lime);border-color:var(--ink);color:var(--ink);font-weight:550}
.page-item.disabled .page-link{color:var(--stone)}
.progress{background:var(--gray);border-radius:999px}
.modal-content{border-radius:24px;border:1px solid var(--hair)}
</style>
</head>
<body>
@php($r = request()->route()?->getName())
@php($tp = request()->route("type"))

<div class="backdrop" id="bd"></div>

<aside class="sb" id="sb" aria-label="Navigasi utama">
  <a class="sb-logo" href="{{ route('dashboard') }}"><span class="sb-mark"></span>DompetKu</a>

  <nav class="sb-nav">
    <a class="sb-link {{ $r=='dashboard'?'is-active':'' }}" style="--i:1" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>

    <div class="sb-sec" style="--i:2">Kelola</div>
    <a class="sb-link {{ $tp=='pemasukan'?'is-active':'' }}"   style="--i:3" href="{{ route('trx.index','pemasukan') }}"><i class="bi bi-arrow-down-circle"></i> Pemasukan</a>
    <a class="sb-link {{ $tp=='pengeluaran'?'is-active':'' }}" style="--i:4" href="{{ route('trx.index','pengeluaran') }}"><i class="bi bi-arrow-up-circle"></i> Pengeluaran</a>
    <a class="sb-link {{ $tp=='tabungan'?'is-active':'' }}"    style="--i:5" href="{{ route('trx.index','tabungan') }}"><i class="bi bi-piggy-bank"></i> Tabungan</a>

    <div class="sb-sec" style="--i:6">Data</div>
    <a class="sb-link {{ str_starts_with($r??'','export')?'is-active':'' }}" style="--i:7" href="{{ route('export.index') }}"><i class="bi bi-download"></i> Export</a>
    <a class="sb-link {{ str_starts_with($r??'','import')?'is-active':'' }}" style="--i:8" href="{{ route('import.index') }}"><i class="bi bi-upload"></i> Import</a>
  </nav>

  <form method="POST" action="{{ route('logout') }}" class="sb-foot">@csrf
    <div class="sb-user">
      <div class="sb-avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
      <div style="min-width:0"><b>{{ auth()->user()->name }}</b><span>Akun aktif</span></div>
    </div>
    <button class="sb-out"><i class="bi bi-box-arrow-left"></i> Keluar</button>
  </form>
</aside>

<main class="main">
  <div class="main-inner">
    <div class="topbar">
      <div class="topbar-l">
        <button type="button" class="menu-btn" id="menuBtn" aria-label="Buka menu" aria-controls="sb" aria-expanded="false"><i class="bi bi-list"></i></button>
        <div>
          <div class="crumb">DompetKu / @yield('title')</div>
          <h1 class="ttl">@yield('title')</h1>
          <p class="sub">@yield('subtitle')</p>
        </div>
      </div>
      <div>@yield('actions')</div>
    </div>

    {{-- Notifikasi flash kini ditampilkan sebagai toast mengambang (partials/feedback) --}}

    <div class="page">
      @yield('content')
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  var sb = document.getElementById('sb');
  var bd = document.getElementById('bd');
  var btn = document.getElementById('menuBtn');

  var logout = document.querySelector('form.sb-foot');
  if (logout) logout.addEventListener('submit', function () { try { sessionStorage.removeItem('sbIntro'); } catch (e) {} });

  function setOpen(open) {
    sb.classList.toggle('show', open);
    bd.classList.toggle('show', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.documentElement.style.overflow = open ? 'hidden' : '';
  }
  btn.addEventListener('click', function () { setOpen(!sb.classList.contains('show')); });
  bd.addEventListener('click', function () { setOpen(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
  sb.querySelectorAll('.sb-link').forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
  window.addEventListener('resize', function () { if (window.innerWidth > 991) setOpen(false); });
})();
</script>
{{-- script khusus halaman; dibungkus agar bisa dimuat ulang saat navigasi tanpa reload --}}
<div id="avxStack" hidden>@stack('scripts')</div>

{{-- Toast mengambang + dialog konfirmasi (form[data-confirm]) --}}
@include('partials.feedback')
</body>
</html>