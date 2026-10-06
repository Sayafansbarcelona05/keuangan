<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'type', 'date', 'description', 'category', 'amount'];
    protected $casts = ['date' => 'date', 'amount' => 'float'];

    public const LABELS = ['pemasukan' => 'Pemasukan', 'pengeluaran' => 'Pengeluaran', 'tabungan' => 'Tabungan'];

    public function user() { return $this->belongsTo(User::class); }
}
