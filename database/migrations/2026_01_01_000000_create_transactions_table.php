<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pemasukan', 'pengeluaran', 'tabungan']);
            $table->date('date');
            $table->string('description');
            $table->string('category')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->index(['user_id', 'type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
