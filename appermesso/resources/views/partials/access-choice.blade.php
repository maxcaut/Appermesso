<div
    id="access-choice"
    class="access-choice-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="access-choice-title"
    aria-describedby="access-choice-description"
>
    <section class="access-choice-card">
        <div class="access-choice-brand">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5h7l4 4V20.5H7z"/><path d="M14 3.5v4h4M10 12h5M10 16h5"/></svg>
            </span>
            <span><strong>Appermesso</strong><small>Gestione presenze</small></span>
        </div>

        <div class="access-choice-heading">
            <p class="eyebrow">Benvenuto</p>
            <h1 id="access-choice-title">Come vuoi continuare?</h1>
            <p id="access-choice-description">
                Accedi per recuperare i dati salvati, crea un account oppure utilizza l’app come ospite.
            </p>
        </div>

        <div class="access-choice-actions">
            <a href="{{ route('login') }}" class="primary-button access-choice-primary">Accedi</a>
            <a href="{{ route('register') }}" class="access-choice-secondary">Registrati</a>
            <a href="{{ route('home', ['ospite' => 1]) }}" class="access-choice-guest">
                Continua come ospite
                <small>Richiede l’accettazione della privacy policy a ogni accesso.</small>
            </a>
        </div>
    </section>
</div>
