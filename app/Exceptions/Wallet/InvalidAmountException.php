<?php

namespace App\Exceptions\Wallet;

/**
 * El monto no es válido: no es un número, es <= 0, tiene más de 4 decimales
 * o haría superar el saldo máximo que cabe en decimal(15, 4).
 */
class InvalidAmountException extends WalletException {}
