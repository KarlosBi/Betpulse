<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Autenticación del jugador con sesiones nativas de Laravel (sin OAuth).
 */
class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Crea el usuario y su wallet con el saldo inicial recreativo.
     *
     * Todo va en una sola transacción: si falla la creación de la wallet, el
     * usuario tampoco se crea (no quedan usuarios sin wallet).
     *
     * El saldo inicial no se escribe directamente en wallets.balance: se
     * acredita con WalletService para que quede registrado en el historial
     * ("Saldo inicial recreativo"). Así la suma de movimientos siempre cuadra
     * con el saldo.
     */
    public function register(Request $request, WalletService $wallets): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($data, $wallets) {
            $user = User::create($data); // la contraseña se hashea por el cast 'hashed'

            $wallet = $user->wallet()->create(['balance' => '0']);
            $wallets->credit($wallet, WalletService::INITIAL_BALANCE, 'Saldo inicial recreativo');

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Mensaje genérico: no revelamos si el email existe o no
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        // Nuevo id de sesión tras iniciar sesión (evita fijación de sesión)
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
