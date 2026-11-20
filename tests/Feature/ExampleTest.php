<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * El lobby del casino es público.
     */
    public function test_casino_lobby_is_public(): void
    {
        $this->get('/')->assertOk()->assertSee('El Cruce de la Gallina');
    }

    /**
     * Mi Wallet exige sesión.
     */
    public function test_dashboard_requires_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
