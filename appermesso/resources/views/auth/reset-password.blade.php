@extends('layouts.account')

@section('title', 'Nuova password')
@section('eyebrow', 'Recupero password')
@section('heading', 'Scegli una nuova password')
@section('description', 'Inserisci e conferma la nuova password del tuo account.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="account-form" data-loading-form>
        @csrf
        <label>
            <span>Nuova password</span>
            <span class="password-field">
                <input type="password" name="password" autocomplete="new-password" required autofocus
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @error('password') aria-describedby="password-error" @enderror>
                <button type="button" class="password-toggle" aria-label="Mostra password" aria-pressed="false">Mostra</button>
            </span>
            @error('password') <small id="password-error" class="field-error">{{ $message }}</small> @enderror
        </label>
        <label>
            <span>Conferma nuova password</span>
            <span class="password-field">
                <input type="password" name="password_confirmation" autocomplete="new-password" required>
                <button type="button" class="password-toggle" aria-label="Mostra password" aria-pressed="false">Mostra</button>
            </span>
        </label>
        <button type="submit" class="primary-button account-submit" data-loading-label="Salvataggio in corso…">Salva nuova password</button>
    </form>
@endsection
