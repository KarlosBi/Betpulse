<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Wallet (cartera virtual) de un usuario.
 *
 * IMPORTANTE: no modifiques `balance` directamente (ni con update() ni con
 * save()). Usa siempre App\Services\WalletService, que bloquea la fila, valida
 * el saldo y registra el movimiento en wallet_transactions.
 *
 * `balance` se castea a 'decimal:4', así que en PHP es un string ("1000.0000"),
 * nunca un float. Eso es lo que permite operar con bcmath sin perder precisión.
 *
 * @property int $id
 * @property int $user_id
 * @property string $balance
 */
#[Fillable(['user_id', 'balance'])]
class Wallet extends Model
{
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
