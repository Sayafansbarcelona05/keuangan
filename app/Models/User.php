<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed']; }

    public function transactions() { return $this->hasMany(Transaction::class); }

    /** Saldo = pemasukan - pengeluaran - tabungan */
    public function saldo(): float
    {
        $t = $this->transactions()->selectRaw('type, SUM(amount) total')->groupBy('type')->pluck('total', 'type');
        return (float) ($t['pemasukan'] ?? 0) - (float) ($t['pengeluaran'] ?? 0) - (float) ($t['tabungan'] ?? 0);
    }
}
