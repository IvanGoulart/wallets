<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users');
            $table->foreignId('receiver_id')->nullable()->constrained('users');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['deposit', 'transfer', 'transfer_in', 'transfer_out', 'reversal']);
            $table->foreignId('reversed_transaction_id')->nullable()->constrained('transactions'); // Para ligar reversões
            $table->enum('status', ['pending', 'completed', 'reversed', 'failed'])->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
