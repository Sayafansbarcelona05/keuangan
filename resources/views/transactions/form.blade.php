@extends('layouts.app')
@section('title',($item->exists?'Edit ':'Tambah ').$label)
@section('subtitle','Saldo tersedia: Rp '.number_format($saldo,0,',','.'))

@section('content')
<?php
  $t    = (string) $type;
  $cats = $t == 'pemasukan' ? ['Gaji','Uang Bulanan','Bonus','Lainnya']
        : ($t == 'tabungan' ? ['Dana Darurat','Investasi','Liburan','Lainnya']
        : ['Belanja','Makanan','Transportasi','Tagihan','Hiburan','Lainnya']);
  $ph   = $t == 'pemasukan' ? 'Uang bulanan' : ($t == 'tabungan' ? 'Tabungan darurat' : 'Belanja telur');
  $tones = ['pemasukan' => 'site', 'pengeluaran' => 'material', 'tabungan' => 'blueprint'];
  $tone = $tones[$t] ?? 'site';
  $dir  = $t == 'pemasukan' ? 1 : -1;
  // saldo tanpa pengaruh data ini (supaya pratinjau "saldo setelah" benar saat mode edit)
  $saldoBase = (float) $saldo - ($item->exists ? $dir * (float) $item->amount : 0);
?>

