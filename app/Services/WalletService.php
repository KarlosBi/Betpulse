<?php

namespace App\Services;

use App\Exceptions\Wallet\InsufficientFundsException;
use App\Exceptions\Wallet\InvalidAmountException;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Único punto de entrada para mover saldo en una wallet.
 *
 * Garantías:
 *  1. Atomicidad: el cambio de saldo y su registro en wallet_transactions se
 *     guardan juntos dentro de DB::transaction(). Si algo falla, no se guarda nada.
 *  2. Concurrencia: lockForUpdate() (SELECT ... FOR UPDATE) bloquea la fila de
 *     la wallet. Si llegan dos apuestas a la vez, la segunda espera a que la
 *     primera termine y lee el saldo ya actualizado. Así nunca se gasta dos
 *     veces el mismo dinero.
 *  3. Precisión: los montos son strings y se operan con bcmath a 4 decimales
 *     (igual que decimal(15, 4) en PostgreSQL). Nunca se usan floats, porque
 *     0.1 + 0.2 en float da 0.30000000000000004.
 *
 * Uso:
 *   $service->credit($wallet, '50.25', 'Premio Crash');
 *   $service->debit($wallet, '10', 'Apuesta Crash');
 */
class WalletService
{
    /** Decimales con los que se opera (coincide con la columna decimal(15, 4)). */
    public const SCALE = 4;

    /** Mayor valor que cabe en decimal(15, 4): 11 dígitos enteros + 4 decimales. */
    public const MAX_BALANCE = '99999999999.9999';

    /** Saldo recreativo con el que empieza cada usuario nuevo. */
    public const INITIAL_BALANCE = '100.0000';

    /**
     * Suma saldo a la wallet.
     *
     * @throws InvalidAmountException
     */
    public function credit(Wallet $wallet, string|int|float $amount, ?string $description = null): WalletTransaction
    {
        return $this->apply($wallet, WalletTransaction::TYPE_CREDIT, $amount, $description);
    }

    /**
     * Resta saldo de la wallet.
     *
     * @throws InvalidAmountException
     * @throws InsufficientFundsException
     */
    public function debit(Wallet $wallet, string|int|float $amount, ?string $description = null): WalletTransaction
    {
        return $this->apply($wallet, WalletTransaction::TYPE_DEBIT, $amount, $description);
    }

    /**
     * Valida un monto y lo devuelve normalizado a 4 decimales ("10" -> "10.0000").
     *
     * Los montos deben llegar como string (por ejemplo, desde un formulario) o
     * entero. Los float se rechazan: pueden traer error de redondeo
     * (0.1 + 0.2 = 0.30000000000000004). `float` está en la firma solo para
     * poder rechazarlo aquí; sin él, PHP convertiría 10.5 en el entero 10 sin
     * avisar y se acreditaría un monto distinto al pedido.
     *
     * Se rechaza (no se redondea) un monto con más de 4 decimales: si alguien
     * apuesta 0.00005, es mejor avisar que inventar un redondeo.
     *
     * @throws InvalidAmountException
     */
    public function normalizeAmount(string|int|float $amount): string
    {
        if (is_float($amount)) {
            throw new InvalidAmountException('El monto debe enviarse como string o entero, no como float.');
        }

        $value = trim((string) $amount);

        if (! preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            throw new InvalidAmountException("El monto '{$value}' no es un número válido.");
        }

        $decimals = str_contains($value, '.') ? strlen(substr($value, strpos($value, '.') + 1)) : 0;
        if ($decimals > self::SCALE) {
            throw new InvalidAmountException('El monto admite como máximo '.self::SCALE.' decimales.');
        }

        if (bccomp($value, '0', self::SCALE) <= 0) {
            throw new InvalidAmountException('El monto debe ser mayor que 0.');
        }

        if (bccomp($value, self::MAX_BALANCE, self::SCALE) > 0) {
            throw new InvalidAmountException('El monto supera el máximo permitido.');
        }

        // bcadd con 0 solo sirve para dejarlo con exactamente 4 decimales
        return bcadd($value, '0', self::SCALE);
    }

    /**
     * Lógica común de crédito y débito.
     */
    private function apply(Wallet $wallet, string $type, string|int|float $amount, ?string $description): WalletTransaction
    {
        // Se valida antes de abrir la transacción: si el monto es inválido,
        // no hace falta tocar la base de datos.
        $amount = $this->normalizeAmount($amount);

        $transaction = DB::transaction(function () use ($wallet, $type, $amount, $description) {
            // Se vuelve a leer la wallet CON bloqueo. No nos fiamos del saldo
            // que traiga $wallet en memoria: puede estar desactualizado.
            $locked = Wallet::whereKey($wallet->getKey())->lockForUpdate()->firstOrFail();

            $before = $locked->balance;

            if ($type === WalletTransaction::TYPE_DEBIT) {
                if (bccomp($before, $amount, self::SCALE) < 0) {
                    throw new InsufficientFundsException($before, $amount);
                }
                $after = bcsub($before, $amount, self::SCALE);
            } else {
                $after = bcadd($before, $amount, self::SCALE);
                if (bccomp($after, self::MAX_BALANCE, self::SCALE) > 0) {
                    throw new InvalidAmountException('El crédito haría superar el saldo máximo permitido.');
                }
            }

            $locked->balance = $after;
            $locked->save();

            return $locked->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
            ]);
        });

        // Deja el modelo que nos pasaron con el saldo nuevo, sin otra consulta
        $wallet->balance = $transaction->balance_after;
        $wallet->syncOriginalAttribute('balance');

        return $transaction;
    }
}
