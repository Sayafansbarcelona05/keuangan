@extends('layouts.app')
@section('title',$label) @section('subtitle','Kelola data '.strtolower($label))
@section('actions')<a href="{{ route('trx.create',$type) }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Tambah {{ $label }}</a>@endsection

@section('content')
<?php
  // angka rapi: pemisah ribuan diredupkan, minus dipisah
  $num = function ($n) {
    $t = str_replace('.', '<i class="sep">.</i>', number_format(abs($n), 0, ',', '.'));
    return ($n < 0 ? '<span class="neg">−</span>' : '') . $t;
  };
  $tones    = ['pemasukan' => 'site', 'pengeluaran' => 'material', 'tabungan' => 'blueprint'];
  $tone     = $tones[(string) $type] ?? 'site';
  $sign     = $type === 'pemasukan' ? '+' : '−';
  $filtered = request()->filled('cari') || request()->filled('bulan');
  $items->appends(request()->query());
?>

<style>
  .av {
    --ink: #0a1217; --paper: #ffffff; --gray: #f1f1f1; --hair: #e6e7e8;
    --stone: #85898b; --graphite: #54595d; --lime: #cdfe00;
    --blueprint: #dceaf8; --material: #f4e3cd; --site: #ddefe2;
    --mono: 'Geist Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    display: grid; gap: 24px; font-weight: 350; line-height: 1.5; color: var(--ink);
  }
  .av *, .av *::before, .av *::after { box-sizing: border-box; }
  .av-mono { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; }

  /* ---- animasi ---- */
  @keyframes av-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  .av-rise { animation: av-rise .6s cubic-bezier(.2,.7,.2,1) calc(var(--i, 0) * 70ms + 100ms) both; }
  .av-row  { animation: av-rise .45s cubic-bezier(.2,.7,.2,1) calc(var(--r, 0) * 30ms + 450ms) both; }
  @media (prefers-reduced-motion: reduce) { .av *, .av *::before, .av *::after { animation: none !important; transition: none !important; } }

  /* ---- kartu statistik ---- */
  .av-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
  .av-stat {
    container-type: inline-size; border-radius: 24px; padding: 28px; min-height: 168px;
    display: flex; flex-direction: column; justify-content: space-between; gap: 24px; border: 1px solid transparent;
  }
  .av-stat .av-mono { opacity: .6; display: flex; justify-content: space-between; align-items: center; }
  .av-stat--dark      { background: var(--ink); color: var(--paper); }
  .av-stat--site      { background: var(--site); }
  .av-stat--material  { background: var(--material); }
  .av-stat--blueprint { background: var(--blueprint); }
  .av-dot { width: 10px; height: 10px; border-radius: 3px; background: var(--lime); }
  .av-name { margin-bottom: 12px; font-size: 14px; font-weight: 450; letter-spacing: .35px; opacity: .75; }
  .av-amount { display: flex; align-items: flex-start; gap: 8px; white-space: nowrap; font-size: clamp(20px, 10cqi, 44px); }
  .av-amount .cur {
    flex: none; font-size: max(11px, .34em); font-weight: 650; letter-spacing: .5px; line-height: 1.3;
    padding: 3px 8px; border-radius: 999px; background: rgba(10,18,23,.1); margin-top: .12em;
  }
  .av-stat--dark .av-amount .cur { background: rgba(255,255,255,.16); }
  .av-amount .num { font-weight: 650; line-height: 1; letter-spacing: -.045em; font-variant-numeric: tabular-nums; min-width: 0; }
  .av .sep { font-style: normal; opacity: .32; letter-spacing: 0; margin: 0 .03em; }
  .av .neg { margin-right: .06em; }
  .av-cap { margin-top: 12px; opacity: .65; }

  /* ---- panel utama ---- */
  .av-card { background: var(--paper); border: 1px solid var(--hair); border-radius: 24px; padding: 28px; }
  .av-filter { display: grid; grid-template-columns: 1.6fr 1fr auto; gap: 8px; margin-bottom: 20px; }
  .av-input {
    width: 100%; height: 44px; padding: 0 14px; font-family: inherit; font-size: 14px; font-weight: 350; color: var(--ink);
    background: var(--paper); border: 1px solid var(--hair); border-radius: 8px; outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .av-input::placeholder { color: var(--stone); }
  .av-input:hover { border-color: var(--stone); }
  .av-input:focus { border-color: var(--ink); box-shadow: 0 0 0 3px var(--lime); }
  .av-actions { display: flex; gap: 8px; }
  .av-btn {
    height: 44px; padding: 0 24px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    border-radius: 999px; border: 0; font-family: inherit; font-size: 14px; font-weight: 550; letter-spacing: .35px;
    text-decoration: none; cursor: pointer; transition: background .15s ease, transform .1s ease;
  }
  .av-btn:active { transform: translateY(1px); }
  .av-btn:focus-visible { outline: 2px solid var(--ink); outline-offset: 3px; }
  .av-btn--lime { background: var(--lime); color: var(--ink); }
  .av-btn--lime:hover { background: #c2f000; color: var(--ink); }
  .av-btn--gray { background: var(--gray); color: var(--ink); border: 1px solid var(--hair); }
  .av-btn--gray:hover { background: var(--hair); color: var(--ink); }
  .av-btn.is-off { opacity: .4; pointer-events: none; }

  /* ---- tabel ---- */
  .av-table-wrap { overflow-x: auto; border-top: 1px solid var(--hair); margin: 0 -28px; }
  .av-table { width: 100%; border-collapse: collapse; font-size: 14px; }
  .av-table th {
    padding: 14px 16px; text-align: left; white-space: nowrap;
    font-size: 12px; font-weight: 550; letter-spacing: .96px; text-transform: uppercase; color: var(--graphite);
    border-bottom: 1px solid var(--hair);
  }
  .av-table th:first-child, .av-table td:first-child { padding-left: 28px; }
  .av-table th:last-child,  .av-table td:last-child  { padding-right: 28px; }
  .av-table td { padding: 14px 16px; border-bottom: 1px solid var(--hair); vertical-align: middle; }
  .av-table tbody tr { transition: background .15s ease; }
  .av-table tbody tr:hover { background: #fafafa; }
  .av-table tbody tr:last-child td { border-bottom: 0; }
  .av-table .r { text-align: right; }
  .av-date { font-family: var(--mono); font-size: 13px; letter-spacing: -0.36px; color: var(--graphite); white-space: nowrap; }
  .av-desc { font-weight: 450; }
  .av-chip { display: inline-block; padding: 2px 11px; border: 1px solid var(--hair); border-radius: 999px; font-size: 13px; font-weight: 400; color: var(--graphite); white-space: nowrap; }
  .av-none-cat { color: var(--stone); }
  .av-amt { font-weight: 550; font-variant-numeric: tabular-nums; white-space: nowrap; letter-spacing: -.01em; }
  .av-sign { display: inline-block; width: 1.1ch; margin-right: 6px; text-align: center; color: var(--stone); }
  .av-cur  { margin-right: 5px; font-size: 11px; font-weight: 550; letter-spacing: .5px; color: var(--stone); }
  .av-ops { display: inline-flex; gap: 6px; }
  .av-ops form { margin: 0; }
  .av-icon {
    width: 36px; height: 36px; display: inline-grid; place-content: center; padding: 0;
    border-radius: 50%; border: 1px solid var(--hair); background: var(--gray); color: var(--ink);
    font-size: 15px; line-height: 1; cursor: pointer; text-decoration: none;
    transition: background .15s ease, color .15s ease, border-color .15s ease;
  }
  .av-icon:hover { background: var(--ink); color: var(--paper); border-color: var(--ink); }
  .av-icon:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }

  /* ---- kosong ---- */
  .av-empty { text-align: center; padding: 56px 16px !important; }
  .av-empty .av-mono { color: var(--stone); display: block; margin-bottom: 10px; }
  .av-empty p { margin: 0 0 20px; font-size: 16px; color: var(--graphite); }

  /* ---- paginasi ---- */
  .av-pager { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 20px 28px 0; margin: 0 -28px; border-top: 1px solid var(--hair); }
  .av-pager .av-mono { color: var(--stone); text-align: center; }

  /* ---- responsif ---- */
  @media (max-width: 767px) {
    .av { gap: 16px; }
    .av-stats { grid-template-columns: 1fr; gap: 16px; }
    .av-stat { min-height: 0; padding: 20px; border-radius: 20px; }
    .av-card { padding: 20px; border-radius: 20px; }
    .av-filter { grid-template-columns: 1fr 1fr; }
    .av-filter > :first-child, .av-filter .av-actions { grid-column: 1 / -1; }
    .av-table-wrap, .av-pager { margin-left: -20px; margin-right: -20px; }
    .av-table th:first-child, .av-table td:first-child { padding-left: 20px; }
    .av-table th:last-child,  .av-table td:last-child  { padding-right: 20px; }
    .av-pager { padding-left: 20px; padding-right: 20px; }
  }
</style>

<div class="av">

  {{-- Statistik --}}
  <div class="av-stats">
    <div class="av-stat av-stat--{{ $tone }} av-rise" style="--i:0">
      <div class="av-mono"><span>01 / TOTAL</span></div>
      <div>
        <div class="av-name">Total {{ $label }}</div>
        <div class="av-amount"><span class="cur">Rp</span><span class="num">{!! $num($total) !!}</span></div>
        <div class="av-mono av-cap">{{ $filtered ? 'Sesuai filter aktif' : 'Semua data' }}</div>
      </div>
    </div>

    <div class="av-stat av-stat--dark av-rise" style="--i:1">
      <div class="av-mono"><span>02 / SALDO</span><span class="av-dot"></span></div>
      <div>
        <div class="av-name">Saldo Tersedia</div>
        <div class="av-amount"><span class="cur">Rp</span><span class="num">{!! $num($saldo) !!}</span></div>
        @if ($type != 'pemasukan')
          <div class="av-mono av-cap">{{ $label }} diambil dari saldo pemasukan</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Daftar --}}
  <div class="av-card av-rise" style="--i:2">
    <form class="av-filter" method="GET">
      <input name="cari" value="{{ request('cari') }}" class="av-input" placeholder="Cari keterangan...">
      <input type="month" name="bulan" value="{{ request('bulan') }}" class="av-input" aria-label="Bulan">
      <div class="av-actions">
        <button class="av-btn av-btn--lime">Filter</button>
        <a href="{{ route('trx.index',$type) }}" class="av-btn av-btn--gray">Reset</a>
      </div>
    </form>

    <div class="av-table-wrap">
      <table class="av-table">
        <thead>
          <tr><th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th class="r">Jumlah</th><th class="r">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($items as $t)
            <tr class="av-row" style="--r:{{ $loop->index }}">
              <td><span class="av-date">{{ $t->date->format('d M Y') }}</span></td>
              <td class="av-desc">{{ $t->description }}</td>
              <td>@if ($t->category)<span class="av-chip">{{ $t->category }}</span>@else<span class="av-none-cat">—</span>@endif</td>
              <td class="r av-amt"><span class="av-sign">{{ $sign }}</span><span class="av-cur">Rp</span>{!! $num($t->amount) !!}</td>
              <td class="r">
                <div class="av-ops">
                  <a href="{{ route('trx.edit',[$type,$t]) }}" class="av-icon" title="Ubah" aria-label="Ubah"><i class="bi bi-pencil"></i></a>
                  <form method="POST" action="{{ route('trx.destroy',[$type,$t]) }}"
                        data-confirm="Data ini akan dihapus permanen."
                        data-confirm-title="Hapus data?"
                        data-confirm-text="“{{ $t->description }}” · Rp {{ number_format($t->amount,0,',','.') }}&#10;Tindakan ini tidak bisa dibatalkan."
                        data-confirm-ok="Ya, hapus" data-confirm-tone="danger" data-ajax="stay">@csrf @method('DELETE')
                    <button type="submit" class="av-icon" title="Hapus" aria-label="Hapus"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="av-empty">
                <span class="av-mono">0 DATA</span>
                <p>Belum ada data {{ strtolower($label) }}.</p>
                <a href="{{ route('trx.create',$type) }}" class="av-btn av-btn--lime">Tambah {{ $label }}</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($items->hasPages())
      <div class="av-pager">
        <a class="av-btn av-btn--gray {{ $items->onFirstPage() ? 'is-off' : '' }}" href="{{ $items->previousPageUrl() ?? '#' }}">← Sebelumnya</a>
        <span class="av-mono">Halaman {{ $items->currentPage() }}@if (method_exists($items, 'lastPage')) dari {{ $items->lastPage() }}@endif</span>
        <a class="av-btn av-btn--gray {{ $items->hasMorePages() ? '' : 'is-off' }}" href="{{ $items->nextPageUrl() ?? '#' }}">Berikutnya →</a>
      </div>
    @endif
  </div>

</div>
@endsection