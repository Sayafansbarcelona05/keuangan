<?php

namespace App\Services;

use App\Models\User;

/**
 * Menganalisis pola keuangan dan memberi saran hemat.
 */
class FinanceAdvisor
{
    /** Kelompok barang berdasarkan kata kunci keterangan */
    private const GROUPS = [
        'Rokok' => ['rokok', 'vape', 'cerutu'],
        'Makanan Instan & Jajan' => ['indomie', 'mie', 'nugget', 'nuget', 'baso', 'bakso', 'gorengan', 'snack', 'jajan', 'saos', 'sosis', 'kopi'],
        'Kebutuhan Dapur' => ['telor', 'telur', 'minyak', 'mentega', 'beras', 'gas', 'gula', 'sayur'],
        'Air Minum' => ['galon', 'air minum', 'aqua'],
        'Kebersihan & Perawatan' => ['sabun', 'shampoo', 'odol', 'pel', 'plastik', 'obat nyamuk', 'detergen'],
    ];

    private const TIPS = [
        'Rokok' => 'Pengeluaran rokok cukup besar. Coba kurangi bertahap (misal 1 bungkus lebih sedikit per minggu) — hasilnya bisa langsung dipindah ke tabungan.',
        'Makanan Instan & Jajan' => 'Jajan & makanan instan sering dibeli dalam jumlah kecil tapi berulang. Belanja sekali seminggu dengan daftar belanja agar lebih terkontrol.',
        'Kebutuhan Dapur' => 'Beli kebutuhan dapur (telur, minyak) dalam kemasan lebih besar biasanya lebih murah per satuannya.',
        'Air Minum' => 'Pembelian air minum cukup sering. Pertimbangkan isi ulang galon atau langganan agar harga per galon lebih murah.',
        'Kebersihan & Perawatan' => 'Sabun & perlengkapan kebersihan bisa dibeli ukuran refill/jumbo yang jauh lebih hemat.',
        'Lainnya' => 'Cek kembali pengeluaran lain-lain, pastikan semuanya memang kebutuhan, bukan keinginan.',
    ];

    public function groupOf(string $desc): string
    {
        $d = strtolower($desc);
        foreach (self::GROUPS as $g => $words) foreach ($words as $w) if (str_contains($d, $w)) return $g;
        return 'Lainnya';
    }

    public function analyze(User $user): array
    {
        $all = $user->transactions()->get();
        $in = $all->where('type', 'pemasukan')->sum('amount');
        $out = $all->where('type', 'pengeluaran')->sum('amount');
        $save = $all->where('type', 'tabungan')->sum('amount');
        $tips = [];
        $groups = [];

        if ($in <= 0) {
            return ['tips' => [['level' => 'info', 'title' => 'Belum ada data', 'text' => 'Tambahkan pemasukan dan pengeluaran agar saran keuangan bisa dibuat.']], 'groups' => [], 'score' => null];
        }

        foreach ($all->where('type', 'pengeluaran') as $t) {
            $g = $this->groupOf($t->description);
            $groups[$g]['total'] = ($groups[$g]['total'] ?? 0) + $t->amount;
            $groups[$g]['count'] = ($groups[$g]['count'] ?? 0) + 1;
        }
        uasort($groups, fn ($a, $b) => $b['total'] <=> $a['total']);
        foreach ($groups as $g => &$v) $v['percent'] = $out > 0 ? round($v['total'] / $out * 100, 1) : 0;
        unset($v);

        $spendRatio = $out / $in * 100;
        $saveRatio = $save / $in * 100;

        if ($spendRatio >= 90) $tips[] = ['level' => 'danger', 'title' => 'Terlalu boros', 'text' => 'Kamu menghabiskan '.round($spendRatio).'% dari total pemasukan. Idealnya pengeluaran maksimal 70–80%.'];
        elseif ($spendRatio >= 75) $tips[] = ['level' => 'warning', 'title' => 'Pengeluaran cukup tinggi', 'text' => 'Pengeluaran '.round($spendRatio).'% dari pemasukan. Masih aman, tapi ada ruang untuk berhemat.'];
        else $tips[] = ['level' => 'success', 'title' => 'Pengeluaran terkendali', 'text' => 'Pengeluaran hanya '.round($spendRatio).'% dari pemasukan. Pertahankan!'];

        if ($saveRatio < 10) $tips[] = ['level' => 'warning', 'title' => 'Tabungan masih kecil', 'text' => 'Tabungan baru '.round($saveRatio, 1).'% dari pemasukan. Coba sisihkan minimal 10–20% setiap kali menerima uang (metode "bayar diri sendiri dulu").'];
        else $tips[] = ['level' => 'success', 'title' => 'Tabungan bagus', 'text' => 'Kamu menabung '.round($saveRatio, 1).'% dari pemasukan. Mantap!'];

        $i = 0;
        foreach ($groups as $g => $v) {
            if ($i++ >= 3) break;
            if ($v['percent'] < 10) continue;
            $tips[] = ['level' => $i === 1 ? 'danger' : 'warning', 'title' => "Boros di $g ({$v['percent']}%)",
                'text' => 'Total Rp '.number_format($v['total'], 0, ',', '.')." dari {$v['count']} transaksi. ".self::TIPS[$g]];
        }

        if (isset($groups['Rokok'])) {
            $potential = $groups['Rokok']['total'] * 0.5;
            $tips[] = ['level' => 'info', 'title' => 'Potensi hemat', 'text' => 'Jika pengeluaran rokok dikurangi setengahnya, kamu bisa menabung tambahan sekitar Rp '.number_format($potential, 0, ',', '.').'.'];
        }

        $small = $all->where('type', 'pengeluaran')->where('amount', '<', 20000)->count();
        $cnt = $all->where('type', 'pengeluaran')->count();
        if ($cnt > 0 && $small / $cnt > 0.4) $tips[] = ['level' => 'info', 'title' => 'Banyak belanja kecil', 'text' => round($small / $cnt * 100).'% transaksimu di bawah Rp 20.000. Belanja kecil yang sering ("latte factor") diam-diam menguras saldo.'];

        $score = max(0, min(100, round(100 - max(0, $spendRatio - 60) * 1.5 + min(20, $saveRatio))));

        return ['tips' => $tips, 'groups' => $groups, 'score' => $score];
    }
}
