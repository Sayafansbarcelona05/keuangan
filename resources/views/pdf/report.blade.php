<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Keuangan — DompetKu</title>
<style>
  /* CSS aman untuk dompdf: tanpa flex/grid/variable */
  @page { margin: 30px 34px 56px 34px; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; font-weight: normal; color: #0a1217; line-height: 1.45; }
  table { border-collapse: collapse; }
  .mono { font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5px; color: #85898b; letter-spacing: .4px; }
  .r { text-align: right; }
  .w100 { width: 100%; }

  /* ---- header ---- */
  .hero { background: #0a1217; color: #ffffff; border-radius: 18px; padding: 20px 24px; }
  .mark { width: 11px; height: 11px; background: #cdfe00; border-radius: 3px; }
  .brand { font-size: 12px; font-weight: bold; color: #ffffff; padding-left: 7px; }
  .hero h1 { margin: 14px 0 0; font-size: 24px; line-height: 1.1; color: #ffffff; letter-spacing: -.5px; }
  .hero .mono { color: #8b9499; }
  .hero .pill { background: #cdfe00; color: #0a1217; font-size: 7.5px; font-weight: bold; padding: 3px 9px; border-radius: 10px; letter-spacing: .6px; }

  /* ---- meta ---- */
  .meta { margin-top: 12px; }
  .meta td { padding: 0 14px 0 0; vertical-align: top; }
  .meta .v { font-size: 9.5px; font-weight: bold; margin-top: 2px; }

  /* ---- kartu ringkasan ---- */
  .cards { border-collapse: separate; border-spacing: 8px 0; margin: 14px -8px 0; width: 103%; }
  .cards td { width: 25%; vertical-align: top; }
  .card { border-radius: 14px; padding: 13px 13px 12px; }
  .card .top { font-family: 'DejaVu Sans Mono', monospace; font-size: 7px; letter-spacing: .4px; }
  .card .nm { margin-top: 14px; font-size: 8.5px; }
  .card .amt { margin-top: 5px; font-size: 14px; font-weight: bold; letter-spacing: -.3px; white-space: nowrap; }
  .card .cur { font-size: 7px; font-weight: bold; padding: 1px 5px; border-radius: 8px; margin-right: 4px; letter-spacing: .4px; }
  .track { margin-top: 10px; height: 4px; border-radius: 4px; }
  .fill { height: 4px; border-radius: 4px; }
  .card .cap { margin-top: 6px; font-family: 'DejaVu Sans Mono', monospace; font-size: 7px; }

  /* ---- judul bagian ---- */
  .sec { margin: 22px 0 8px; }
  .sec h2 { margin: 0; font-size: 14px; letter-spacing: -.2px; }

  /* ---- tabel transaksi ---- */
  .t { width: 100%; border: 1px solid #e6e7e8; border-radius: 12px; }
  .t th { background: #0a1217; color: #ffffff; padding: 8px 8px; text-align: left; font-size: 7.5px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
  .t th.r { text-align: right; }
  .t td { padding: 7px 8px; border-bottom: 1px solid #e6e7e8; vertical-align: middle; word-wrap: break-word; }
  .t tr { page-break-inside: avoid; }
  .t thead { display: table-header-group; }
  .t .d { font-family: 'DejaVu Sans Mono', monospace; font-size: 8px; color: #54595d; white-space: nowrap; }
  .t .no { color: #85898b; font-size: 8px; }
  .t .amt { text-align: right; font-weight: bold; white-space: nowrap; }
  .t .sg { color: #85898b; }
  .t .cr { font-size: 7px; color: #85898b; margin-right: 2px; }
  .t .cat { color: #54595d; }
  .bd { font-size: 7.5px; padding: 2px 8px; border-radius: 9px; color: #ffffff; background: #3e4a50; white-space: nowrap; }
  .bd-pemasukan { background: #cdfe00; color: #0a1217; }
  .bd-pengeluaran { background: #0a1217; color: #ffffff; }
  .bd-tabungan { background: #24476b; color: #ffffff; }
  .none { text-align: center; padding: 26px 8px !important; color: #54595d; }

  /* ---- footer (diulang di tiap halaman) ---- */
  .foot { position: fixed; left: 0; right: 0; bottom: -36px; border-top: 1px solid #e6e7e8; padding-top: 6px; }
</style>
</head>
<body>
@php
  $rp   = fn($n) => 'Rp '.number_format($n, 0, ',', '.');
  // angka rapi: pemisah ribuan diredupkan
  $num  = function ($n, $dot = '#9aa0a3') {
    $t = str_replace('.', '<span style="color:'.$dot.'">.</span>', number_format(abs($n), 0, ',', '.'));
    return ($n < 0 ? '−' : '').$t;
  };
  $in    = (float) $sum('pemasukan');
  $out   = (float) $sum('pengeluaran');
  $sv    = (float) $sum('tabungan');
  $saldo = $in - $out - $sv;
  $pct   = fn($v) => $in > 0 ? max(0, min(100, (int) round($v / $in * 100))) : null;

  // [kode, nama, nilai, latar, teks, titik, track, fill, pill, caption]
  $cards = [
    ['01 / SALDO',       'Saldo Tersedia',    $saldo, '#0a1217', '#ffffff', '#6b7378', '#2b353b', '#cdfe00', '#3a444a', $pct($saldo)],
    ['02 / PEMASUKAN',   'Total Pemasukan',   $in,    '#ddefe2', '#0a1217', '#8fa396', '#c2d8c8', '#0a1217', '#c2d8c8', $in > 0 ? 100 : null],
    ['03 / PENGELUARAN', 'Total Pengeluaran', $out,   '#f4e3cd', '#0a1217', '#a89a86', '#e0cdb2', '#0a1217', '#e0cdb2', $pct($out)],
    ['04 / TABUNGAN',    'Total Tabungan',    $sv,    '#dceaf8', '#0a1217', '#8aa0b5', '#bfd2e6', '#0a1217', '#bfd2e6', $pct($sv)],
  ];
  $periode = ($dari ? \Carbon\Carbon::parse($dari)->format('d M Y') : 'Awal').' – '.($sampai ? \Carbon\Carbon::parse($sampai)->format('d M Y') : 'Sekarang');
@endphp

{{-- Footer tiap halaman --}}
<div class="foot">
  <table class="w100"><tr>
    <td class="mono">DOMPETKU · LAPORAN KEUANGAN</td>
    <td class="mono r">{{ strtoupper($user->name) }}</td>
  </tr></table>
</div>

{{-- Header --}}
<div class="hero">
  <table class="w100">
    <tr>
      <td>
        <table><tr>
          <td class="mark">&nbsp;</td>
          <td class="brand">DompetKu</td>
        </tr></table>
      </td>
      <td class="r"><span class="pill">LAPORAN</span></td>
    </tr>
  </table>
  <h1>Laporan Keuangan</h1>
  <div class="mono" style="margin-top:6px">{{ strtoupper($periode) }}</div>
</div>

{{-- Meta --}}
<table class="meta">
  <tr>
    <td><div class="mono">PENGGUNA</div><div class="v">{{ $user->name }}</div></td>
    <td><div class="mono">PERIODE</div><div class="v">{{ $periode }}</div></td>
    <td><div class="mono">JENIS</div><div class="v">{{ $jenis ? ucfirst($jenis) : 'Semua jenis' }}</div></td>
    <td><div class="mono">DICETAK</div><div class="v">{{ now()->format('d M Y, H:i') }}</div></td>
  </tr>
</table>

{{-- Ringkasan --}}
<table class="cards">
  <tr>
    @foreach ($cards as $i => $c)
      <td>
        <div class="card" style="background:{{ $c[3] }};color:{{ $c[4] }}">
          <div class="top" style="color:{{ $c[4] }};opacity:.6">{{ $c[0] }}</div>
          <div class="nm">{{ $c[1] }}</div>
          <div class="amt"><span class="cur" style="background:{{ $c[8] }}">Rp</span>{!! $num($c[2], $c[5]) !!}</div>
          <div class="track" style="background:{{ $c[6] }}"><div class="fill" style="background:{{ $c[7] }};width:{{ $c[9] ?? 0 }}%"></div></div>
          <div class="cap">
            @if ($i === 1) Basis perbandingan
            @elseif (!is_null($c[9])) {{ $c[9] }}% dari pemasukan
            @else —
            @endif
          </div>
        </div>
      </td>
    @endforeach
  </tr>
</table>

{{-- Transaksi --}}
<div class="sec">
  <table class="w100"><tr>
    <td><h2>Daftar Transaksi</h2></td>
    <td class="r mono">{{ count($items) }} DATA</td>
  </tr></table>
</div>

<table class="t">
  <thead>
    <tr>
      <th style="width:5%">No</th>
      <th style="width:13%">Tanggal</th>
      <th style="width:14%">Jenis</th>
      <th style="width:29%">Keterangan</th>
      <th style="width:16%">Kategori</th>
      <th class="r" style="width:23%">Jumlah</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $i => $t)
      <tr>
        <td class="no">{{ $i + 1 }}</td>
        <td class="d">{{ $t->date->format('d-m-Y') }}</td>
        <td><span class="bd bd-{{ $t->type }}">{{ ucfirst($t->type) }}</span></td>
        <td>{{ $t->description }}</td>
        <td class="cat">{{ $t->category ?: '-' }}</td>
        <td class="amt"><span class="sg">{{ $t->type == 'pemasukan' ? '+' : '−' }}</span> <span class="cr">Rp</span>{!! $num($t->amount) !!}</td>
      </tr>
    @empty
      <tr><td colspan="6" class="none">Tidak ada transaksi pada periode ini.</td></tr>
    @endforelse
  </tbody>
</table>

</body>
</html>