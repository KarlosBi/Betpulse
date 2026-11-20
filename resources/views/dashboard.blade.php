{{-- Mi Wallet: saldo actual y últimos 20 movimientos del jugador --}}
@extends('layouts.app')

@section('title', 'Mi Wallet · BetPulse')

@section('content')
    <div class="section-head">
        <h3>Mi Wallet</h3>
    </div>

    <div class="card wallet-hero">
        <div>
            <div class="label">Saldo disponible</div>
            {{-- El saldo es un string ("1000.0000"); no se pasa por number_format para no convertirlo a float --}}
            <div class="amount">{{ $wallet->balance }}</div>
            <div class="note">Créditos recreativos · sin valor real</div>
        </div>
        <a href="{{ route('home') }}" class="btn btn-solid">Ir al casino</a>
    </div>

    <div class="card">
        <h3>Últimos movimientos</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th class="num">Monto</th>
                        <th class="num">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $tx->description }}</td>
                            <td class="num {{ $tx->type }}">{{ $tx->type === 'credit' ? '+' : '−' }}{{ $tx->amount }}</td>
                            <td class="num">{{ $tx->balance_after }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin movimientos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
