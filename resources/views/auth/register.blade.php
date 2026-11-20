@extends('layouts.app')

@section('title', 'Crear cuenta · BetPulse')

@section('content')
    <div class="auth-wrap">
        <div class="card auth-card">
            <h2>Crear cuenta</h2>
            <p class="sub">Empiezas con 1000.0000 créditos recreativos (no es dinero real).</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <label for="name">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">

                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">

                <label for="password">Contraseña (mínimo 8 caracteres)</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">

                <label for="password_confirmation">Repite la contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

                <button type="submit" class="btn btn-solid btn-block" style="margin-top:22px">Registrarme</button>
            </form>

            <p class="alt">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </div>
    </div>
@endsection
