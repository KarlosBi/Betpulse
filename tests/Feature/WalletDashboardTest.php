<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Mi Wallet: muestra el saldo y avisa si el historial no cuadra con él.
 */
class WalletDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function userWithWallet(): User
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create(['balance' => '0']);
        app(WalletService::class)->credit($wallet, WalletService::INITIAL_BALANCE, 'Saldo inicial recreativo');

        return $user;
    }

    public function test_wallet_shows_balance_and_balanced_history(): void
    {
        $user = $this->userWithWallet();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee(WalletService::INITIAL_BALANCE)
            ->assertSee('Cuadra');
    }

    public function test_wallet_flags_balance_edited_outside_wallet_service(): void
    {
        $user = $this->userWithWallet();

        // Simula un cambio manual (p. ej. desde pgAdmin) que no deja movimiento
        DB::table('wallets')->where('user_id', $user->id)->update(['balance' => '300']);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Descuadre')
            ->assertSee('Según movimientos: '.WalletService::INITIAL_BALANCE);
    }
}
