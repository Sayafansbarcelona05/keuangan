<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index() { return view('import'); }

    /**
     * Format CSV: Tanggal,Jenis,Keterangan,Kategori,Jumlah (Rp)
     * Tanggal dd-mm-yyyy, Jenis = Pemasukan / Pengeluaran / Tabungan.
     */
    public function store(Request $r)
    {
        $r->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $user = $r->user();
        $handle = fopen($r->file('file')->getRealPath(), 'r');
        $first = fgets($handle);
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        rewind($handle);

        $rows = [];
        $errors = [];
        $line = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if ($line === 1) continue; // header
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) continue;
            if (count($row) < 5) { $errors[] = "Baris $line: kolom kurang."; continue; }

            [$tgl, $jenis, $ket, $kat, $jml] = array_map(fn ($v) => trim(str_replace("\xEF\xBB\xBF", '', (string) $v)), $row);
            $type = strtolower($jenis);
            if (!in_array($type, ['pemasukan', 'pengeluaran', 'tabungan'])) { $errors[] = "Baris $line: jenis '$jenis' tidak dikenal."; continue; }
            try {
                $date = Carbon::createFromFormat(str_contains($tgl, '/') ? 'd/m/Y' : 'd-m-Y', $tgl)->startOfDay();
            } catch (\Throwable $e) {
                try { $date = Carbon::parse($tgl); } catch (\Throwable $e) { $errors[] = "Baris $line: tanggal '$tgl' tidak valid."; continue; }
            }
            $amount = abs((float) preg_replace('/[^0-9.\-]/', '', $jml));
            if ($amount <= 0) { $errors[] = "Baris $line: jumlah tidak valid."; continue; }

            $rows[] = [
                'user_id' => $user->id, 'type' => $type, 'date' => $date->toDateString(),
                'description' => $ket ?: '-', 'category' => ($kat === '' || $kat === '-') ? null : $kat,
                'amount' => $amount, 'created_at' => now(), 'updated_at' => now(),
            ];
        }
        fclose($handle);

        if (!$rows) return back()->with('error', 'Tidak ada data valid untuk diimport.')->with('import_errors', $errors);

        DB::transaction(function () use ($r, $user, $rows) {
            if ($r->boolean('replace')) $user->transactions()->delete();
            foreach (array_chunk($rows, 500) as $chunk) DB::table('transactions')->insert($chunk);
        });

        return redirect()->route('dashboard')
            ->with('success', count($rows).' transaksi berhasil diimport.'.($errors ? ' '.count($errors).' baris dilewati.' : ''))
            ->with('import_errors', $errors);
    }
}
