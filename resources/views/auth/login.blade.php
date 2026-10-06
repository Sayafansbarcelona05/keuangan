<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login — {{ config('app.name') }}</title>

  {{-- Aspekta tidak tersedia di Google Fonts, Inter dipakai sebagai substitusi (sesuai pedoman) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300..700&family=Geist+Mono:wght@350..700&display=swap" rel="stylesheet">

  <style>
    :root {
      --color-drawing-ink: #0a1217;
      --color-paper: #ffffff;
      --color-drafting-gray: #f1f1f1;
      --color-hairline: #e6e7e8;
      --color-stone: #85898b;
      --color-graphite: #54595d;
      --color-signal-lime: #cdfe00;

      --font-aspekta: 'Aspekta', 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      --font-mono: 'Geist Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;

      --radius-input: 8px;
      --radius-feature: 24px;
      --radius-panel: 35.2px;
      --radius-pill: 999px;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      background: var(--color-paper);
      color: var(--color-drawing-ink);
      font-family: var(--font-aspekta);
      font-size: 16px;
      font-weight: 350;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* ===== Shell: dua panel dengan jarak 24px ===== */
    .auth-shell {
      min-height: 100%;
      display: grid;
      grid-template-columns: 1.05fr 1fr;
      gap: 24px;
      padding: 24px;
    }

    /* ===== Panel kiri (gelap) ===== */
    .hero-panel {
      background: var(--color-drawing-ink);
      color: var(--color-paper);
      border-radius: var(--radius-panel);
      padding: 44px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 560px;
    }
    .brand {
      display: flex; align-items: center; gap: 10px;
      font-weight: 650; font-size: 18px; letter-spacing: -0.2px;
    }
    .brand-mark {
      width: 12px; height: 12px; background: var(--color-signal-lime);
      border-radius: 3px; flex: none;
    }
    .mono-label {
      font-family: var(--font-mono);
      font-size: 12px; font-weight: 350; line-height: 1.5;
      letter-spacing: -0.36px;
      color: rgba(255, 255, 255, .6);
    }
    .hero-title {
      margin: 20px 0 16px;
      font-size: 48px; font-weight: 650;
      line-height: 45.6px; letter-spacing: -1.2px;
      text-transform: uppercase;
      max-width: 11ch;
    }
    .hero-copy {
      margin: 0; max-width: 38ch;
      color: rgba(255, 255, 255, .7);
      font-size: 16px; font-weight: 350;
    }
    .hero-foot {
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
      border-top: 1px solid rgba(255, 255, 255, .14);
      padding-top: 20px;
    }
    .chip-lime {
      background: var(--color-signal-lime); color: var(--color-drawing-ink);
      border-radius: var(--radius-pill);
      padding: 3px 11px;
      font-size: 10px; font-weight: 650; letter-spacing: 1.2px; text-transform: uppercase;
    }

    /* ===== Panel kanan (form) ===== */
    .form-panel {
      border: 1px solid var(--color-hairline);
      border-radius: var(--radius-feature);
      background: var(--color-paper);
      display: flex; align-items: center; justify-content: center;
      padding: 44px 36px;
    }
    .form-wrap { width: 100%; max-width: 380px; }

    .form-title {
      margin: 0 0 6px;
      font-size: 24px; font-weight: 550; line-height: 1.2; letter-spacing: -0.24px;
    }
    .form-sub { margin: 0 0 36px; color: var(--color-graphite); font-size: 16px; }

    .field { margin-bottom: 20px; }
    .label-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
    .label, .util-link {
      font-size: 12px; font-weight: 550; line-height: 1.5;
      letter-spacing: 0.96px; text-transform: uppercase;
    }
    .label { color: var(--color-drawing-ink); }
    .util-link {
      color: var(--color-drawing-ink);
      text-decoration: none;
      border: 0; background: none; padding: 0; cursor: pointer; font-family: inherit;
    }
    .util-link:hover { text-decoration: underline; text-underline-offset: 3px; }

    .input-box { position: relative; }
    .input {
      width: 100%;
      height: 48px;
      padding: 0 14px;
      font-family: inherit; font-size: 16px; font-weight: 350; color: var(--color-drawing-ink);
      background: var(--color-paper);
      border: 1px solid var(--color-hairline);
      border-radius: var(--radius-input);
      outline: none;
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    .input::placeholder { color: var(--color-stone); }
    .input:hover { border-color: var(--color-stone); }
    .input:focus {
      border-color: var(--color-drawing-ink);
      box-shadow: 0 0 0 3px var(--color-signal-lime);
    }
    .input.has-error { border-color: var(--color-drawing-ink); }
    .input-box .input.with-toggle { padding-right: 52px; }
    .input-box .toggle {
      position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
      width: 32px; height: 32px; padding: 0;
      display: grid; place-content: center;
      border-radius: var(--radius-input);
      color: var(--color-graphite);
      transition: background .15s ease, color .15s ease;
    }
    .input-box .toggle svg { width: 18px; height: 18px; }
    .input-box .toggle .eye-off { display: none; }
    .input-box .toggle.is-visible .eye { display: none; }
    .input-box .toggle.is-visible .eye-off { display: block; }
    .input-box .toggle:hover { background: var(--color-drafting-gray); color: var(--color-drawing-ink); text-decoration: none; }
    .input-box .toggle:focus-visible { outline: 2px solid var(--color-drawing-ink); outline-offset: 1px; }

    .error-text {
      margin-top: 6px;
      font-family: var(--font-mono); font-size: 12px; letter-spacing: -0.36px; line-height: 1.5;
      color: var(--color-drawing-ink);
    }
    .error-text::before { content: "! "; font-weight: 700; }

    .notice {
      margin-bottom: 20px; padding: 12px 14px;
      border: 1px solid var(--color-hairline); border-radius: 12px;
      background: var(--color-drafting-gray);
      font-size: 14px; font-weight: 400;
    }

    .remember {
      display: flex; align-items: center; gap: 10px;
      margin: 4px 0 28px;
      font-size: 14px; font-weight: 350; color: var(--color-graphite);
      cursor: pointer; user-select: none;
    }
    .remember input {
      appearance: none; -webkit-appearance: none;
      width: 18px; height: 18px; margin: 0;
      border: 1px solid var(--color-stone); border-radius: 5px;
      background: var(--color-paper);
      display: grid; place-content: center; cursor: pointer;
      transition: background .15s ease, border-color .15s ease;
    }
    .remember input:checked { background: var(--color-signal-lime); border-color: var(--color-drawing-ink); }
    .remember input:checked::after {
      content: ""; width: 9px; height: 5px;
      border-left: 2px solid var(--color-drawing-ink);
      border-bottom: 2px solid var(--color-drawing-ink);
      transform: translateY(-1px) rotate(-45deg);
    }
    .remember input:focus-visible { outline: 2px solid var(--color-drawing-ink); outline-offset: 2px; }

    .btn-lime {
      width: 100%; height: 48px;
      display: inline-flex; align-items: center; justify-content: center; gap: 10px;
      padding: 0 24px;
      background: var(--color-signal-lime); color: var(--color-drawing-ink);
      border: 0; border-radius: var(--radius-pill);
      font-family: inherit; font-size: 14px; font-weight: 550; letter-spacing: 0.35px;
      cursor: pointer;
      transition: background .15s ease, transform .1s ease;
    }
    .btn-lime:hover { background: #c2f000; }
    .btn-lime:active { transform: translateY(1px); }
    .btn-lime:focus-visible { outline: 2px solid var(--color-drawing-ink); outline-offset: 3px; }
    .btn-lime[disabled] { opacity: .7; cursor: progress; }
    .spinner {
      display: none; width: 14px; height: 14px;
      border: 2px solid rgba(10, 18, 23, .25); border-top-color: var(--color-drawing-ink);
      border-radius: 50%; animation: spin .7s linear infinite;
    }
    .btn-lime[disabled] .spinner { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .register-row {
      margin: 28px 0 0; padding-top: 20px;
      border-top: 1px solid var(--color-hairline);
      text-align: center; font-size: 14px; color: var(--color-graphite);
    }
    .register-row a {
      color: var(--color-drawing-ink); font-weight: 550;
      text-decoration: underline; text-underline-offset: 3px;
    }

    /* ===== Animasi masuk ===== */
    @keyframes panel-in-left {
      from { opacity: 0; transform: translateX(-32px) scale(.985); }
      to   { opacity: 1; transform: none; }
    }
    @keyframes panel-in-right {
      from { opacity: 0; transform: translateX(32px) scale(.985); }
      to   { opacity: 1; transform: none; }
    }
    @keyframes rise {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: none; }
    }
    @keyframes pop {
      0%   { opacity: 0; transform: scale(0) rotate(-45deg); }
      70%  { opacity: 1; transform: scale(1.25) rotate(0); }
      100% { opacity: 1; transform: scale(1); }
    }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-6px); }
      40%, 80% { transform: translateX(6px); }
    }

    /* Panel */
    .hero-panel { animation: panel-in-left .75s cubic-bezier(.2, .7, .2, 1) both; }
    .form-panel { animation: panel-in-right .75s cubic-bezier(.2, .7, .2, 1) .1s both; }

    /* Isi panel kiri */
    .brand            { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .30s both; }
    .brand-mark       { animation: pop .6s cubic-bezier(.2, .7, .2, 1) .55s both; }
    .hero-panel > div:nth-child(2) > .mono-label { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .40s both; }
    .hero-title       { animation: rise .7s cubic-bezier(.2, .7, .2, 1) .48s both; }
    .hero-copy        { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .60s both; }
    .hero-foot        { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .75s both; }
    .chip-lime        { animation: pop .6s cubic-bezier(.2, .7, .2, 1) .95s both; }

    /* Isi panel kanan (berurutan) */
    .form-title                { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .35s both; }
    .form-sub                  { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .42s both; }
    .form-wrap > .notice       { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .48s both; }
    form .field:nth-of-type(1) { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .52s both; }
    form .field:nth-of-type(2) { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .60s both; }
    form .remember             { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .68s both; }
    form .btn-lime             { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .76s both; }
    .register-row              { animation: rise .6s cubic-bezier(.2, .7, .2, 1) .86s both; }

    /* Getar halus saat login gagal */
    form.shake { animation: shake .45s ease .9s both; }

    /* Hormati pengguna yang mematikan animasi */
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation: none !important;
        transition: none !important;
      }
    }

    /* ===== Responsif ===== */
    @media (max-width: 900px) {
      .auth-shell { grid-template-columns: 1fr; padding: 12px; gap: 12px; }
      .hero-panel { min-height: 0; padding: 28px; border-radius: var(--radius-feature); gap: 36px; }
      .hero-title { font-size: 36px; line-height: 34.2px; letter-spacing: -0.9px; max-width: none; }
      .hero-foot { display: none; }
      .form-panel { padding: 36px 20px; }
    }
  </style>
