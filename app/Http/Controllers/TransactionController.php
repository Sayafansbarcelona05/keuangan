<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private function own(Request $r, Transaction $t, string $type): void
    {
        abort_unless($t->user_id === $r->user()->id && $t->type === $type, 404);
    }

    public function index(Request $r, string $type)
    {
        $q = $r->user()->transactions()->where('type', $type);
        if ($r->filled('cari')) $q->where('description', 'like', '%'.$r->cari.'%');
        if ($r->filled('bulan')) $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$r->bulan]);
        $total = (clone $q)->sum('amount');
        $items = $q->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();
        return view('transactions.index', [
            'type' => $type, 'label' => Transaction::LABELS[$type],
            'items' => $items, 'total' => $total, 'saldo' => $r->user()->saldo(),
        ]);
    }

    public function create(Request $r, string $type)
    {
        return view('transactions.form', ['type' => $type, 'label' => Transaction::LABELS[$type], 'item' => new Transaction(['date' => now()]), 'saldo' => $r->user()->saldo()]);
    }

    public function store(Request $r, string $type)
    {
        $data = $this->validated($r, $type, 0);
        $r->user()->transactions()->create($data + ['type' => $type]);
        return redirect()->route('trx.index', $type)->with('success', Transaction::LABELS[$type].' berhasil ditambahkan.');
    }

    public function edit(Request $r, string $type, Transaction $transaction)
    {
        $this->own($r, $transaction, $type);
        return view('transactions.form', ['type' => $type, 'label' => Transaction::LABELS[$type], 'item' => $transaction, 'saldo' => $r->user()->saldo()]);
    }

    public function update(Request $r, string $type, Transaction $transaction)
    {
        $this->own($r, $transaction, $type);
        $data = $this->validated($r, $type, $transaction->amount, $transaction);
        $transaction->update($data);
        return redirect()->route('trx.index', $type)->with('success', Transaction::LABELS[$type].' berhasil diperbarui.');
    }

    public function destroy(Request $r, string $type, Transaction $transaction)
    {
        $this->own($r, $transaction, $type);
        if ($type === 'pemasukan' && $r->user()->saldo() - $transaction->amount < 0) {
            return back()->with('error', 'Tidak bisa dihapus: saldo akan menjadi minus karena pemasukan ini sudah terpakai.');
        }
        $transaction->delete();
        return back()->with('success', 'Data berhasil dihapus.');
    }

    private function validated(Request $r, string $type, float $oldAmount, ?Transaction $existing = null): array
    {
        $data = $r->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:1',
        ], [], ['date' => 'tanggal', 'description' => 'keterangan', 'category' => 'kategori', 'amount' => 'jumlah']);

        $saldo = $r->user()->saldo();
        if ($type === 'pemasukan') {
            // Mengurangi pemasukan tidak boleh membuat saldo minus
            if ($saldo - $oldAmount + $data['amount'] < 0) {
                back()->withInput()->withErrors(['amount' => 'Jumlah terlalu kecil, saldo akan menjadi minus.'])->throwResponse();
            }
        } else {
            $available = $saldo + $oldAmount;
            if ($data['amount'] > $available) {
                back()->withInput()->withErrors(['amount' => 'Saldo tidak cukup. Saldo tersedia: Rp '.number_format($available, 0, ',', '.')])->throwResponse();
            }
        }
        return $data;
    }
}
