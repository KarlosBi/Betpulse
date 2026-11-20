<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla wallets: una cartera virtual (saldo recreativo) por usuario.
 *
 * - balance es decimal(15, 4): PostgreSQL guarda el valor exacto (tipo NUMERIC),
 *   sin los errores de redondeo de float. En PHP se lee como string y se opera
 *   con bcmath (ver App\Services\WalletService).
 * - user_id es único: cada usuario tiene exactamente una wallet.
 * - El CHECK (balance >= 0) es una segunda red de seguridad: aunque un bug en el
 *   código intentara dejar el saldo negativo, PostgreSQL rechazaría el UPDATE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 4)->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE wallets ADD CONSTRAINT wallets_balance_non_negative CHECK (balance >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
