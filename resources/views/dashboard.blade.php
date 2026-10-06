@extends('layouts.app')
@section('title','Dashboard') @section('subtitle','Ringkasan keuangan & semua transaksi')
@php($rp = fn($n) => 'Rp '.number_format($n,0,',','.'))

@section('content')
<?php
  // angka rapi: pemisah ribuan diredupkan, minus dipisah
  $num = function ($n) {
    $t = str_replace('.', '<i class="sep">.</i>', number_format(abs($n), 0, ',', '.'));
    return ($n < 0 ? '<span class="neg">−</span>' : '') . $t;
  };
  $base = max((float) $pemasukan, 0);
  $pct  = fn ($v) => $base > 0 ? (int) round($v / $base * 100) : null;
  $stats = [
    ['Saldo Tersedia',    $saldo,       'dark',      '01', $pct($saldo)],
    ['Total Pemasukan',   $pemasukan,   'site',      '02', $base > 0 ? 100 : null],
    ['Total Pengeluaran', $pengeluaran, 'material',  '03', $pct($pengeluaran)],
    ['Total Tabungan',    $tabungan,    'blueprint', '04', $pct($tabungan)],
  ];
  $levels = [
    'success' => ['BAIK',      '#1a7f55'],
    'warning' => ['PERHATIAN', '#9a6700'],
    'danger'  => ['PENTING',   '#0a1217'],
    'info'    => ['INFO',      '#24476b'],
  ];
  $transactions->appends(request()->query());
