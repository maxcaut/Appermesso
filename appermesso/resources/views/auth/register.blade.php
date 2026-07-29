@extends('layouts.account')

@section('title', 'Registrati')
@section('eyebrow', 'Nuovo account')
@section('heading', 'Crea il tuo account')
@section('description', 'Registrati con email e password. Potrai aggiungere l’anagrafica dal profilo.')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="account-form" data-loading-form>
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
                <input type="password" name="password" autocomplete="new-password" required
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @error('password') aria-describedby="password-error" @enderror>
                <button type="button" class="password-toggle" aria-label="Mostra password" aria-pressed="false">Mostra</button>
            </span>
            @error('password') <small id="password-error" class="field-error">{{ $message }}</small> @enderror
        </label>
        <label>
            <span>Conferma password</span>
            <span class="password-field">
                <input type="password" name="password_confirmation" autocomplete="new-password" required>
                <button type="button" class="password-toggle" aria-label="Mostra password" aria-pressed="false">Mostra</button>
            </span>
        </label>
        <button type="submit" class="primary-button account-submit" data-loading-label="Registrazione in corso…">Registrati</button>
    </form>

    <div class="account-links">
        <p>Hai già un account? <a href="{{ route('login') }}">Accedi</a></p>
        <a href="{{ url('/') }}" class="guest-link">Continua come ospite</a>
    </div>
@endsection
