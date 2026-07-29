@extends('layouts.account')

@section('title', 'Recupera password')
@section('eyebrow', 'Recupero password')
@section('heading', 'Reimposta la password')
@section('description', 'Inserisci l’email associata al tuo account per ricevere il link di recupero.')

@section('content')
    <div
        class="form-message is-success is-hidden"
        role="status"
        data-recovery-bridge
        data-session-url="{{ route('password.session') }}"
    >
        Verifica del link di recupero in corso…
    </div>
    <form method="POST" action="{{ route('password.email') }}" class="account-form" data-loading-form>
        @csrf
        <label>
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @error('email') aria-describedby="email-error" @enderror>
            @error('email') <small id="email-error" class="field-error">{{ $message }}</small> @enderror
        </label>
        <button type="submit" class="primary-button account-submit" data-loading-label="Invio in corso…">Invia link di recupero</button>
    </form>
    <div class="account-links">
        <a href="{{ route('login') }}">Torna all’accesso</a>
        <a href="{{ url('/') }}" class="guest-link">Continua come ospite</a>
    </div>
@endsection
