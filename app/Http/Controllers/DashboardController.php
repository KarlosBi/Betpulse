<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Panel del jugador: por ahora muestra saldo y últimos movimientos.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $wallet = $request->user()->wallet;

        return view('dashboard', [
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->latest('id')->limit(20)->get(),
        ]);
    }
}
