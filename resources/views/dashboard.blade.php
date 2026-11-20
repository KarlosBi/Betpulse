{{--
    Mi Wallet
    1. Tarjeta principal: saldo + gráfico de evolución (últimos 20 movimientos)
    2. Cuatro indicadores: acreditado, debitado, nº de movimientos y cuadre
    3. Historial de movimientos
    Los montos son strings ("1000.0000"); no se pasan por number_format para
    no convertirlos a float.
--}}
@extends('layouts.app')

@section('title', 'Mi Wallet · BetPulse')

@section('content')
    <div class="section-head">
        <h3>Mi Wallet</h3>
        <span class="muted">{{ auth()->user()->email }}</span>
    </div>

    {{-- 1. Saldo --}}
    <section class="wallet-hero">
        <div class="wallet-hero-main">
            <div class="label">Saldo disponible</div>
            <div class="amount">{{ $wallet->balance }}<span class="unit">créditos</span></div>
            <div class="note">Créditos recreativos · sin valor real</div>
            <a href="{{ route('home') }}" class="btn btn-solid">Ir al casino</a>
        </div>

        <div class="wallet-hero-chart">
            <div class="label">Evolución del saldo</div>
            @if ($sparkline)
                <svg viewBox="0 0 300 60" preserveAspectRatio="none" aria-label="Evolución del saldo">
                    <defs>
                        <linearGradient id="spark-fill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#FF5A12" stop-opacity=".35"/>
                            <stop offset="100%" stop-color="#FF5A12" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <polygon points="0,60 {{ $sparkline }} 300,60" fill="url(#spark-fill)"/>
                    <polyline points="{{ $sparkline }}" fill="none" stroke="#FF5A12" stroke-width="2" vector-effect="non-scaling-stroke"/>
                </svg>
            @else
                <div class="chart-empty">El gráfico aparecerá cuando tengas más movimientos.</div>
            @endif
        </div>
    </section>

    {{-- 2. Indicadores --}}
    <section class="stats">
        <div class="stat">
            <span class="stat-ico credit-bg">↑</span>
            <div>
                <div class="stat-label">Total acreditado</div>
                <div class="stat-value credit">+{{ $credits }}</div>
            </div>
        </div>
        <div class="stat">
            <span class="stat-ico debit-bg">↓</span>
            <div>
                <div class="stat-label">Total debitado</div>
                <div class="stat-value debit">−{{ $debits }}</div>
            </div>
        </div>
        <div class="stat">
            <span class="stat-ico neutral-bg">#</span>
            <div>
                <div class="stat-label">Movimientos</div>
                <div class="stat-value">{{ $count }}</div>
            </div>
        </div>
        {{-- Cuadre: créditos − débitos debe ser igual al saldo --}}
        <div class="stat {{ $balanced ? '' : 'stat-alert' }}">
            <span class="stat-ico {{ $balanced ? 'credit-bg' : 'alert-bg' }}">{{ $balanced ? '✓' : '!' }}</span>
            <div>
                <div class="stat-label">Historial</div>
                @if ($balanced)
                    <div class="stat-value credit">Cuadra</div>
                @else
                    <div class="stat-value alert">Descuadre</div>
                    <div class="stat-hint">Según movimientos: {{ $expected }}</div>
                @endif
            </div>
        </div>
    </section>

    {{-- 3. Historial --}}
    <section class="card">
        <div class="card-head">
            <h3>Últimos movimientos</h3>
            <span class="muted">Últimos 20</span>
        </div>

        <ul class="tx-list">
            @forelse ($transactions as $tx)
                <li class="tx">
                    <span class="tx-ico {{ $tx->type }}-bg">{{ $tx->type === 'credit' ? '↑' : '↓' }}</span>
                    <div class="tx-info">
                        <div class="tx-desc">{{ $tx->description ?? ($tx->type === 'credit' ? 'Crédito' : 'Débito') }}</div>
                        <div class="tx-date">{{ $tx->created_at->format('d/m/Y · H:i') }}</div>
                    </div>
                    <div class="tx-amounts">
                        <div class="tx-amount {{ $tx->type }}">{{ $tx->type === 'credit' ? '+' : '−' }}{{ $tx->amount }}</div>
                        <div class="tx-after">Saldo {{ $tx->balance_after }}</div>
                    </div>
                </li>
            @empty
                <li class="tx-empty">Sin movimientos todavía.</li>
            @endforelse
        </ul>
    </section>
@endsection
