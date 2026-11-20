{{--
    Lobby del casino (página de inicio, pública).
    Estructura: buscador -> barra de categorías -> banners -> cuadrícula de juegos.
    Por ahora solo "El Cruce de la Gallina" (Crash) está planificado; el resto
    son fichas "Próximamente" para dar forma al lobby.
--}}
@extends('layouts.app')

@section('title', 'Casino · BetPulse')

@php
    // Catálogo provisional del lobby. Cuando existan juegos reales, vendrá de
    // la base de datos o de config/.
    $categories = [
        ['ico' => '🏠', 'name' => 'Inicio', 'active' => true],
        ['ico' => '✨', 'name' => 'Novedades'],
        ['ico' => '🚀', 'name' => 'Crash'],
        ['ico' => '🎡', 'name' => 'Ruleta'],
        ['ico' => '🃏', 'name' => 'Cartas'],
        ['ico' => '🕹️', 'name' => 'Arcade'],
        ['ico' => '💎', 'name' => 'Jackpots'],
        ['ico' => '🎲', 'name' => 'Todos'],
    ];

    $games = [
        ['name' => 'El Cruce de la Gallina', 'emoji' => '🐔', 'bg' => 'g1', 'tag' => 'Pronto'],
        ['name' => 'Pulse Rocket', 'emoji' => '🚀', 'bg' => 'g4', 'tag' => 'Próximamente'],
        ['name' => 'Ruleta Zafiro', 'emoji' => '🎡', 'bg' => 'g2', 'tag' => 'Próximamente'],
        ['name' => 'Blackjack Perla', 'emoji' => '🃏', 'bg' => 'g3', 'tag' => 'Próximamente'],
        ['name' => 'Dados Brasa', 'emoji' => '🎲', 'bg' => 'g5', 'tag' => 'Próximamente'],
        ['name' => 'Minas', 'emoji' => '💣', 'bg' => 'g6', 'tag' => 'Próximamente'],
    ];
@endphp

@section('content')
    {{-- Buscador: filtra las fichas del lobby en el navegador (JS mínimo abajo) --}}
    <div class="search">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
        <input id="game-search" type="search" placeholder="Buscar juego" aria-label="Buscar juego">
    </div>

    <nav class="categories" aria-label="Categorías">
        @foreach ($categories as $cat)
            <a href="#juegos" class="category {{ ($cat['active'] ?? false) ? 'active' : '' }}">
                <span class="ico">{{ $cat['ico'] }}</span>
                {{ $cat['name'] }}
            </a>
        @endforeach
    </nav>

    <section class="banners">
        <div class="banner banner-crash">
            <div class="kicker">Originales BetPulse</div>
            <h2>El Cruce de la Gallina</h2>
            <p style="margin:0 0 14px;max-width:60%">El multiplicador sube… ¿te retiras a tiempo?</p>
            <span class="btn btn-solid">Muy pronto</span>
            <span class="art" aria-hidden="true">🐔</span>
        </div>

        <div class="banner banner-credits">
            <div class="kicker">Créditos recreativos</div>
            @auth
                <h2>Tu saldo: {{ auth()->user()->wallet?->balance }}</h2>
                <a href="{{ route('dashboard') }}" class="btn btn-accent">Ver movimientos</a>
            @else
                <h2>Empieza con 1000 créditos</h2>
                <a href="{{ route('register') }}" class="btn btn-solid">Crear cuenta</a>
            @endauth
            <span class="art" aria-hidden="true">🪙</span>
        </div>
    </section>

    <div class="section-head" id="juegos">
        <h3>Juegos de BetPulse</h3>
    </div>

    <div class="games" id="games">
        @foreach ($games as $game)
            <div class="game {{ $game['bg'] }} is-soon" data-name="{{ mb_strtolower($game['name']) }}">
                <span class="tag {{ $game['tag'] === 'Pronto' ? '' : 'soon' }}">{{ $game['tag'] }}</span>
                <span class="emoji" aria-hidden="true">{{ $game['emoji'] }}</span>
                <span class="name">{{ $game['name'] }}</span>
            </div>
        @endforeach
    </div>
    <p class="empty-search" id="empty-search">No hay juegos con ese nombre.</p>

    <script>
        // Filtro simple por nombre (sin librerías)
        document.getElementById('game-search').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let visibles = 0;
            document.querySelectorAll('#games .game').forEach(function (el) {
                const show = el.dataset.name.includes(q);
                el.style.display = show ? '' : 'none';
                if (show) visibles++;
            });
            document.getElementById('empty-search').style.display = visibles ? 'none' : 'block';
        });
    </script>
@endsection
