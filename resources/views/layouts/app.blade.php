{{--
    Layout principal de BetPulse: cabecera fija con logo, navegación, saldo y
    cuenta. Los estilos están en public/css/betpulse.css (CSS plano, sin Vite).
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BetPulse')</title>
    <link rel="stylesheet" href="{{ asset('css/betpulse.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="{{ route('home') }}" class="logo">Bet<span>Pulse</span></a>

            <nav class="mainnav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Casino</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Mi Wallet</a>
                @endauth
            </nav>

            <div class="account">
                @auth
                    {{-- Saldo siempre visible, como en cualquier casino --}}
                    <a href="{{ route('dashboard') }}" class="balance-chip" title="Ver movimientos">
                        <small>Saldo</small>
                        <strong>{{ auth()->user()->wallet?->balance ?? '0.0000' }}</strong>
                    </a>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn">Salir</button>
                    </form>
                @else
                    <a href="{{ route('register') }}" class="btn btn-accent">Registrarse</a>
                    <a href="{{ route('login') }}" class="btn">Entrar</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="container">
        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer container">
        BetPulse es un simulador recreativo: los créditos son virtuales y no tienen valor real.
    </footer>
</body>
</html>
