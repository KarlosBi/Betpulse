<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla wallet_transactions: libro mayor (ledger) de cada wallet.
 *
 * Cada movimiento guarda el saldo antes y después. Así se puede auditar que
 *   balance_after = balance_before ± amount
 * y que el último balance_after coincide con wallets.balance.
 *
 * - type: 'credit' (entra dinero) o 'debit' (sale dinero).
 * - amount siempre es positivo; el signo lo da el type.
 * - description: texto libre ("Saldo inicial", "Apuesta Crash #12", ...).
 * - Los registros no se editan ni se borran: un error se corrige con un
 *   movimiento nuevo en sentido contrario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10);
            $table->decimal('amount', 15, 4);
            $table->decimal('balance_before', 15, 4);
            $table->decimal('balance_after', 15, 4);
            $table->string('description')->nullable();
            $table->timestamps();

            // Consulta típica: historial de una wallet ordenado por fecha
            $table->index(['wallet_id', 'created_at']);
        });

        // Reglas también a nivel de base de datos
        DB::statement("ALTER TABLE wallet_transactions ADD CONSTRAINT wallet_transactions_type_valid CHECK (type IN ('credit', 'debit'))");
        DB::statement('ALTER TABLE wallet_transactions ADD CONSTRAINT wallet_transactions_amount_positive CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
