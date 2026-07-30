@extends('layouts.account')

@section('title', 'Accedi')
@section('eyebrow', 'Area personale')
@section('heading', 'Bentornato')
@section('description', 'Accedi per usare i dati salvati nel tuo profilo.')

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" class="account-form" data-loading-form>
        @csrf
        <label>
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @error('email') aria-describedby="email-error" @enderror>
            @error('email') <small id="email-error" class="field-error">{{ $message }}</small> @enderror
        </label>
        <label>
            <span>Password</span>
            <span class="password-field">
                <input type="password" name="password" autocomplete="current-password" required
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @error('password') aria-describedby="password-error" @enderror>
                <button type="button" class="password-toggle" aria-label="Mostra password" aria-pressed="false">Mostra</button>
            </span>
            @error('password') <small id="password-error" class="field-error">{{ $message }}</small> @enderror
        </label>
        <div class="account-form-meta">
            <a href="{{ route('password.request') }}">Password dimenticata?</a>
        </div>
        <button type="submit" class="primary-button account-submit" data-loading-label="Accesso in corso…">Accedi</button>
    </form>

    <div class="account-links">
        <p>Non hai un account? <a href="{{ route('register') }}">Registrati</a></p>
        <a href="{{ route('home', ['ospite' => 1]) }}" class="guest-link">Continua come ospite</a>
    </div>
@endsection
