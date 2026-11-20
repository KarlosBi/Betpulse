@extends('layouts.app')

@section('title', 'Iniciar sesión · BetPulse')

@section('content')
    <div class="auth-wrap">
        <div class="card auth-card">
            <h2>Iniciar sesión</h2>
            <p class="sub">Entra para ver tu saldo y jugar.</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">

                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">

                <label class="check"><input type="checkbox" name="remember"> Recordarme</label>

                <button type="submit" class="btn btn-solid btn-block" style="margin-top:18px">Entrar</button>
            </form>

            <p class="alt">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
        </div>
    </div>
@endsection
