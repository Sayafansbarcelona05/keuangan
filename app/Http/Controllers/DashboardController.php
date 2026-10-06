<?php

namespace App\Http\Controllers;

use App\Services\FinanceAdvisor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r, FinanceAdvisor $advisor)
    {
        $user = $r->user();
        $q = $user->transactions();
        if ($r->filled('jenis')) $q->where('type', $r->jenis);
        if ($r->filled('dari')) $q->whereDate('date', '>=', $r->dari);
        if ($r->filled('sampai')) $q->whereDate('date', '<=', $r->sampai);
        if ($r->filled('cari')) $q->where('description', 'like', '%'.$r->cari.'%');
        $transactions = $q->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $totals = $user->transactions()->selectRaw('type, SUM(amount) total')->groupBy('type')->pluck('total', 'type');

        // Grafik 6 bulan terakhir
        $chart = ['labels' => [], 'pemasukan' => [], 'pengeluaran' => [], 'tabungan' => []];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $chart['labels'][] = $m->translatedFormat('M Y');
            foreach (['pemasukan', 'pengeluaran', 'tabungan'] as $t) {
                $chart[$t][] = (float) $user->transactions()->where('type', $t)
                    ->whereBetween('date', [$m->copy(), $m->copy()->endOfMonth()])->sum('amount');
            }
        }

        return view('dashboard', [
            'transactions' => $transactions,
            'pemasukan' => (float) ($totals['pemasukan'] ?? 0),
            'pengeluaran' => (float) ($totals['pengeluaran'] ?? 0),
            'tabungan' => (float) ($totals['tabungan'] ?? 0),
            'saldo' => $user->saldo(),
            'chart' => $chart,
            'advice' => $advisor->analyze($user),
        ]);
    }
}