?>

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

  /* ---- util ---- */
  .av-mono  { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; color: var(--stone); }
  .av-label { font-size: 12px; font-weight: 550; letter-spacing: .96px; text-transform: uppercase; }
  .av-card  { background: var(--paper); border: 1px solid var(--hair); border-radius: 24px; padding: 28px; }
  .av-title { margin: 0 0 20px; font-size: 24px; font-weight: 550; line-height: 1.2; letter-spacing: -0.24px; }
  .av-head  { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
  .av-head .av-title { margin: 0; }

  /* ---- animasi masuk ---- */
  @keyframes av-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  @keyframes av-grow { from { width: 0; } }
  .av-rise { animation: av-rise .6s cubic-bezier(.2,.7,.2,1) calc(var(--i, 0) * 70ms + 100ms) both; }
  .av-fill { animation: av-grow .9s cubic-bezier(.2,.7,.2,1) .6s both; }
  @media (prefers-reduced-motion: reduce) { .av *, .av *::before, .av *::after { animation: none !important; transition: none !important; } }

  /* ---- statistik ---- */
  .av-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }

  /* ---- kartu statistik ---- */
  .av-stat {
    container-type: inline-size; position: relative; overflow: hidden;
    border-radius: 24px; padding: 28px; min-height: 204px; border: 1px solid transparent;
    display: flex; flex-direction: column; justify-content: space-between; gap: 28px;
  }
  .av-stat .av-mono { color: inherit; opacity: .6; display: flex; justify-content: space-between; align-items: center; }
  .av-stat--dark      { background: var(--ink); color: var(--paper); }
  .av-stat--site      { background: var(--site); }
  .av-stat--material  { background: var(--material); }
  .av-stat--blueprint { background: var(--blueprint); }
  .av-dot { width: 10px; height: 10px; border-radius: 3px; background: var(--lime); }
  .av-name { margin-bottom: 12px; font-size: 14px; font-weight: 450; letter-spacing: .35px; opacity: .75; }

  /* angka: satu baris, "Rp" kecil di pojok kiri atas, ukuran menyesuaikan lebar kartu */
  .av-amount { display: flex; align-items: flex-start; gap: 8px; white-space: nowrap; font-size: clamp(18px, 12cqi, 36px); }
  .av-amount .cur {
    flex: none; font-size: max(11px, .36em); font-weight: 650; letter-spacing: .5px; line-height: 1.3;
    padding: 3px 8px; border-radius: 999px; background: rgba(10,18,23,.1); margin-top: .12em;
  }
  .av-stat--dark .av-amount .cur { background: rgba(255,255,255,.16); }
  .av-amount .num { font-weight: 650; line-height: 1; letter-spacing: -.045em; font-variant-numeric: tabular-nums; min-width: 0; }
  .av .sep { font-style: normal; opacity: .32; letter-spacing: 0; margin: 0 .03em; }
  .av .neg { margin-right: .06em; }

  .av-meter { height: 4px; border-radius: 999px; background: rgba(10,18,23,.1); overflow: hidden; margin: 18px 0 10px; }
  .av-meter > span { display: block; height: 100%; width: var(--w); border-radius: 999px; background: var(--ink); }
  .av-stat--dark .av-meter { background: rgba(255,255,255,.16); }
  .av-stat--dark .av-meter > span { background: var(--lime); }
  .av-cap { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; opacity: .65; }
  @media (max-width: 640px) { .av-stat { min-height: 0; padding: 20px; border-radius: 20px; } }

  /* ---- grafik & kelompok ---- */
  .av-grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
  .av-chart  { position: relative; height: 300px; }
  .av-group  { margin-bottom: 18px; }
  .av-group:last-child { margin-bottom: 0; }
  .av-group-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: 450; margin-bottom: 8px; }
  .av-group-row .pct { font-family: var(--mono); font-size: 12px; letter-spacing: -0.36px; }
  .av-bar  { height: 8px; border-radius: 999px; background: var(--gray); overflow: hidden; }
  .av-bar > span { display: block; height: 100%; border-radius: 999px; background: var(--ink); width: var(--w); }
  .av-group small { display: block; margin-top: 6px; }
  .av-empty { color: var(--graphite); font-size: 14px; margin: 0; }

  /* ---- badge ---- */
  .av-badge {
    display: inline-block; padding: 3px 11px; border-radius: 999px;
    font-size: 13px; font-weight: 450; line-height: 1.5; color: var(--paper); background: #3e4a50; white-space: nowrap;
  }
  .av-badge--pemasukan   { background: var(--lime); color: var(--ink); }
  .av-badge--pengeluaran { background: var(--ink); }
  .av-badge--tabungan    { background: var(--plan); }

  /* ---- saran ---- */
  .av-tips { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .av-tip  { border: 1px solid var(--hair); border-radius: 12px; padding: 16px; background: var(--paper); display: flex; flex-direction: column; gap: 8px; align-items: flex-start; }
  .av-tip b { font-size: 16px; font-weight: 550; letter-spacing: -0.1px; }
  .av-tip p { margin: 0; font-size: 14px; color: var(--graphite); }

  /* ---- filter ---- */
  .av-filter { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr auto; gap: 8px; margin-bottom: 20px; }
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
    height: 44px; padding: 0 24px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 999px; border: 0; font-family: inherit; font-size: 14px; font-weight: 550; letter-spacing: .35px;
    text-decoration: none; cursor: pointer; transition: background .15s ease, transform .1s ease;
  }
  .av-btn:active { transform: translateY(1px); }
  .av-btn:focus-visible { outline: 2px solid var(--ink); outline-offset: 3px; }
  .av-btn--lime { background: var(--lime); color: var(--ink); }
  .av-btn--lime:hover { background: #c2f000; }
  .av-btn--gray { background: var(--gray); color: var(--ink); border: 1px solid var(--hair); }
  .av-btn--gray:hover { background: var(--hair); }

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
  .av-table .muted { color: var(--stone); }
  .av-amt { font-weight: 550; font-variant-numeric: tabular-nums; white-space: nowrap; letter-spacing: -.01em; }
  .av-sign { display: inline-block; width: 1.1ch; margin-right: 6px; text-align: center; color: var(--stone); }
  .av-cur { margin-right: 5px; font-size: 11px; font-weight: 550; letter-spacing: .5px; color: var(--stone); }
  .av-group small { white-space: nowrap; }
  .av-none { text-align: center; padding: 48px 16px !important; color: var(--graphite); }
  .av-none a, .av-link { color: var(--ink); font-weight: 550; text-decoration: underline; text-underline-offset: 3px; }

  /* ---- paginasi ---- */
  .av-pager { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 20px; border-top: 1px solid var(--hair); margin: 0 -28px; padding-left: 28px; padding-right: 28px; }
  .av-btn.is-off { opacity: .4; pointer-events: none; }

  /* ---- responsif ---- */
  @media (max-width: 1199px) {
    .av-stats { grid-template-columns: repeat(2, 1fr); }
    .av-grid-2 { grid-template-columns: 1fr; }
    .av-filter { grid-template-columns: 1fr 1fr; }
    .av-filter > :first-child, .av-filter .av-actions { grid-column: 1 / -1; }
  }
  @media (max-width: 640px) {
    .av { gap: 16px; }
    .av-stats, .av-tips { grid-template-columns: 1fr; gap: 16px; }
    .av-card { padding: 20px; border-radius: 20px; }
    .av-table-wrap, .av-pager { margin-left: -20px; margin-right: -20px; }
    .av-table th:first-child, .av-table td:first-child { padding-left: 20px; }
    .av-table th:last-child,  .av-table td:last-child  { padding-right: 20px; }
    .av-pager { padding-left: 20px; padding-right: 20px; }
  }
</style>

<div class="av">

  {{-- Statistik --}}
  <div class="av-stats">
    @foreach ($stats as $i => [$label, $value, $tone, $no, $p])
      <div class="av-stat av-stat--{{ $tone }} av-rise" style="--i:{{ $i }}">
        <div class="av-mono">
          <span>{{ $no }} / RINGKASAN</span>
          @if ($tone === 'dark')<span class="av-dot"></span>@endif
        </div>
        <div>
          <div class="av-name">{{ $label }}</div>
          <div class="av-amount"><span class="cur">Rp</span><span class="num">{!! $num($value) !!}</span></div>
          <div class="av-meter"><span class="av-fill" style="--w:{{ max(0, min(100, $p ?? 0)) }}%"></span></div>
          <div class="av-cap">
            @if ($tone === 'site') Basis perbandingan
            @elseif (!is_null($p)) {{ $p }}% dari pemasukan
            @else —
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Grafik + kelompok pengeluaran --}}
  <div class="av-grid-2">
    <div class="av-card av-rise" style="--i:4">
      <h3 class="av-title">Arus Kas 6 Bulan Terakhir</h3>
      <div class="av-chart"><canvas id="cf"></canvas></div>
    </div>

    <div class="av-card av-rise" style="--i:5">
      <h3 class="av-title">Pengeluaran per Kelompok</h3>
      @forelse ($advice['groups'] as $g => $v)
        <div class="av-group">
          <div class="av-group-row"><span>{{ $g }}</span><span class="pct">{{ $v['percent'] }}%</span></div>
          <div class="av-bar"><span class="av-fill" style="--w:{{ $v['percent'] }}%"></span></div>
          <small class="av-mono">{{ $rp($v['total']) }} · {{ $v['count'] }}x</small>
        </div>
      @empty
        <p class="av-empty">Belum ada pengeluaran.</p>
      @endforelse
    </div>
  </div>

  {{-- Saran keuangan --}}
  <div class="av-card av-rise" style="--i:6">
    <div class="av-head">
      <h3 class="av-title">Saran Keuangan</h3>
      @if (!is_null($advice['score']))
        @php($sc = $advice['score'])
        <span class="av-badge" style="background:{{ $sc >= 70 ? '#1a7f55' : ($sc >= 45 ? '#9a6700' : '#0a1217') }}">Skor Kesehatan: {{ $sc }}/100</span>
      @endif
    </div>
    <div class="av-tips">
      @foreach ($advice['tips'] as $t)
        @php($lv = $levels[$t['level']] ?? ['INFO', '#3e4a50'])
        <div class="av-tip">
          <span class="av-badge" style="background:{{ $lv[1] }}">{{ $lv[0] }}</span>
          <b>{{ $t['title'] }}</b>
          <p>{{ $t['text'] }}</p>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Semua transaksi --}}
  <div class="av-card av-rise" style="--i:7">
    <h3 class="av-title">Semua Transaksi</h3>

    <form class="av-filter" method="GET">
      <input name="cari" value="{{ request('cari') }}" class="av-input" placeholder="Cari keterangan...">
      <select name="jenis" class="av-input">
        <option value="">Semua jenis</option>
        @foreach (\App\Models\Transaction::LABELS as $k => $l)
          <option value="{{ $k }}" @selected(request('jenis') == $k)>{{ $l }}</option>
        @endforeach
      </select>
      <input type="date" name="dari" value="{{ request('dari') }}" class="av-input" aria-label="Dari tanggal">
      <input type="date" name="sampai" value="{{ request('sampai') }}" class="av-input" aria-label="Sampai tanggal">
      <div class="av-actions">
        <button class="av-btn av-btn--lime">Filter</button>
        <a href="{{ route('dashboard') }}" class="av-btn av-btn--gray">Reset</a>
      </div>
    </form>

    <div class="av-table-wrap">
      <table class="av-table">
        <thead>
          <tr><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th>Kategori</th><th class="r">Jumlah</th></tr>
        </thead>
        <tbody>
          @forelse ($transactions as $t)
            <tr>
              <td class="muted">{{ $t->date->format('d M Y') }}</td>
              <td><span class="av-badge av-badge--{{ $t->type }}">{{ ucfirst($t->type) }}</span></td>
              <td>{{ $t->description }}</td>
              <td class="muted">{{ $t->category ?: '-' }}</td>
              <td class="r av-amt"><span class="av-sign">{{ $t->type == 'pemasukan' ? '+' : '−' }}</span><span class="av-cur">Rp</span>{!! $num($t->amount) !!}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="av-none">Belum ada transaksi. <a href="{{ route('import.index') }}">Import CSV</a> atau tambah manual.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($transactions->hasPages())
      <div class="av-pager">
        <a class="av-btn av-btn--gray {{ $transactions->onFirstPage() ? 'is-off' : '' }}" href="{{ $transactions->previousPageUrl() ?? '#' }}">← Sebelumnya</a>
        <span class="av-mono">Halaman {{ $transactions->currentPage() }}@if (method_exists($transactions, 'lastPage')) dari {{ $transactions->lastPage() }}@endif</span>
        <a class="av-btn av-btn--gray {{ $transactions->hasMorePages() ? '' : 'is-off' }}" href="{{ $transactions->nextPageUrl() ?? '#' }}">Berikutnya →</a>
      </div>
    @endif
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const c = @json($chart);
Chart.defaults.font.family = "'Aspekta','Inter',system-ui,sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#85898b';

