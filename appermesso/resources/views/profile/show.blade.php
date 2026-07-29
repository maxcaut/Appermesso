@extends('layouts.account')

@section('title', 'Profilo')
@section('eyebrow', 'Area personale')
@section('heading', 'Il tuo profilo')
@section('description', 'Salva i dati che verranno proposti automaticamente nel modulo PDF.')

@section('content')
    @php
        $profileValue = fn (string $key) => old($key, data_get($profile ?? [], $key, ''));
    @endphp
    @if ($profileError ?? null)
        <div class="form-message is-error" role="alert">{{ $profileError }}</div>
    @endif
    <form method="POST" action="{{ route('profile.update') }}" class="account-form profile-form" data-loading-form>
        @csrf
        @method('PUT')
        <div class="form-grid">
            <label>
                <span>Nome</span>
                <input type="text" name="nome" value="{{ $profileValue('nome') }}" autocomplete="given-name" required
                    aria-invalid="{{ $errors->has('nome') ? 'true' : 'false' }}">
                @error('nome') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                <span>Cognome</span>
                <input type="text" name="cognome" value="{{ $profileValue('cognome') }}" autocomplete="family-name" required
                    aria-invalid="{{ $errors->has('cognome') ? 'true' : 'false' }}">
                @error('cognome') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                <span>Matricola</span>
                <input type="text" name="matricola" value="{{ $profileValue('matricola') }}">
                @error('matricola') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                <span>Centro di costo</span>
                <input type="text" name="centro_costo" value="{{ $profileValue('centro_costo') }}">
                @error('centro_costo') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                <span>Livello</span>
                <select name="livello">
                    @foreach (['D2' => 'D2 ex 3 liv.', 'C2' => 'C2 ex 4 liv.', 'C3' => 'C3 ex 5 liv.', 'B1' => 'B1 ex 5S liv.'] as $value => $label)
                        <option value="{{ $value }}" @selected($profileValue('livello') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('livello') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                <span>Qualifica</span>
                <select name="qualifica">
                    @foreach (['Operaio', 'Impiegato'] as $value)
                        <option value="{{ $value }}" @selected($profileValue('qualifica') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
                @error('qualifica') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label class="field-span-2">
                <span>Ente</span>
                <input type="text" name="ente" value="{{ $profileValue('ente') }}">
                @error('ente') <small class="field-error">{{ $message }}</small> @enderror
            </label>
        </div>
        <p class="profile-note">Le modifiche effettuate direttamente nel modulo PDF non aggiorneranno questi dati.</p>
        <button type="submit" class="primary-button account-submit" data-loading-label="Salvataggio in corso…">Salva modifiche</button>
    </form>

    <div class="profile-actions">
        <a href="{{ url('/') }}" class="secondary-link">Vai al modulo</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button">Esci</button>
        </form>
    </div>
@endsection