<style>
  .av {
    --ink: #0a1217; --paper: #ffffff; --gray: #f1f1f1; --hair: #e6e7e8;
    --stone: #85898b; --graphite: #54595d; --lime: #cdfe00;
    --blueprint: #dceaf8; --material: #f4e3cd; --site: #ddefe2;
    --mono: 'Geist Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 24px; align-items: start;
    font-weight: 350; line-height: 1.5; color: var(--ink);
  }
  .av *, .av *::before, .av *::after { box-sizing: border-box; }
  .av-mono { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; }

  @keyframes av-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  .av-rise { animation: av-rise .6s cubic-bezier(.2,.7,.2,1) calc(var(--i, 0) * 70ms + 100ms) both; }
  @media (prefers-reduced-motion: reduce) { .av *, .av *::before, .av *::after { animation: none !important; transition: none !important; } }

  /* ---- form ---- */
  .av-card { background: var(--paper); border: 1px solid var(--hair); border-radius: 24px; padding: 28px; }
  .av-field { margin-bottom: 20px; }
  .av-label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 550; letter-spacing: .96px; text-transform: uppercase; }
  .av-label small { font-size: 12px; font-weight: 350; letter-spacing: 0; text-transform: none; color: var(--stone); margin-left: 6px; }
  .av-input {
    width: 100%; height: 48px; padding: 0 14px; font-family: inherit; font-size: 16px; font-weight: 350; color: var(--ink);
    background: var(--paper); border: 1px solid var(--hair); border-radius: 8px; outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .av-input::placeholder { color: var(--stone); }
  .av-input:hover { border-color: var(--stone); }
  .av-input:focus { border-color: var(--ink); box-shadow: 0 0 0 3px var(--lime); }
  .av-input.has-error { border-color: var(--ink); }
  .av-error { margin-top: 6px; font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; }
  .av-error::before { content: "! "; font-weight: 700; }

  .av-money { position: relative; }
  .av-money .pre {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    padding: 3px 9px; border-radius: 999px; background: var(--gray); border: 1px solid var(--hair);
    font-size: 12px; font-weight: 650; letter-spacing: .5px; pointer-events: none;
  }
  .av-money .av-input { padding-left: 58px; font-size: 20px; font-weight: 550; letter-spacing: -.01em; font-variant-numeric: tabular-nums; height: 56px; }
  .av-money input[type=number]::-webkit-outer-spin-button,
  .av-money input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
  .av-money input[type=number] { -moz-appearance: textfield; appearance: textfield; }

  .av-picks { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
  .av-pick {
    height: 30px; padding: 0 12px; border-radius: 999px; border: 1px solid var(--hair); background: var(--paper);
    font-family: inherit; font-size: 13px; font-weight: 400; color: var(--graphite); cursor: pointer;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
  }
  .av-pick:hover { background: var(--gray); color: var(--ink); }
  .av-pick.on { background: var(--lime); border-color: var(--ink); color: var(--ink); font-weight: 550; }
  .av-pick:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }

  .av-row { display: flex; gap: 8px; margin-top: 8px; padding-top: 24px; border-top: 1px solid var(--hair); }
  .av-btn {
    height: 48px; padding: 0 28px; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    border-radius: 999px; border: 0; font-family: inherit; font-size: 14px; font-weight: 550; letter-spacing: .35px;
    text-decoration: none; cursor: pointer; transition: background .15s ease, transform .1s ease;
  }
  .av-btn:active { transform: translateY(1px); }
  .av-btn:focus-visible { outline: 2px solid var(--ink); outline-offset: 3px; }
  .av-btn--lime { background: var(--lime); color: var(--ink); flex: 1; }
  .av-btn--lime:hover { background: #c2f000; color: var(--ink); }
  .av-btn--lime[disabled] { opacity: .7; cursor: progress; }
  .av-btn--gray { background: var(--gray); color: var(--ink); border: 1px solid var(--hair); }
  .av-btn--gray:hover { background: var(--hair); color: var(--ink); }
  .av-spin { display: none; width: 14px; height: 14px; border: 2px solid rgba(10,18,23,.25); border-top-color: var(--ink); border-radius: 50%; animation: av-spin .7s linear infinite; }
  .av-btn[disabled] .av-spin { display: inline-block; }
  @keyframes av-spin { to { transform: rotate(360deg); } }

  /* ---- pratinjau ---- */
  .av-side { position: sticky; top: 24px; }
  .av-prev { container-type: inline-size; border-radius: 24px; padding: 28px; border: 1px solid transparent; }
  .av-prev--site { background: var(--site); } .av-prev--material { background: var(--material); } .av-prev--blueprint { background: var(--blueprint); }
  .av-prev .av-top { display: flex; justify-content: space-between; align-items: center; opacity: .6; }
  .av-prev .av-name { margin: 28px 0 12px; font-size: 14px; font-weight: 450; letter-spacing: .35px; opacity: .75; }
  .av-amount { display: flex; align-items: flex-start; gap: 8px; white-space: nowrap; font-size: clamp(22px, 13cqi, 44px); }
  .av-amount .cur {
    flex: none; font-size: max(11px, .34em); font-weight: 650; letter-spacing: .5px; line-height: 1.3;
    padding: 3px 8px; border-radius: 999px; background: rgba(10,18,23,.1); margin-top: .12em;
  }
  .av-amount .num { font-weight: 650; line-height: 1; letter-spacing: -.045em; font-variant-numeric: tabular-nums; min-width: 0; }
  .av .sep { font-style: normal; opacity: .32; letter-spacing: 0; margin: 0 .03em; }
  .av .neg { margin-right: .06em; }
  .av-cap { margin-top: 12px; opacity: .65; }
  .av-lines { margin: 24px 0 0; padding: 0; list-style: none; border-top: 1px solid rgba(10,18,23,.14); }
  .av-lines li { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 12px 0; border-bottom: 1px solid rgba(10,18,23,.14); font-size: 14px; }
  .av-lines li:last-child { border-bottom: 0; padding-bottom: 0; }
  .av-lines .k { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; opacity: .6; flex: none; }
  .av-lines .v { text-align: right; font-weight: 450; min-width: 0; overflow-wrap: anywhere; }
  .av-lines .is-empty { color: var(--graphite); opacity: .55; font-weight: 350; }
  .av-chip { display: inline-block; padding: 2px 11px; border: 1px solid rgba(10,18,23,.25); border-radius: 999px; font-size: 13px; font-weight: 400; background: rgba(255,255,255,.55); }
  .av-after { margin-top: 12px; background: var(--ink); color: var(--paper); border-radius: 16px; padding: 16px 18px; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
  .av-after .av-mono { opacity: .6; }
  .av-after .v { font-size: 18px; font-weight: 650; letter-spacing: -.02em; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .av-after .v small { font-size: 11px; font-weight: 650; letter-spacing: .5px; margin-right: 6px; opacity: .6; }

  @media (max-width: 991px) {
    .av { grid-template-columns: 1fr; }
    .av-side { position: static; }
  }
  @media (max-width: 575px) {
    .av-card, .av-prev { padding: 20px; border-radius: 20px; }
    .av-row { flex-direction: column-reverse; }
    .av-btn { width: 100%; }
  }
</style>

<div class="av">

  {{-- Form --}}
  <div class="av-card av-rise" style="--i:0">
    <form method="POST" action="{{ $item->exists ? route('trx.update',[$type,$item]) : route('trx.store',$type) }}" id="trxForm"
      data-confirm="{{ $item->exists ? 'Simpan perubahan data ini?' : 'Tambah data ini?' }}"
      data-confirm-title="{{ $item->exists ? 'Simpan perubahan?' : 'Tambah '.$label.'?' }}"
      data-confirm-ok="{{ $item->exists ? 'Ya, simpan' : 'Ya, tambah' }}" data-ajax>@csrf @if($item->exists) @method('PUT') @endif

      <div class="av-field">
        <label class="av-label" for="date">Tanggal</label>
        <input id="date" type="date" name="date" value="{{ old('date', optional($item->date)->format('Y-m-d')) }}" class="av-input @error('date') has-error @enderror" required>
        @error('date')<div class="av-error">{{ $message }}</div>@enderror
      </div>

      <div class="av-field">
        <label class="av-label" for="description">Keterangan</label>
        <input id="description" name="description" value="{{ old('description',$item->description) }}" class="av-input @error('description') has-error @enderror" placeholder="{{ $ph }}" autocomplete="off" required>
        @error('description')<div class="av-error">{{ $message }}</div>@enderror
      </div>

      <div class="av-field">
        <label class="av-label" for="category">Kategori <small>(opsional)</small></label>
        <input id="category" name="category" list="cats" value="{{ old('category',$item->category) }}" class="av-input @error('category') has-error @enderror" placeholder="Pilih atau ketik sendiri" autocomplete="off">
        <datalist id="cats">@foreach ($cats as $c)<option value="{{ $c }}">@endforeach</datalist>
        <div class="av-picks" id="picks">
          @foreach ($cats as $c)<button type="button" class="av-pick">{{ $c }}</button>@endforeach
        </div>
        @error('category')<div class="av-error">{{ $message }}</div>@enderror
      </div>

      <div class="av-field">
        <label class="av-label" for="amount">Jumlah (Rp)</label>
        <div class="av-money">
          <span class="pre">Rp</span>
          <input id="amount" type="number" name="amount" min="1" step="1" inputmode="numeric" placeholder="0" value="{{ old('amount', $item->amount ? (int)$item->amount : '') }}" class="av-input @error('amount') has-error @enderror" required>
        </div>
        @error('amount')<div class="av-error">{{ $message }}</div>@enderror
      </div>

      <div class="av-row">
        <button type="submit" class="av-btn av-btn--lime" id="saveBtn"><span class="av-spin" aria-hidden="true"></span><span id="saveTxt">Simpan</span></button>
        <a href="{{ route('trx.index',$type) }}" class="av-btn av-btn--gray">Batal</a>
      </div>
    </form>
  </div>

  {{-- Pratinjau langsung --}}
  <aside class="av-side av-rise" style="--i:1" aria-label="Pratinjau">
    <div class="av-prev av-prev--{{ $tone }}">
      <div class="av-top av-mono"><span>PRATINJAU</span><span>{{ strtoupper($t) }}</span></div>
      <div class="av-name">{{ $label }}</div>
      <div class="av-amount"><span class="cur">Rp</span><span class="num" id="pNum">0</span></div>
      <div class="av-mono av-cap">{{ $dir > 0 ? 'Menambah saldo' : 'Mengurangi saldo' }}</div>

      <ul class="av-lines">
        <li><span class="k">TANGGAL</span><span class="v" id="pDate">—</span></li>
        <li><span class="k">KETERANGAN</span><span class="v is-empty" id="pDesc">Belum diisi</span></li>
        <li><span class="k">KATEGORI</span><span class="v" id="pCat">—</span></li>
      </ul>
    </div>

    <div class="av-after">
      <span class="av-mono">SALDO SETELAH</span>
      <span class="v"><small>Rp</small><span id="pAfter">0</span></span>
    </div>
  </aside>

</div>

<script>
(function () {
  var $ = function (id) { return document.getElementById(id); };
  var d = $('date'), desc = $('description'), cat = $('category'), amt = $('amount');
  var pNum = $('pNum'), pDate = $('pDate'), pDesc = $('pDesc'), pCat = $('pCat'), pAfter = $('pAfter');
  var picks = document.querySelectorAll('#picks .av-pick');
  var SALDO_BASE = {{ $saldoBase }};
  var DIR = {{ $dir }};
  var nf = new Intl.NumberFormat('id-ID');

  function fmt(n) {
    var s = nf.format(Math.abs(n)).split('.').join('<i class="sep">.</i>');
    return (n < 0 ? '<span class="neg">−</span>' : '') + s;
  }

  function update() {
    var v = parseFloat(amt.value) || 0;
    pNum.innerHTML = fmt(v);
    pAfter.innerHTML = fmt(SALDO_BASE + DIR * v);

    var txt = desc.value.trim();
    pDesc.textContent = txt || 'Belum diisi';
    pDesc.classList.toggle('is-empty', !txt);

    var c = cat.value.trim();
    pCat.innerHTML = '';
    if (c) { var chip = document.createElement('span'); chip.className = 'av-chip'; chip.textContent = c; pCat.appendChild(chip); }
    else { pCat.textContent = '—'; }

    if (d.value) {
      var dt = new Date(d.value + 'T00:00:00');
      pDate.textContent = isNaN(dt) ? '—' : dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } else { pDate.textContent = '—'; }

    // teks konfirmasi dinamis (dibaca oleh partials/feedback)
    var after = SALDO_BASE + DIR * v;
    var msg = (txt ? '“' + txt + '”' : 'Tanpa keterangan') + ' · Rp ' + nf.format(v) + '\nSaldo setelah: Rp ' + nf.format(after);
    if (after < 0) msg += '\nPerhatian: saldo akan menjadi minus.';
    form.dataset.confirmText = msg;

    picks.forEach(function (b) { b.classList.toggle('on', b.textContent.trim() === c); });
  }

  [d, desc, cat, amt].forEach(function (el) { el.addEventListener('input', update); el.addEventListener('change', update); });
  picks.forEach(function (b) {
    b.addEventListener('click', function () { cat.value = b.textContent.trim(); update(); cat.focus(); });
  });

  var form = $('trxForm'), btn = $('saveBtn'), saveTxt = $('saveTxt');
  // dipicu setelah pengguna menekan "Ya" pada dialog konfirmasi
  form.addEventListener('av:confirmed', function () { btn.disabled = true; saveTxt.textContent = 'Menyimpan...'; });
  form.addEventListener('av:reset', function () { btn.disabled = false; saveTxt.textContent = 'Simpan'; });

  update();
})();
</script>
@endsection