<div
    id="privacy-consent"
    class="privacy-consent-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="privacy-consent-title"
    aria-describedby="privacy-consent-description"
    @if ($privacyAcceptUrl ?? null) data-accept-url="{{ $privacyAcceptUrl }}" @endif
    @if ($privacyPersistsToProfile ?? false) data-persists-to-profile="true" @endif
>
    <section class="privacy-consent-card">
        <div class="privacy-consent-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 3 5.5 5.8v5.1c0 4.3 2.7 8.2 6.5 10.1 3.8-1.9 6.5-5.8 6.5-10.1V5.8z"/>
                <path d="M9.2 12.1 11 14l4-4.2"/>
            </svg>
        </div>

        <div class="privacy-consent-heading">
            <p class="eyebrow">Privacy e protezione dei dati</p>
            <h1 id="privacy-consent-title">Consenso al trattamento dei dati personali</h1>
            <p id="privacy-consent-description">
                Prima di utilizzare Appermesso, leggi le informazioni sul trattamento dei dati inseriti nel modulo.
            </p>
        </div>

        <div class="privacy-consent-copy">
            <p>
                I dati personali forniti tramite l’app vengono trattati per compilare e generare il PDF della
                richiesta di presenza, assenza o omessa timbratura.
            </p>
            <p>
                Quando generi il documento, l’app registra nome, cognome, tipologia di utilizzo e data e ora
                dell’operazione. Gli altri dati inseriti vengono utilizzati per creare il PDF e non fanno parte
                di questa registrazione di utilizzo.
            </p>
            <p>
                @if ($privacyPersistsToProfile ?? false)
                    Il consenso verrà memorizzato nel tuo profilo e potrai revocarlo in qualsiasi momento dalla
                    pagina del profilo.
                @else
                    Se non presti il consenso, non potrai accedere alle funzionalità dell’app. Per gli utenti non
                    autenticati questa scelta non viene memorizzata.
                @endif
            </p>
        </div>

        <label class="privacy-consent-check">
            <input type="checkbox" id="privacy-consent-checkbox">
            <span class="privacy-checkbox" aria-hidden="true">
                <svg viewBox="0 0 16 16"><path d="m3 8 3 3 7-7"/></svg>
            </span>
            <span>
                Ho letto le informazioni sopra riportate e acconsento espressamente al trattamento dei miei
                dati personali per le finalità descritte.
            </span>
        </label>

        <p id="privacy-consent-error" class="privacy-consent-error" role="alert" hidden></p>

        <div class="privacy-consent-actions">
            <a href="{{ route('privacy.refused') }}" class="privacy-refuse-button">Rifiuta</a>
            <button type="button" id="privacy-accept" class="primary-button privacy-accept-button" disabled>
                Accetta e continua
            </button>
        </div>
    </section>
</div>