</head>

<body>
  <main class="auth-shell">

    {{-- Panel kiri --}}
    <section class="hero-panel" aria-hidden="false">
      <div>
        <div class="brand"><span class="brand-mark"></span>{{ config('app.name') }}</div>
      </div>

      <div>
        <span class="mono-label">AUTH / 01 — MASUK</span>
        <h1 class="hero-title">Masuk ke akun kamu</h1>
        <p class="hero-copy">Lanjutkan pekerjaanmu di tempat terakhir kamu berhenti. Semua data tetap tersimpan dan aman.</p>
      </div>

      <div class="hero-foot">
        <span class="mono-label">v1.0 — sesi terenkripsi</span>
        <span class="chip-lime">Aman</span>
      </div>
    </section>

    {{-- Panel kanan: form --}}
    <section class="form-panel">
      <div class="form-wrap">
        <h2 class="form-title">Selamat datang kembali</h2>
        <p class="form-sub">Masukkan email dan password untuk melanjutkan.</p>

        @if (session('status'))
          <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
          <div class="notice" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm" class="{{ $errors->any() ? 'shake' : '' }}">
          @csrf

          <div class="field">
            <div class="label-row"><label class="label" for="email">Email</label></div>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input @error('email') has-error @enderror"
                   placeholder="nama@email.com" autocomplete="email" required autofocus>
            @error('email')
              <div class="error-text">{{ $message }}</div>
            @enderror
          </div>

          <div class="field">
            <div class="label-row">
              <label class="label" for="password">Password</label>
              @if (Route::has('password.request'))
                <a class="util-link" href="{{ route('password.request') }}">Lupa password?</a>
              @endif
            </div>
            <div class="input-box">
              <input id="password" type="password" name="password"
                     class="input with-toggle @error('password') has-error @enderror"
                     placeholder="Masukkan password" autocomplete="current-password" required>
              <button type="button" class="util-link toggle" id="togglePass" aria-label="Tampilkan password"><svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m2 2 20 20"/></svg></button>
            </div>
            @error('password')
              <div class="error-text">{{ $message }}</div>
            @enderror
          </div>

          <label class="remember">
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            Ingat saya di perangkat ini
          </label>

          <button type="submit" class="btn-lime" id="loginBtn">
            <span class="spinner" aria-hidden="true"></span>
            <span id="loginBtnText">Masuk</span>
          </button>
        </form>

        @if (Route::has('register'))
          <p class="register-row">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
        @endif
      </div>
    </section>

  </main>

  <script>
    (function () {
      var pass = document.getElementById('password');
      var toggle = document.getElementById('togglePass');
      var form = document.getElementById('loginForm');
      var btn = document.getElementById('loginBtn');
      var btnText = document.getElementById('loginBtnText');

      toggle.addEventListener('click', function () {
        var show = pass.type === 'password';
        pass.type = show ? 'text' : 'password';
        toggle.classList.toggle('is-visible', show);
        toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      });

      form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        btn.disabled = true;
        btnText.textContent = 'Memproses...';
      });
    })();
  </script>
</body>
</html>