const rupiah = v => v >= 1e6 ? 'Rp ' + (v / 1e6).toLocaleString('id-ID') + ' jt' : 'Rp ' + v.toLocaleString('id-ID');

new Chart(document.getElementById('cf'), {
  type: 'bar',
  data: {
    labels: c.labels,
    datasets: [
      { label: 'Pemasukan',   data: c.pemasukan,   backgroundColor: '#cdfe00', borderColor: '#0a1217', borderWidth: 1, borderRadius: 6 },
      { label: 'Pengeluaran', data: c.pengeluaran, backgroundColor: '#0a1217', borderRadius: 6 },
      { label: 'Tabungan',    data: c.tabungan,    backgroundColor: '#dceaf8', borderColor: '#24476b', borderWidth: 1, borderRadius: 6 }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 900, easing: 'easeOutQuart', delay: ctx => ctx.dataIndex * 60 + 500 },
    plugins: {
      legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10, padding: 18, color: '#0a1217' } },
      tooltip: {
        backgroundColor: '#0a1217', padding: 12, cornerRadius: 8, boxPadding: 4,
        callbacks: { label: ctx => ' ' + ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') }
      }
    },
    scales: {
      x: { grid: { display: false }, border: { color: '#e6e7e8' } },
      y: { grid: { color: '#e6e7e8' }, border: { display: false }, ticks: { callback: rupiah } }
    }
  }
});
</script>
@endpush