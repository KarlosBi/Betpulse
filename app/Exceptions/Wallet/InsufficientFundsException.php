<?php

namespace App\Exceptions\Wallet;

/**
 * Se intentó debitar más de lo que hay en la wallet.
 */
class InsufficientFundsException extends WalletException
{
    public function __construct(
        public readonly string $balance,
        public readonly string $amount,
    ) {
        parent::__construct("Saldo insuficiente: disponible {$balance}, solicitado {$amount}.");
    }
}
