<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un movimiento del libro mayor de una wallet.
 *
 * Solo lo crea App\Services\WalletService. Los montos son strings con 4
 * decimales (cast 'decimal:4') para operar con bcmath.
 *
 * @property int $id
 * @property int $wallet_id
 * @property string $type 'credit' | 'debit'
 * @property string $amount siempre positivo
 * @property string $balance_before
 * @property string $balance_after
 * @property string|null $description
 */
#[Fillable(['wallet_id', 'type', 'amount', 'balance_before', 'balance_after', 'description'])]
class WalletTransaction extends Model
{
    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'balance_before' => 'decimal:4',
            'balance_after' => 'decimal:4',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
