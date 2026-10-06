@extends('layouts.app')
@section('title','Import Data') @section('subtitle','Upload file CSV catatan keuangan')

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
  .av [hidden] { display: none !important; }

  .av-mono  { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; color: var(--stone); }
  .av-card  { background: var(--paper); border: 1px solid var(--hair); border-radius: 24px; padding: 28px; }
  .av-title { margin: 0 0 6px; font-size: 24px; font-weight: 550; line-height: 1.2; letter-spacing: -0.24px; }
  .av-sub   { margin: 0 0 24px; font-size: 14px; color: var(--graphite); }
  .av-head  { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
  .av-head .av-title { margin: 0 0 4px; }
  .av-head .av-sub { margin: 0; }

  @keyframes av-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  .av-rise { animation: av-rise .6s cubic-bezier(.2,.7,.2,1) calc(var(--i, 0) * 70ms + 100ms) both; }
  @media (prefers-reduced-motion: reduce) { .av *, .av *::before, .av *::after { animation: none !important; transition: none !important; } }

  .av-layout { display: grid; grid-template-columns: 1.1fr 1fr; gap: 24px; align-items: start; }

  .av-step { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
  .av-step .av-mono { padding: 2px 8px; border: 1px solid var(--hair); border-radius: 999px; }
  .av-step span.t { font-size: 14px; font-weight: 550; }
  .av-block + .av-block { margin-top: 28px; padding-top: 28px; border-top: 1px solid var(--hair); }

  /* ---- dropzone ---- */
  .av-drop {
    position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px;
    min-height: 200px; padding: 28px; text-align: center; cursor: pointer;
    border: 1.5px dashed var(--stone); border-radius: 20px; background: var(--paper);
    transition: background .15s ease, border-color .15s ease, transform .15s ease;
  }
  .av-drop:hover, .av-drop.is-over { background: var(--site); border-color: var(--ink); }
  .av-drop.is-over { transform: scale(1.01); }
  .av-drop.is-set { border-style: solid; border-color: var(--ink); background: var(--ink); color: var(--paper); }
  .av-drop.is-bad { border-color: #b42318; }
  .av-drop input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
  .av-drop:focus-within { box-shadow: 0 0 0 3px var(--lime); }
  .av-ico {
    width: 52px; height: 52px; border-radius: 999px; display: grid; place-items: center;
    background: var(--lime); color: var(--ink); font-size: 22px; font-weight: 650;
  }
  .av-drop-t { font-size: 16px; font-weight: 550; letter-spacing: -0.1px; }
  .av-drop-s { font-size: 13px; opacity: .65; }
  .av-fname { font-family: var(--mono); font-size: 13px; letter-spacing: -0.36px; word-break: break-all; }

  /* ---- toggle ganti data ---- */
  .av-check {
    display: flex; gap: 14px; align-items: flex-start; padding: 16px; cursor: pointer;
    border: 1px solid var(--hair); border-radius: 12px; transition: background .15s ease, border-color .15s ease;
  }
  .av-check:hover { background: #fafafa; }
  .av-check input { position: absolute; opacity: 0; pointer-events: none; }
  .av-box {
    flex: none; width: 22px; height: 22px; margin-top: 1px; border-radius: 6px; border: 1.5px solid var(--stone);
    display: grid; place-items: center; font-size: 13px; font-weight: 700; color: transparent; transition: all .15s ease;
  }
  .av-check input:checked + .av-box { background: var(--ink); border-color: var(--ink); color: var(--lime); }
  .av-check input:focus-visible + .av-box { box-shadow: 0 0 0 3px var(--lime); }
  .av-check:has(input:checked) { border-color: #b42318; background: #fff5f4; }
  .av-check b { display: block; font-size: 14px; font-weight: 550; }
  .av-check small { display: block; margin-top: 2px; font-size: 13px; color: var(--graphite); }
  .av-warn { display: none; margin-top: 8px; font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; color: #b42318; }
  .av-check:has(input:checked) .av-warn { display: block; }

  /* ---- tombol ---- */
  .av-btn {
    height: 48px; padding: 0 28px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    border-radius: 999px; border: 0; font-family: inherit; font-size: 14px; font-weight: 550; letter-spacing: .35px;
    cursor: pointer; transition: background .15s ease, transform .1s ease, opacity .15s ease;
  }
  .av-btn:active { transform: translateY(1px); }
  .av-btn:focus-visible { outline: 2px solid var(--ink); outline-offset: 3px; }
  .av-btn--lime { background: var(--lime); color: var(--ink); }
  .av-btn--lime:hover { background: #c2f000; }
  .av-btn:disabled { opacity: .45; cursor: not-allowed; }
  .av-spin { display: none; width: 14px; height: 14px; border: 2px solid rgba(10,18,23,.25); border-top-color: var(--ink); border-radius: 50%; animation: av-spin .7s linear infinite; }
  .av-btn.is-busy { opacity: .8; cursor: progress; }
  .av-btn.is-busy .av-spin { display: inline-block; }
  @keyframes av-spin { to { transform: rotate(360deg); } }
  .av-submit { margin-top: 24px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }

  .av-err { margin: 10px 0 0; font-size: 13px; color: #b42318; font-weight: 450; }

  /* ---- panel format ---- */
  .av-code {
    margin: 0 0 16px; padding: 18px; border-radius: 16px; background: var(--ink); color: #e9ecee; overflow-x: auto;
    font-family: var(--mono); font-size: 12px; line-height: 1.8; letter-spacing: -0.36px; white-space: pre;
  }
  .av-code .h { color: var(--lime); }
  .av-code .d { color: #8b9499; }
  .av-rules { display: grid; gap: 12px; margin: 0; padding: 0; list-style: none; }
  .av-rules li { display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: var(--graphite); }
  .av-rules li::before { content: ""; flex: none; width: 8px; height: 8px; margin-top: 8px; border-radius: 2px; background: var(--lime); border: 1px solid var(--ink); }
  .av-rules b { color: var(--ink); font-weight: 550; }
  .av-tags { display: inline-flex; gap: 6px; flex-wrap: wrap; vertical-align: middle; }
  .av-badge { display: inline-block; padding: 1px 10px; border-radius: 999px; font-size: 12px; font-weight: 450; color: var(--paper); background: var(--ink); white-space: nowrap; }
  .av-badge--pemasukan { background: var(--lime); color: var(--ink); }
  .av-badge--tabungan { background: var(--plan); }

  /* ---- tinjau data ---- */
  .av-pv-pill { display: inline-flex; gap: 8px; align-items: center; padding: 4px 12px; border-radius: 999px; background: var(--gray); border: 1px solid var(--hair); font-size: 13px; font-weight: 450; }
  .av-pv-pill b { font-weight: 650; }
  .av-pv-pills { display: flex; gap: 8px; flex-wrap: wrap; }
  .av-pv-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
  .av-pv-stat { border-radius: 20px; padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; }
  .av-pv-stat--site { background: var(--site); } .av-pv-stat--material { background: var(--material); } .av-pv-stat--blueprint { background: var(--blueprint); }
  .av-pv-stat .top { display: flex; justify-content: space-between; align-items: center; }
  .av-pv-stat .top .av-mono { color: inherit; opacity: .6; }
  .av-pv-stat .nm { font-size: 14px; font-weight: 450; letter-spacing: .35px; opacity: .75; }
  .av-pv-stat .val { display: flex; align-items: flex-start; gap: 8px; font-size: 28px; font-weight: 650; letter-spacing: -.045em; line-height: 1; font-variant-numeric: tabular-nums; }
  .av-pv-stat .val .cur { flex: none; margin-top: .1em; padding: 3px 8px; border-radius: 999px; background: rgba(10,18,23,.1); font-size: 11px; font-weight: 650; letter-spacing: .5px; line-height: 1.3; }
  .av-pv-stat .val .num { min-width: 0; overflow-wrap: anywhere; }
  .av-pv-stat .val .sep { font-style: normal; opacity: .32; letter-spacing: 0; margin: 0 .03em; }
  .av-pv-bar { display: flex; align-items: center; gap: 12px; padding: 12px 16px; margin-bottom: 20px; border-radius: 12px; background: #fff5f4; border: 1px solid #f3c9c4; font-size: 13px; color: #8a1c13; }
  .av-pv-bar i { flex: none; width: 8px; height: 8px; border-radius: 2px; background: #b42318; }

  .av-table-wrap { overflow-x: auto; border-top: 1px solid var(--hair); margin: 0 -28px; }
  .av-table { width: 100%; border-collapse: collapse; font-size: 14px; }
  .av-table th {
    padding: 14px 16px; text-align: left; white-space: nowrap;
    font-size: 12px; font-weight: 550; letter-spacing: .96px; text-transform: uppercase; color: var(--graphite);
    border-bottom: 1px solid var(--hair);
  }
  .av-table th:first-child, .av-table td:first-child { padding-left: 28px; }
  .av-table th:last-child,  .av-table td:last-child  { padding-right: 28px; }
  .av-table td { padding: 12px 16px; border-bottom: 1px solid var(--hair); vertical-align: middle; }
  .av-table tbody tr:last-child td { border-bottom: 0; }
  .av-table .r { text-align: right; }
  .av-date { font-family: var(--mono); font-size: 13px; letter-spacing: -0.36px; color: var(--graphite); white-space: nowrap; }
  .av-chip { display: inline-block; padding: 2px 11px; border: 1px solid var(--hair); border-radius: 999px; font-size: 13px; color: var(--graphite); white-space: nowrap; }
  .av-amt { font-weight: 550; font-variant-numeric: tabular-nums; white-space: nowrap; letter-spacing: -.01em; }
  .av-sign { display: inline-block; width: 1.1ch; margin-right: 6px; text-align: center; color: var(--stone); }
  .av-cur { margin-right: 5px; font-size: 11px; font-weight: 550; letter-spacing: .5px; color: var(--stone); }
  .av-more { padding-top: 16px; border-top: 1px solid var(--hair); margin: 0 -28px; padding-left: 28px; padding-right: 28px; }

  .av-skip { margin-top: 20px; border: 1px solid #e8d49a; background: #fff9e6; border-radius: 16px; padding: 18px 20px; }
  .av-skip-h { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 14px; font-weight: 550; color: #7a5200; }
  .av-skip-h .av-mono { color: #7a5200; padding: 1px 8px; border: 1px solid #e8d49a; border-radius: 999px; }
  .av-skip ul { margin: 0; padding: 0; list-style: none; max-height: 220px; overflow-y: auto; display: grid; gap: 4px; }
  .av-skip li { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; color: #5c4200; }
  .av-note { margin: 16px 0 0; font-size: 12px; color: var(--stone); }

  @media (max-width: 1199px) { .av-layout { grid-template-columns: 1fr; } }
  @media (max-width: 767px) { .av-pv-stats { grid-template-columns: 1fr; } }
  @media (max-width: 640px) {
    .av { gap: 16px; }
    .av-card { padding: 20px; border-radius: 20px; }
    .av-btn { width: 100%; }
    .av-table-wrap, .av-more { margin-left: -20px; margin-right: -20px; }
    .av-table th:first-child, .av-table td:first-child { padding-left: 20px; }
    .av-table th:last-child,  .av-table td:last-child  { padding-right: 20px; }
    .av-more { padding-left: 20px; padding-right: 20px; }
  }
</style>

<div class="av">
  <div class="av-layout">

    {{-- Kiri: upload --}}
    <div class="av-card av-rise" style="--i:0">
      <h3 class="av-title">Upload File</h3>
      <p class="av-sub">Tarik file CSV ke area di bawah, atau klik untuk memilih dari perangkat. Isinya bisa ditinjau dulu sebelum diimport.</p>

      <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" id="imf"
            data-confirm="Import data ini?" data-confirm-title="Import data?" data-confirm-ok="Ya, import" data-ajax>
        @csrf

        <div class="av-block">
          <div class="av-step"><span class="av-mono">01</span><span class="t">File CSV</span></div>
          <label class="av-drop @error('file') is-bad @enderror" id="drop">
            <input type="file" name="file" id="file" accept=".csv,text/csv" required>
            <span class="av-ico" id="ico" aria-hidden="true">↑</span>
            <span>
              <span class="av-drop-t" id="dt">Pilih atau jatuhkan file CSV</span><br>
              <span class="av-drop-s" id="ds">Hanya .csv</span>
            </span>
            <span class="av-fname" id="fn" hidden></span>
          </label>
          @error('file')<p class="av-err">{{ $message }}</p>@enderror
        </div>

        <div class="av-block">
          <div class="av-step"><span class="av-mono">02</span><span class="t">Opsi</span></div>
          <label class="av-check" for="rp">
            <input type="checkbox" name="replace" value="1" id="rp">
            <span class="av-box" aria-hidden="true">✓</span>
            <span>
              <b>Hapus semua data lama sebelum import</b>
              <small>Seluruh transaksi yang ada akan diganti isi file ini.</small>
              <span class="av-warn">Tindakan ini tidak bisa dibatalkan.</span>
            </span>
          </label>
        </div>

        <div class="av-submit">
          <button class="av-btn av-btn--lime" id="go" disabled><span class="av-spin" aria-hidden="true"></span><span id="goTxt">Import Sekarang →</span></button>
          <span class="av-mono" id="hint">Belum ada file dipilih</span>
        </div>
      </form>
    </div>

    {{-- Kanan: format --}}
    <div class="av-card av-rise" style="--i:1">
      <h3 class="av-title">Format CSV</h3>
      <p class="av-sub">Gunakan header persis seperti contoh berikut.</p>

<pre class="av-code"><span class="h">Tanggal,Jenis,Keterangan,Kategori,"Jumlah (Rp)"</span>
01-07-2026,Pemasukan,"Uang Bulanan",-,200000.00
01-07-2026,Pengeluaran,"Sabun Mandi",Belanja,<span class="d">-</span>57000.00
05-07-2026,Tabungan,"Dana darurat",-,<span class="d">-</span>50000.00</pre>

      <ul class="av-rules">
        <li><span><b>Tanggal</b> memakai format <span class="av-mono" style="color:var(--ink)">dd-mm-yyyy</span>.</span></li>
        <li>
          <span><b>Jenis</b> salah satu dari
            <span class="av-tags">
              <span class="av-badge av-badge--pemasukan">Pemasukan</span>
              <span class="av-badge">Pengeluaran</span>
              <span class="av-badge av-badge--tabungan">Tabungan</span>
            </span>
          </span>
        </li>
        <li><span><b>Jumlah</b> boleh bertanda minus, tandanya otomatis diabaikan.</span></li>
        <li><span><b>Kategori</b> isi <span class="av-mono" style="color:var(--ink)">-</span> bila tidak ada.</span></li>
      </ul>
    </div>

  </div>

  {{-- Tinjau data: muncul setelah file dipilih --}}
  <div class="av-card" id="pv" hidden>
    <div class="av-head">
      <div>
        <h3 class="av-title">Tinjau Data</h3>
        <p class="av-sub">Berikut isi file yang akan ditambahkan. Periksa dulu sebelum menekan Import.</p>
      </div>
      <div class="av-pv-pills">
        <span class="av-pv-pill">Total baris <b id="pvTotal">0</b></span>
        <span class="av-pv-pill">Valid <b id="pvOk">0</b></span>
        <span class="av-pv-pill">Dilewati <b id="pvBad">0</b></span>
      </div>
    </div>

    <div class="av-pv-bar" id="pvWarn" hidden><i></i><span>Semua data lama akan <b>dihapus</b> terlebih dahulu, lalu diganti dengan isi file ini.</span></div>

    <div class="av-pv-stats">
      <div class="av-pv-stat av-pv-stat--site">
        <div class="top"><span class="nm">Pemasukan</span><span class="av-mono" id="pvCntIn">0 baris</span></div>
        <div class="val"><span class="cur">Rp</span><span class="num" id="pvIn">0</span></div>
      </div>
      <div class="av-pv-stat av-pv-stat--material">
        <div class="top"><span class="nm">Pengeluaran</span><span class="av-mono" id="pvCntOut">0 baris</span></div>
        <div class="val"><span class="cur">Rp</span><span class="num" id="pvOut">0</span></div>
      </div>
      <div class="av-pv-stat av-pv-stat--blueprint">
        <div class="top"><span class="nm">Tabungan</span><span class="av-mono" id="pvCntSav">0 baris</span></div>
        <div class="val"><span class="cur">Rp</span><span class="num" id="pvSav">0</span></div>
      </div>
    </div>

    <div class="av-table-wrap">
      <table class="av-table">
        <thead>
          <tr><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th>Kategori</th><th class="r">Jumlah</th></tr>
        </thead>
        <tbody id="pvRows"></tbody>
      </table>
    </div>
    <div class="av-more av-mono" id="pvMore" hidden></div>

    <div class="av-skip" id="pvSkip" hidden>
      <div class="av-skip-h"><span>Baris yang akan dilewati</span><span class="av-mono" id="pvSkipN">0</span></div>
      <ul id="pvSkipList"></ul>
    </div>

    <p class="av-note">Tinjauan ini dibaca langsung di browser Anda dan merupakan perkiraan. Hasil akhir mengikuti pengecekan di server.</p>
  </div>

  @if (session('import_errors'))
    <div class="av-card av-rise" style="--i:2" role="alert">
      <div class="av-skip" style="margin-top:0">
        <div class="av-skip-h"><span>Baris dilewati saat import terakhir</span><span class="av-mono">{{ count(session('import_errors')) }}</span></div>
        <ul>
          @foreach (session('import_errors') as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
  const $ = id => document.getElementById(id);
  const input = $('file'), drop = $('drop'), go = $('go'), form = $('imf'), rp = $('rp');
  if (!input || !form) return;

  const JENIS = { pemasukan: 'Pemasukan', pengeluaran: 'Pengeluaran', tabungan: 'Tabungan' };
  const nf = new Intl.NumberFormat('id-ID');
  const size = b => b < 1024 ? b + ' B' : b < 1048576 ? (b / 1024).toFixed(1) + ' KB' : (b / 1048576).toFixed(1) + ' MB';
  const rupiah = n => nf.format(Math.round(n));
  let result = null;

  /* ---------- parser CSV (mendukung kutip & ; atau ,) ---------- */
  function parseCSV(text) {
    if (text.charCodeAt(0) === 0xFEFF) text = text.slice(1);
    const first = text.split(/\r?\n/, 1)[0] || '';
    const delim = first.split(';').length > first.split(',').length ? ';' : ',';
    const rows = []; let row = [], cell = '', q = false;
    for (let i = 0; i < text.length; i++) {
      const ch = text[i];
      if (q) {
        if (ch === '"') { if (text[i + 1] === '"') { cell += '"'; i++; } else q = false; }
        else cell += ch;
      } else if (ch === '"') q = true;
      else if (ch === delim) { row.push(cell); cell = ''; }
      else if (ch === '\n' || ch === '\r') {
        if (ch === '\r' && text[i + 1] === '\n') i++;
        row.push(cell); cell = ''; rows.push(row); row = [];
      } else cell += ch;
    }
    if (cell !== '' || row.length) { row.push(cell); rows.push(row); }
    return rows.filter(r => r.some(c => c.trim() !== ''));
  }

  function parseAmount(raw) {
    let s = raw.replace(/rp/ig, '').replace(/\s/g, '').replace(/^[-−]/, '');
    if (/^\d+(\.\d+)?$/.test(s)) return parseFloat(s);
    if (/^\d{1,3}(\.\d{3})*(,\d+)?$/.test(s)) return parseFloat(s.replace(/\./g, '').replace(',', '.'));
    return NaN;
  }

  function analyse(rows) {
    const out = { total: 0, ok: [], bad: [], sum: { pemasukan: 0, pengeluaran: 0, tabungan: 0 }, cnt: { pemasukan: 0, pengeluaran: 0, tabungan: 0 } };
    let start = 0;
    if (rows.length && /^tanggal$/i.test((rows[0][0] || '').trim())) start = 1;
    for (let i = start; i < rows.length; i++) {
      const r = rows[i], no = i + 1; out.total++;
      const d = (r[0] || '').trim(), j = (r[1] || '').trim().toLowerCase(), desc = (r[2] || '').trim();
      const cat = (r[3] || '').trim(); const amt = parseAmount((r[4] || '').trim());
      const errs = [];
      if (r.length < 5) errs.push('kolom kurang (butuh 5)');
      const m = d.match(/^(\d{2})-(\d{2})-(\d{4})$/);
      if (!m) errs.push('tanggal harus dd-mm-yyyy');
      else { const dt = new Date(+m[3], +m[2] - 1, +m[1]); if (dt.getMonth() !== +m[2] - 1 || dt.getDate() !== +m[1]) errs.push('tanggal tidak ada di kalender'); }
      if (!JENIS[j]) errs.push('jenis tidak dikenal');
      if (!desc) errs.push('keterangan kosong');
      if (isNaN(amt)) errs.push('jumlah tidak valid'); else if (amt <= 0) errs.push('jumlah harus lebih dari 0');
      if (errs.length) { out.bad.push({ no, msg: errs.join(', ') }); continue; }
      out.ok.push({ d, j, desc, cat: (cat === '' ? '-' : cat), amt });
      out.sum[j] += amt; out.cnt[j]++;
    }
    return out;
  }

  /* ---------- tampilan tinjauan ---------- */
  const mk = (tag, cls, txt) => { const e = document.createElement(tag); if (cls) e.className = cls; if (txt != null) e.textContent = txt; return e; };
  const fmtNum = n => nf.format(Math.round(n)).split('.').join('<i class="sep">.</i>');

  function renderPreview(res) {
    $('pv').hidden = false;
    $('pvTotal').textContent = res.total; $('pvOk').textContent = res.ok.length; $('pvBad').textContent = res.bad.length;
    $('pvIn').innerHTML = fmtNum(res.sum.pemasukan); $('pvOut').innerHTML = fmtNum(res.sum.pengeluaran); $('pvSav').innerHTML = fmtNum(res.sum.tabungan);
    $('pvCntIn').textContent = res.cnt.pemasukan + ' baris'; $('pvCntOut').textContent = res.cnt.pengeluaran + ' baris'; $('pvCntSav').textContent = res.cnt.tabungan + ' baris';

    const body = $('pvRows'); body.textContent = '';
    const LIMIT = 10;
    res.ok.slice(0, LIMIT).forEach(r => {
      const tr = mk('tr');
      const td1 = mk('td'); td1.appendChild(mk('span', 'av-date', r.d));
      const td2 = mk('td'); td2.appendChild(mk('span', 'av-badge av-badge--' + r.j, JENIS[r.j]));
      const td3 = mk('td', null, r.desc);
      const td4 = mk('td'); td4.appendChild(r.cat === '-' ? mk('span', 'av-mono', '—') : mk('span', 'av-chip', r.cat));
      const td5 = mk('td', 'r av-amt');
      td5.appendChild(mk('span', 'av-sign', r.j === 'pemasukan' ? '+' : '−'));
      td5.appendChild(mk('span', 'av-cur', 'Rp'));
      td5.appendChild(document.createTextNode(rupiah(r.amt)));
      [td1, td2, td3, td4, td5].forEach(td => tr.appendChild(td)); body.appendChild(tr);
    });
    if (!res.ok.length) {
      const tr = mk('tr'); const td = mk('td', 'av-mono', 'Tidak ada baris yang terbaca valid.');
      td.colSpan = 5; td.style.padding = '28px'; td.style.textAlign = 'center'; tr.appendChild(td); body.appendChild(tr);
    }

    const more = $('pvMore');
    more.hidden = res.ok.length <= LIMIT;
    more.textContent = 'Menampilkan ' + LIMIT + ' dari ' + res.ok.length + ' baris valid. Sisanya ikut diimport.';

    $('pvSkip').hidden = !res.bad.length;
    $('pvSkipN').textContent = res.bad.length;
    const ul = $('pvSkipList'); ul.textContent = '';
    res.bad.slice(0, 15).forEach(b => ul.appendChild(mk('li', null, 'Baris ' + b.no + ': ' + b.msg)));
    if (res.bad.length > 15) ul.appendChild(mk('li', null, '… dan ' + (res.bad.length - 15) + ' baris lainnya'));
  }

  /* ---------- teks dialog konfirmasi ---------- */
  function syncConfirm() {
    $('pvWarn').hidden = !rp.checked;
    if (!result) return;
    const ok = result.ok.length, s = result.sum;
    let t = ok + ' transaksi akan ditambahkan.\nPemasukan Rp ' + rupiah(s.pemasukan) + ' · Pengeluaran Rp ' + rupiah(s.pengeluaran) + ' · Tabungan Rp ' + rupiah(s.tabungan);
    if (result.bad.length) t += '\n' + result.bad.length + ' baris tidak valid akan dilewati.';
    if (rp.checked) {
      t += '\nSEMUA DATA LAMA AKAN DIHAPUS terlebih dahulu. Tindakan ini tidak bisa dibatalkan.';
      form.dataset.confirmTone = 'danger'; form.dataset.confirmTitle = 'Ganti semua data?'; form.dataset.confirmOk = 'Ya, hapus & import';
    } else {
      delete form.dataset.confirmTone; form.dataset.confirmTitle = 'Import ' + ok + ' transaksi?'; form.dataset.confirmOk = 'Ya, import';
    }
    form.dataset.confirmText = t;
  }

  /* ---------- file dipilih ---------- */
  function sync() {
    const f = input.files[0];
    drop.classList.remove('is-bad');
    if (!f) {
      result = null; $('pv').hidden = true;
      drop.classList.remove('is-set');
      $('ico').textContent = '↑'; $('dt').textContent = 'Pilih atau jatuhkan file CSV'; $('ds').hidden = false;
      $('fn').hidden = true; go.disabled = true; $('hint').textContent = 'Belum ada file dipilih';
      return;
    }
    drop.classList.add('is-set');
    $('ico').textContent = '✓'; $('dt').textContent = 'File dipilih'; $('ds').hidden = true;
    $('fn').hidden = false; $('fn').textContent = f.name + ' · ' + size(f.size);
    go.disabled = true; $('hint').textContent = 'Membaca isi file…';

    f.text().then(text => {
      if (input.files[0] !== f) return;
      result = analyse(parseCSV(text));
      renderPreview(result); syncConfirm();
      go.disabled = false;
      $('hint').textContent = result.ok.length
        ? result.ok.length + ' baris siap diimport' + (result.bad.length ? ', ' + result.bad.length + ' dilewati' : '')
        : 'Tidak ada baris valid, periksa format file';
      $('pv').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }).catch(() => { go.disabled = false; $('hint').textContent = 'Tidak bisa membaca file di browser'; });
  }

  input.addEventListener('change', sync);
  rp.addEventListener('change', syncConfirm);
  ['dragenter', 'dragover'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.add('is-over'); }));
  ['dragleave', 'drop'].forEach(e => drop.addEventListener(e, () => drop.classList.remove('is-over')));
  drop.addEventListener('drop', ev => {
    ev.preventDefault();
    const f = ev.dataTransfer.files[0];
    if (f && /\.csv$/i.test(f.name)) { input.files = ev.dataTransfer.files; sync(); }
    else { drop.classList.add('is-bad'); $('hint').textContent = 'File harus berformat .csv'; if (window.avToast) avToast('File harus berformat .csv', { type: 'error' }); }
  });

  // status tombol: dipicu dialog konfirmasi (partials/feedback)
  form.addEventListener('av:confirmed', () => { go.disabled = true; go.classList.add('is-busy'); $('goTxt').textContent = 'Mengimport...'; });
  form.addEventListener('av:reset', () => { go.disabled = false; go.classList.remove('is-busy'); $('goTxt').textContent = 'Import Sekarang →'; });

  sync();
})();
</script>
@endpush