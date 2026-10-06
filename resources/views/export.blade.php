@extends('layouts.app')
@section('title','Export Data') @section('subtitle','Unduh laporan dalam format PDF atau CSV')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300..700&family=Geist+Mono:wght@350..700&display=swap" rel="stylesheet">

<style>
  .av {
    --ink: #0a1217; --paper: #ffffff; --gray: #f1f1f1; --hair: #e6e7e8;
    --stone: #85898b; --graphite: #54595d; --lime: #cdfe00;
    --blueprint: #dceaf8; --material: #f4e3cd; --site: #ddefe2; --plan: #24476b;
    --font: 'Aspekta', 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    --mono: 'Geist Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-family: var(--font); font-weight: 350; color: var(--ink); line-height: 1.5;
    display: grid; gap: 24px;
  }
  .av *, .av *::before, .av *::after { box-sizing: border-box; }

  .av-mono  { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; color: var(--stone); }
  .av-label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 550; letter-spacing: .96px; text-transform: uppercase; color: var(--graphite); }
  .av-card  { background: var(--paper); border: 1px solid var(--hair); border-radius: 24px; padding: 28px; }
  .av-title { margin: 0 0 6px; font-size: 24px; font-weight: 550; line-height: 1.2; letter-spacing: -0.24px; }
  .av-sub   { margin: 0 0 24px; font-size: 14px; color: var(--graphite); }

  @keyframes av-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  .av-rise { animation: av-rise .6s cubic-bezier(.2,.7,.2,1) calc(var(--i, 0) * 70ms + 100ms) both; }
  @media (prefers-reduced-motion: reduce) { .av *, .av *::before, .av *::after { animation: none !important; transition: none !important; } }

  .av-layout { display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: start; }

  /* ---- langkah ---- */
  .av-step { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
  .av-step .av-mono { padding: 2px 8px; border: 1px solid var(--hair); border-radius: 999px; }
  .av-step span.t { font-size: 14px; font-weight: 550; }
  .av-block + .av-block { margin-top: 28px; padding-top: 28px; border-top: 1px solid var(--hair); }

  /* ---- input ---- */
  .av-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .av-fields .full { grid-column: 1 / -1; }
  .av-input {
    width: 100%; height: 44px; padding: 0 14px; font-family: inherit; font-size: 14px; font-weight: 350; color: var(--ink);
    background: var(--paper); border: 1px solid var(--hair); border-radius: 8px; outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .av-input:hover { border-color: var(--stone); }
  .av-input:focus { border-color: var(--ink); box-shadow: 0 0 0 3px var(--lime); }

  /* ---- chip rentang cepat ---- */
  .av-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
  .av-chip {
    height: 34px; padding: 0 16px; border-radius: 999px; border: 1px solid var(--hair); background: var(--paper);
    font-family: inherit; font-size: 13px; font-weight: 450; color: var(--ink); cursor: pointer;
    transition: background .15s ease, border-color .15s ease;
  }
  .av-chip:hover { background: var(--gray); }
  .av-chip.is-on { background: var(--ink); color: var(--paper); border-color: var(--ink); }
  .av-chip:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }

  /* ---- kartu format (tombol submit) ---- */
  .av-fmts { display: grid; gap: 16px; }
  .av-fmt {
    position: relative; overflow: hidden; text-align: left; width: 100%; cursor: pointer;
    border-radius: 24px; padding: 24px 28px; border: 1px solid transparent; font-family: inherit; color: inherit;
    display: flex; flex-direction: column; gap: 22px; min-height: 164px; justify-content: space-between;
    transition: transform .2s cubic-bezier(.2,.7,.2,1), box-shadow .2s ease;
  }
  .av-fmt:hover { transform: translateY(-3px); box-shadow: 0 12px 28px -14px rgba(10,18,23,.35); }
  .av-fmt:active { transform: translateY(0); }
  .av-fmt:focus-visible { outline: 2px solid var(--ink); outline-offset: 3px; }
  .av-fmt--pdf { background: var(--ink); color: var(--paper); }
  .av-fmt--csv { background: var(--site); color: var(--ink); }
  .av-fmt-top { display: flex; justify-content: space-between; align-items: center; font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; opacity: .6; }
  .av-dot { width: 10px; height: 10px; border-radius: 3px; background: var(--lime); }
  .av-fmt-name { font-size: 32px; font-weight: 650; letter-spacing: -.045em; line-height: 1; }
  .av-fmt-desc { margin-top: 8px; font-size: 14px; opacity: .72; }
  .av-fmt-go {
    position: absolute; right: 24px; bottom: 24px; width: 44px; height: 44px; border-radius: 999px;
    display: grid; place-items: center; font-size: 18px; transition: transform .2s ease;
  }
  .av-fmt--pdf .av-fmt-go { background: var(--lime); color: var(--ink); }
  .av-fmt--csv .av-fmt-go { background: var(--ink); color: var(--paper); }
  .av-fmt:hover .av-fmt-go { transform: translateY(2px) rotate(8deg); }

  /* ---- ringkasan ---- */
  .av-sum { margin-top: 20px; padding: 14px 16px; border-radius: 12px; background: var(--gray); display: grid; gap: 6px; }
  .av-sum div { display: flex; justify-content: space-between; gap: 12px; font-size: 13px; }
  .av-sum div span:first-child { color: var(--graphite); }
  .av-sum div span:last-child { font-weight: 550; text-align: right; }

  .av-note { margin: 0; font-size: 13px; color: var(--graphite); display: flex; gap: 10px; align-items: flex-start; }
  .av-note i { font-style: normal; flex: none; width: 8px; height: 8px; margin-top: 7px; border-radius: 2px; background: var(--lime); border: 1px solid var(--ink); }

  @media (max-width: 1199px) { .av-layout { grid-template-columns: 1fr; } .av-fmts { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 640px) {
    .av { gap: 16px; }
    .av-card { padding: 20px; border-radius: 20px; }
    .av-fmts, .av-fields { grid-template-columns: 1fr; }
    .av-fmt { padding: 20px; border-radius: 20px; min-height: 0; }
  }
</style>

<form method="GET" id="ef" class="av">
  <div class="av-layout">

    {{-- Kiri: filter --}}
    <div class="av-card av-rise" style="--i:0">
      <h3 class="av-title">Atur Laporan</h3>
      <p class="av-sub">Pilih data yang ingin diekspor. Kosongkan semua untuk mengunduh seluruh transaksi.</p>

      <div class="av-block">
        <div class="av-step"><span class="av-mono">01</span><span class="t">Jenis transaksi</span></div>
        <select name="jenis" id="f-jenis" class="av-input">
          <option value="">Semua jenis</option>
          @foreach (\App\Models\Transaction::LABELS as $k => $l)
            <option value="{{ $k }}">{{ $l }}</option>
          @endforeach
        </select>
      </div>

      <div class="av-block">
        <div class="av-step"><span class="av-mono">02</span><span class="t">Rentang tanggal</span></div>
        <div class="av-chips" role="group" aria-label="Rentang cepat">
          <button type="button" class="av-chip is-on" data-range="all">Semua</button>
          <button type="button" class="av-chip" data-range="month">Bulan ini</button>
          <button type="button" class="av-chip" data-range="3m">3 bulan</button>
          <button type="button" class="av-chip" data-range="6m">6 bulan</button>
          <button type="button" class="av-chip" data-range="year">Tahun ini</button>
        </div>
        <div class="av-fields">
          <div>
            <label class="av-label" for="f-dari">Dari</label>
            <input type="date" name="dari" id="f-dari" class="av-input">
          </div>
          <div>
            <label class="av-label" for="f-sampai">Sampai</label>
            <input type="date" name="sampai" id="f-sampai" class="av-input">
          </div>
        </div>

        <div class="av-sum" aria-live="polite">
          <div><span>Jenis</span><span id="s-jenis">Semua jenis</span></div>
          <div><span>Periode</span><span id="s-periode">Seluruh waktu</span></div>
        </div>
      </div>
    </div>

    {{-- Kanan: format --}}
    <div class="av-card av-rise" style="--i:1">
      <h3 class="av-title">Pilih Format</h3>
      <p class="av-sub">Klik salah satu untuk langsung mengunduh.</p>

      <div class="av-fmts">
        <button formaction="{{ route('export.pdf') }}" class="av-fmt av-fmt--pdf">
          <div class="av-fmt-top"><span>A / LAPORAN</span><span class="av-dot"></span></div>
          <div>
            <div class="av-fmt-name">PDF</div>
            <div class="av-fmt-desc">Rapi untuk dibaca &amp; dicetak</div>
          </div>
          <span class="av-fmt-go" aria-hidden="true">↓</span>
        </button>

        <button formaction="{{ route('export.csv') }}" class="av-fmt av-fmt--csv">
          <div class="av-fmt-top"><span>B / DATA MENTAH</span></div>
          <div>
            <div class="av-fmt-name">CSV</div>
            <div class="av-fmt-desc">Bisa diimport kembali ke aplikasi</div>
          </div>
          <span class="av-fmt-go" aria-hidden="true">↓</span>
        </button>
      </div>

      <div style="margin-top:24px">
        <p class="av-note"><i></i><span>File CSV memakai format yang sama dengan fitur Import, jadi aman dipakai sebagai cadangan data.</span></p>
      </div>
    </div>

  </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
  const $ = id => document.getElementById(id);
  const dari = $('f-dari'), sampai = $('f-sampai'), jenis = $('f-jenis');
  const chips = document.querySelectorAll('.av-chip');
  const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  const fmt = v => v ? new Date(v + 'T00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : null;

  function summary() {
    $('s-jenis').textContent = jenis.value ? jenis.options[jenis.selectedIndex].text : 'Semua jenis';
    const a = fmt(dari.value), b = fmt(sampai.value);
    $('s-periode').textContent = a && b ? a + ' – ' + b : a ? 'Sejak ' + a : b ? 'Sampai ' + b : 'Seluruh waktu';
  }

  function setRange(r) {
    const now = new Date(), end = iso(now);
    let start = '';
    if (r === 'month') start = iso(new Date(now.getFullYear(), now.getMonth(), 1));
    if (r === '3m')    start = iso(new Date(now.getFullYear(), now.getMonth() - 2, 1));
    if (r === '6m')    start = iso(new Date(now.getFullYear(), now.getMonth() - 5, 1));
    if (r === 'year')  start = iso(new Date(now.getFullYear(), 0, 1));
    dari.value = start;
    sampai.value = r === 'all' ? '' : end;
    chips.forEach(c => c.classList.toggle('is-on', c.dataset.range === r));
    summary();
  }

  chips.forEach(c => c.addEventListener('click', () => setRange(c.dataset.range)));
  [dari, sampai].forEach(el => el.addEventListener('input', () => {
    chips.forEach(c => c.classList.remove('is-on'));
    summary();
  }));
  jenis.addEventListener('change', summary);
  summary();
})();
</script>
@endpush