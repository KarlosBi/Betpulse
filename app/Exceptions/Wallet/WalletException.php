<?php

namespace App\Exceptions\Wallet;

use RuntimeException;

/**
 * Excepción base de la wallet.
 *
 * Es una excepción "controlada": representa un error de negocio esperado
 * (monto inválido, saldo insuficiente), no un fallo del sistema. Se renderiza
 * como 422 / mensaje de validación en bootstrap/app.php.
 */
abstract class WalletException extends RuntimeException {}
