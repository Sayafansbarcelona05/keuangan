{{--
  Toast mengambang + dialog konfirmasi.
  Pasang SEKALI di layouts/app.blade.php, tepat sebelum </body>:
      @include('partials.feedback')
--}}
@php
  $avxFlash = [];
  $avxMap = ['success' => 'success', 'ok' => 'success', 'status' => 'success', 'sukses' => 'success', 'berhasil' => 'success', 'message' => 'success', 'msg' => 'success',
             'error' => 'error', 'danger' => 'error', 'warning' => 'warning', 'info' => 'info'];
  foreach ($avxMap as $key => $type) {
    if (session()->has($key) && is_string(session($key))) {
      $avxFlash[] = ['type' => $type, 'msg' => session($key)];
    }
  }
  if (session()->has('import_errors') && is_array(session('import_errors')) && count(session('import_errors'))) {
    $avxFlash[] = ['type' => 'warning', 'msg' => count(session('import_errors')) . ' baris dilewati karena tidak valid. Detailnya ada di halaman ini.'];
  }
  if (isset($errors) && $errors->any()) {
    $avxFlash[] = ['type' => 'error', 'msg' => 'Ada isian yang belum benar. Periksa kembali formulir.'];
  }
@endphp

<style>
  .avx {
    --ink: #0a1217; --paper: #ffffff; --gray: #f1f1f1; --hair: #e6e7e8;
    --stone: #85898b; --graphite: #54595d; --lime: #cdfe00; --blueprint: #dceaf8; --danger: #b42318;
    --mono: 'Geist Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-family: 'Aspekta', 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    font-weight: 350; line-height: 1.5; color: var(--ink);
  }
  .avx *, .avx *::before, .avx *::after { box-sizing: border-box; }

  /* ---- toast ---- */
  .avx-toasts {
    position: fixed; z-index: 2147483000; top: calc(20px + env(safe-area-inset-top, 0px)); left: 50%;
    transform: translateX(-50%); display: grid; gap: 10px; justify-items: center;
    width: min(440px, calc(100vw - 24px)); pointer-events: none;
  }
  .avx-toast {
    pointer-events: auto; position: relative; overflow: hidden; display: flex; gap: 12px; align-items: center;
    width: 100%; padding: 10px 10px 13px 10px; border-radius: 999px;
    background: rgba(10,18,23,.92); color: var(--paper);
    -webkit-backdrop-filter: blur(14px) saturate(1.4); backdrop-filter: blur(14px) saturate(1.4);
    border: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 24px 48px -12px rgba(10,18,23,.5), 0 6px 16px -6px rgba(10,18,23,.35);
    animation: avx-in .55s cubic-bezier(.34,1.45,.5,1) both;
  }
  .avx-toast.is-out { animation: avx-out .25s ease forwards; }
  .avx-ti {
    flex: none; width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center;
    font-size: 15px; font-weight: 700; background: var(--lime); color: var(--ink);
  }
  .avx-toast--error   .avx-ti { background: #ff8a7e; }
  .avx-toast--warning .avx-ti { background: #ffd166; }
  .avx-toast--info    .avx-ti { background: var(--blueprint); }
  .avx-tb { flex: 1; min-width: 0; padding: 1px 0; }
  .avx-tt { font-size: 14px; font-weight: 550; letter-spacing: .1px; }
  .avx-tm { margin-top: 1px; font-size: 13px; line-height: 1.35; opacity: .75; overflow-wrap: anywhere; }
  .avx-x {
    flex: none; width: 28px; height: 28px; border: 0; border-radius: 50%; padding: 0; cursor: pointer;
    background: rgba(255,255,255,.1); color: var(--paper); font-size: 15px; line-height: 1;
    transition: background .15s ease;
  }
  .avx-x:hover { background: rgba(255,255,255,.22); }
  .avx-x:focus-visible { outline: 2px solid var(--lime); outline-offset: 2px; }
  .avx-bar {
    position: absolute; left: 22px; right: 22px; bottom: 4px; height: 3px; border-radius: 999px; background: var(--lime);
    transform-origin: left; opacity: .9; animation: avx-bar var(--d, 4500ms) linear forwards;
  }
  .avx-toast--error   .avx-bar { background: #ff8a7e; }
  .avx-toast--warning .avx-bar { background: #ffd166; }
  .avx-toast--info    .avx-bar { background: var(--blueprint); }
  .avx-toast:hover .avx-bar { animation-play-state: paused; }

  @keyframes avx-in  { from { opacity: 0; transform: translateY(-28px) scale(.9); } to { opacity: 1; transform: none; } }
  @keyframes avx-out { to { opacity: 0; transform: translateY(-20px) scale(.94); } }
  @keyframes avx-bar { to { transform: scaleX(0); } }
  @keyframes avx-pop { from { opacity: 0; transform: translateY(14px) scale(.96); } to { opacity: 1; transform: none; } }
  @keyframes avx-fade { from { opacity: 0; } to { opacity: 1; } }

  /* ---- bar loading navigasi ---- */
  .avx-load {
    position: fixed; z-index: 2147483001; top: 0; left: 0; right: 0; height: 3px; pointer-events: none;
    background: linear-gradient(90deg, var(--ink), var(--lime)) no-repeat; background-size: 38% 100%;
    opacity: 0; transition: opacity .2s ease; animation: avx-slide 1s linear infinite;
  }
  .avx-load.on { opacity: 1; }
  @keyframes avx-slide { from { background-position: -40% 0; } to { background-position: 140% 0; } }

  /* ---- dialog konfirmasi ---- */
  .avx-dlg { border: 0; padding: 0; background: transparent; width: 420px; max-width: calc(100vw - 32px); color: var(--ink); overflow: visible; }
  .avx-dlg::backdrop { background: rgba(10,18,23,.5); backdrop-filter: blur(4px); }
  .avx-dlg[open] { animation: avx-pop .28s cubic-bezier(.2,.7,.2,1) both; }
  .avx-dlg[open]::backdrop { animation: avx-fade .25s ease both; }
  .avx-dlg-in { background: var(--paper); border: 1px solid var(--hair); border-radius: 24px; padding: 28px; }
  .avx-dtop { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
  .avx-dmono { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; color: var(--stone); }
  .avx-dico {
    width: 48px; height: 48px; border-radius: 50%; display: grid; place-items: center;
    font-size: 20px; font-weight: 700; background: var(--lime); color: var(--ink);
  }
  .avx-dlg.is-danger .avx-dico { background: #fde7e4; color: var(--danger); }
  .avx-dt { margin: 0 0 8px; font-size: 24px; font-weight: 550; line-height: 1.2; letter-spacing: -0.24px; }
  .avx-dp {
    margin: 0 0 24px; font-size: 14px; color: var(--graphite); white-space: pre-line; overflow-wrap: anywhere;
  }
  .avx-act { display: flex; gap: 8px; padding-top: 24px; border-top: 1px solid var(--hair); }
  .avx-btn {
    height: 48px; padding: 0 24px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 999px; border: 0; font-family: inherit; font-size: 14px; font-weight: 550; letter-spacing: .35px;
    cursor: pointer; transition: background .15s ease, transform .1s ease;
  }
  .avx-btn:active { transform: translateY(1px); }
  .avx-btn:focus-visible { outline: 2px solid var(--ink); outline-offset: 3px; }
  .avx-btn--gray { background: var(--gray); color: var(--ink); border: 1px solid var(--hair); }
  .avx-btn--gray:hover { background: var(--hair); }
  .avx-btn--ok { flex: 1; background: var(--lime); color: var(--ink); }
  .avx-btn--ok:hover { background: #c2f000; }
  .avx-dlg.is-danger .avx-btn--ok { background: var(--danger); color: var(--paper); }
  .avx-dlg.is-danger .avx-btn--ok:hover { background: #912018; }

  @media (max-width: 575px) {
    .avx-toasts { top: calc(12px + env(safe-area-inset-top, 0px)); }
    .avx-dlg-in { padding: 20px; border-radius: 20px; }
    .avx-act { flex-direction: column-reverse; }
    .avx-btn { width: 100%; }
  }
  /* mode gerak berkurang: matikan gerakan masuk/keluar, progress bar tetap tampil */
  @media (prefers-reduced-motion: reduce) {
    .avx-toast, .avx-toast.is-out, .avx-dlg[open], .avx-dlg[open]::backdrop { animation: none !important; }
    .avx-btn { transition: none; }
  }
</style>

<div class="avx">
  <div class="avx-load" id="avxLoad" aria-hidden="true"></div>
  <div class="avx-toasts" id="avxToasts" role="region" aria-live="polite" aria-label="Notifikasi"></div>
  <script type="application/json" id="avxFlash">@json($avxFlash)</script>

  <dialog class="avx-dlg" id="avxDlg" aria-labelledby="avxT" aria-describedby="avxP">
    <div class="avx-dlg-in">
      <div class="avx-dtop">
        <span class="avx-dico" id="avxIco" aria-hidden="true">?</span>
        <span class="avx-dmono">KONFIRMASI</span>
      </div>
      <h3 class="avx-dt" id="avxT">Yakin?</h3>
      <p class="avx-dp" id="avxP"></p>
      <div class="avx-act">
        <button type="button" class="avx-btn avx-btn--gray" id="avxNo">Batal</button>
        <button type="button" class="avx-btn avx-btn--ok" id="avxOk">Ya, lanjut</button>
      </div>
    </div>
  </dialog>
</div>

<script>
(function () {
  var $ = function (id) { return document.getElementById(id); };
  var box = $('avxToasts'), dlg = $('avxDlg');
  var TITLES = { success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Info' };
  var ICONS  = { success: '✓', error: '!', warning: '!', info: 'i' };

  /* ---------- toast ---------- */
  function toast(message, opts) {
    opts = opts || {};
    var type = opts.type || 'success';
    var dur  = opts.duration || (type === 'error' ? 6500 : 4500);

    var el = document.createElement('div');
    el.className = 'avx-toast avx-toast--' + type;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.innerHTML =
      '<span class="avx-ti" aria-hidden="true"></span>' +
      '<div class="avx-tb"><div class="avx-tt"></div><div class="avx-tm"></div></div>' +
      '<button type="button" class="avx-x" aria-label="Tutup">×</button>' +
      '<span class="avx-bar" style="--d:' + dur + 'ms"></span>';
    el.querySelector('.avx-ti').textContent = ICONS[type] || '✓';
    el.querySelector('.avx-tt').textContent = opts.title || TITLES[type] || '';
    el.querySelector('.avx-tm').textContent = message;
    box.appendChild(el);

    var remain = dur, started = Date.now(), timer = setTimeout(close, remain), gone = false;
    function close() {
      if (gone) return; gone = true; clearTimeout(timer);
      el.classList.add('is-out');
      setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 260);
    }
    el.addEventListener('mouseenter', function () { clearTimeout(timer); remain -= Date.now() - started; });
    el.addEventListener('mouseleave', function () { started = Date.now(); timer = setTimeout(close, Math.max(remain, 600)); });
    el.querySelector('.avx-x').addEventListener('click', close);
    return close;
  }

  /* ---------- konfirmasi ---------- */
  function confirmBox(o) {
    o = o || {};
    return new Promise(function (resolve) {
      var text = o.text || '';
      if (typeof dlg.showModal !== 'function') { resolve(window.confirm((o.title ? o.title + '\n' : '') + text)); return; }
      if (dlg.open) { resolve(false); return; }

      var danger = o.tone === 'danger';
      dlg.classList.toggle('is-danger', danger);
      $('avxIco').textContent = danger ? '!' : '?';
      $('avxT').textContent = o.title || 'Yakin?';
      $('avxP').textContent = text;
      $('avxOk').textContent = o.ok || 'Ya, lanjut';
      $('avxNo').textContent = o.cancel || 'Batal';
      dlg.returnValue = '';

      function done() { dlg.removeEventListener('close', done); resolve(dlg.returnValue === 'ok'); }
      dlg.addEventListener('close', done);
      dlg.showModal();
      (danger ? $('avxNo') : $('avxOk')).focus();
    });
  }
  $('avxOk').addEventListener('click', function () { dlg.close('ok'); });
  $('avxNo').addEventListener('click', function () { dlg.close('cancel'); });
  dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close('cancel'); });

  window.avToast = toast;
  window.avConfirm = confirmBox;

  /* ---------- ganti isi halaman tanpa reload ---------- */
  var PARTS = ['.topbar-l > div', '.topbar > div:last-child', '.page'];

  function parse(html) { return new DOMParser().parseFromString(html, 'text/html'); }
  function hasShell(doc) { return !!(doc.querySelector('.page') && doc.querySelector('.topbar')); }
  function readFlash(doc) {
    var el = doc.getElementById('avxFlash');
    try { return JSON.parse(el.textContent) || []; } catch (e) { return []; }
  }
  function showFlash(list) {
    list.forEach(function (n, i) { setTimeout(function () { toast(n.msg, { type: n.type }); }, 120 + i * 180); });
  }
  // tandai menu sidebar aktif sesuai halaman baru (elemen tidak diganti, jadi listener menu tetap hidup)
  function syncNav(doc) {
    var cur = document.querySelectorAll('.sb-link'), nw = doc.querySelectorAll('.sb-link');
    if (cur.length !== nw.length) return;
    cur.forEach(function (el, i) { el.className = nw[i].className; });
  }

  // jalankan script khusus halaman (@push('scripts')) secara berurutan; script lama dibersihkan
  function runStack(doc) {
    var cur = document.getElementById('avxStack'), nw = doc.getElementById('avxStack');
    if (!cur || !nw) return;
    cur.querySelectorAll('script:not([src])').forEach(function (n) { n.parentNode.removeChild(n); });
    var list = Array.prototype.slice.call(nw.querySelectorAll('script'));
    (function next(i) {
      if (i >= list.length) return;
      var o = list[i], n = document.createElement('script');
      if (o.getAttribute('src')) {
        var src = o.getAttribute('src');
        var loaded = Array.prototype.some.call(document.scripts, function (x) { return x.getAttribute('src') === src; });
        if (loaded) return next(i + 1);
        n.src = src; n.onload = n.onerror = function () { next(i + 1); };
        cur.appendChild(n);
      } else {
        n.text = '(function(){\n' + o.textContent + '\n})();';   // dibungkus supaya const/let tidak bentrok saat dijalankan ulang
        cur.appendChild(n); next(i + 1);
      }
    })(0);
  }

  function swap(doc, url) {
    PARTS.forEach(function (sel) {
      var a = document.querySelector(sel), b = doc.querySelector(sel);
      if (a && b) a.innerHTML = b.innerHTML;
    });
    if (doc.title) document.title = doc.title;
    // innerHTML tidak menjalankan <script>, jadi dijalankan ulang manual
    document.querySelectorAll('.page script').forEach(function (old) {
      var n = document.createElement('script');
      if (old.src) n.src = old.src;
      n.text = old.textContent;
      old.parentNode.replaceChild(n, old);
    });
    syncNav(doc);
    runStack(doc);
    if (url && url !== location.href) {
      history.pushState(null, '', url);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  /* ---------- navigasi tanpa reload (link & filter) ---------- */
  var navCtl = null, navSeq = 0, loadEl = $('avxLoad');

  function navigate(url, push) {
    var seq = ++navSeq;
    if (navCtl) navCtl.abort();
    navCtl = new AbortController();
    var tm = setTimeout(function () { loadEl.classList.add('on'); }, 120);

    return fetch(url, {
      credentials: 'same-origin', signal: navCtl.signal,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    }).then(function (r) {
      return r.text().then(function (h) { return { r: r, h: h }; });
    }).then(function (o) {
      if (seq !== navSeq) return;
      var doc = parse(o.h);
      // respons bukan halaman aplikasi (error / login / layout lama) -> muat penuh
      if (!o.r.ok || !hasShell(doc) || !doc.getElementById('avxStack')) { location.assign(o.r.url || url); return; }
      swap(doc, push ? o.r.url : null);
      showFlash(readFlash(doc));
    }).catch(function (err) {
      if (err && err.name === 'AbortError') return;
      location.assign(url);
    }).then(function () {
      clearTimeout(tm);
      if (seq === navSeq) loadEl.classList.remove('on');
    });
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || (a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-no-ajax')) return;
    var h = a.getAttribute('href');
    if (!h || h.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(h)) return;
    var u; try { u = new URL(a.href, location.href); } catch (x) { return; }
    if (u.origin !== location.origin) return;
    if (u.pathname === location.pathname && u.search === location.search && u.hash) return;
    e.preventDefault();
    navigate(u.href, true);
  });

  function ajaxSubmit(f) {
    var stay = f.getAttribute('data-ajax') === 'stay';
    var row = f.closest('tr');
    if (row) row.style.opacity = '.4';

    function fail(msg) {
      delete f.dataset.avxOk;
      f.dispatchEvent(new CustomEvent('av:reset'));
      if (row) row.style.opacity = '';
      toast(msg, { type: 'error' });
    }

    fetch(f.action, {
      method: 'POST', body: new FormData(f), credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    }).then(function (resp) {
      if (resp.status === 419) { fail('Sesi berakhir. Memuat ulang halaman…'); setTimeout(function () { location.reload(); }, 1400); return; }
      if (!resp.ok) { fail(resp.status >= 500 ? 'Terjadi kesalahan di server.' : 'Permintaan ditolak (' + resp.status + ').'); return; }

      return resp.text().then(function (html) {
        var doc = parse(html);
        if (!hasShell(doc)) { location.assign(resp.url); return; }   // mis. sesi habis -> halaman login
        var flash = readFlash(doc);

        // mode "stay": tetap di URL sekarang (filter & halaman tidak hilang)
        if (stay && resp.url !== location.href) {
          return fetch(location.href, { credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (h) { swap(parse(h), null); showFlash(flash); });
        }
        swap(doc, stay ? null : resp.url);
        showFlash(flash);
      });
    }).catch(function () { fail('Koneksi bermasalah. Periksa internet Anda.'); });
  }

  /* ---------- otomatis untuk <form data-confirm> ---------- */
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f || !f.matches) return;

    // form filter/pencarian (GET) -> navigasi tanpa reload
    if (!f.hasAttribute('data-confirm')) {
      var m = (f.getAttribute('method') || 'get').toLowerCase();
      var sub = e.submitter;
      if (m === 'get' && !f.hasAttribute('data-no-ajax') && !f.querySelector('[formaction]') && !(sub && sub.hasAttribute('formaction'))) {
        var act = new URL(f.action || location.href, location.href);
        if (act.origin === location.origin) {
          e.preventDefault();
          var qs = new URLSearchParams(new FormData(f)).toString();
          act.search = qs ? '?' + qs : '';
          navigate(act.href, true);
        }
      }
      return;
    }

    if (f.dataset.avxOk === '1') return;
    e.preventDefault();
    confirmBox({
      title:  f.dataset.confirmTitle,
      text:   f.dataset.confirmText || f.dataset.confirm,
      ok:     f.dataset.confirmOk,
      tone:   f.dataset.confirmTone
    }).then(function (yes) {
      if (!yes) return;
      f.dataset.avxOk = '1';
      f.dispatchEvent(new CustomEvent('av:confirmed'));
      if (f.hasAttribute('data-ajax')) ajaxSubmit(f); else f.submit();
    });
  }, true);

  // tombol back/forward browser setelah navigasi AJAX
  window.addEventListener('popstate', function () { navigate(location.href, false); });

  // tombol "kembali" browser: kembalikan state form
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
      delete f.dataset.avxOk; f.dispatchEvent(new CustomEvent('av:reset'));
    });
  });

  /* ---------- flash dari session ---------- */
  var first = readFlash(document);
  first.forEach(function (n, i) { setTimeout(function () { toast(n.msg, { type: n.type }); }, 350 + i * 180); });
})();
</script>