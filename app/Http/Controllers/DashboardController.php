<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Mi Wallet: saldo, resumen de movimientos, gráfico de evolución y
 * comprobación de que el historial cuadra con el saldo.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $wallet = $request->user()->wallet;
        $scale = WalletService::SCALE;

        // Totales por tipo. PostgreSQL devuelve NUMERIC como string; bcadd con
        // '0' lo normaliza a 4 decimales (y convierte null en "0.0000").
        $credits = bcadd((string) $wallet->transactions()->where('type', WalletTransaction::TYPE_CREDIT)->sum('amount'), '0', $scale);
        $debits = bcadd((string) $wallet->transactions()->where('type', WalletTransaction::TYPE_DEBIT)->sum('amount'), '0', $scale);

        // Cuadre: créditos - débitos debe ser igual al saldo. Si alguien cambia
        // wallets.balance a mano (sin WalletService), aquí se nota.
        $expected = bcsub($credits, $debits, $scale);
        $balanced = bccomp($expected, $wallet->balance, $scale) === 0;

        $transactions = $wallet->transactions()->latest('id')->limit(20)->get();

        return view('dashboard', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'credits' => $credits,
            'debits' => $debits,
            'count' => $wallet->transactions()->count(),
            'expected' => $expected,
            'balanced' => $balanced,
            'sparkline' => $this->sparkline($transactions->reverse()->pluck('balance_after')->all()),
        ]);
    }

    /**
     * Convierte una lista de saldos en los puntos de un <polyline> SVG de
     * 300x60. Devuelve null si hay menos de 2 puntos (no hay línea que dibujar).
     *
     * Aquí sí se usa float: solo sirve para dibujar, no para calcular dinero.
     *
     * @param  array<int, string>  $balances
     */
    private function sparkline(array $balances): ?string
    {
        if (count($balances) < 2) {
            return null;
        }

        $values = array_map('floatval', $balances);
        $min = min($values);
        $range = (max($values) - $min) ?: 1;
        $step = 300 / (count($values) - 1);

        $points = [];
        foreach ($values as $i => $value) {
            $x = round($i * $step, 1);
            $y = round(56 - (($value - $min) / $range) * 52, 1); // margen de 4px arriba y abajo
            $points[] = "{$x},{$y}";
        }

        return implode(' ', $points);
    }
}
