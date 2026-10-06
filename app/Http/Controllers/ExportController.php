<?php

namespace App\Http\Controllers;

use App\Services\FinanceAdvisor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index() { return view('export'); }

    private function query(Request $r)
    {
        $q = $r->user()->transactions();
        if ($r->filled('jenis')) $q->where('type', $r->jenis);
        if ($r->filled('dari')) $q->whereDate('date', '>=', $r->dari);
        if ($r->filled('sampai')) $q->whereDate('date', '<=', $r->sampai);
        return $q->orderBy('date')->orderBy('id')->get();
    }

    public function pdf(Request $r, FinanceAdvisor $advisor)
    {
        $items = $this->query($r);
        $pdf = Pdf::loadView('pdf.report', [
            'items' => $items,
            'user' => $r->user(),
            'dari' => $r->dari, 'sampai' => $r->sampai, 'jenis' => $r->jenis,
            'sum' => fn ($t) => $items->where('type', $t)->sum('amount'),
            'advice' => $advisor->analyze($r->user()),
        ])->setPaper('a4');
        return $pdf->download('laporan-keuangan_'.now()->format('Ymd_His').'.pdf');
    }

    public function csv(Request $r)
    {
        $items = $this->query($r);
        $name = 'catatan-keuangan_'.now()->format('Ymd_His').'.csv';
        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Jenis', 'Keterangan', 'Kategori', 'Jumlah (Rp)']);
            foreach ($items as $t) {
                $amount = $t->type === 'pemasukan' ? $t->amount : -$t->amount;
                fputcsv($out, [$t->date->format('d-m-Y'), ucfirst($t->type), $t->description, $t->category ?: '-', number_format($amount, 2, '.', '')]);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
