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
                Prima di utilizzare Appermesso, leggi quali dati vengono trattati e quali vengono memorizzati.
            </p>
        </div>

        <div class="privacy-consent-copy">
            <p>
                <strong>Dati memorizzati per ogni PDF generato.</strong>
                L’app registra nome, cognome, tipologia di utilizzo (assenza, presenza e/o omessa timbratura)
                e data e ora della generazione.
            </p>
            <p>
                <strong>Dati memorizzati se crei un account.</strong>
                Vengono conservati identificativo utente, indirizzo email e credenziali di autenticazione.
                Se compili il profilo, vengono inoltre salvati nome, cognome, matricola, centro di costo,
                livello, qualifica ed ente, insieme alle date di creazione e ultimo aggiornamento del profilo.
                Vengono memorizzate anche la data e l’ora in cui presti il consenso e, durante l’accesso,
                le informazioni tecniche e i token necessari a mantenere attiva la sessione.
            </p>
            <p>
                <strong>Dati non memorizzati nel registro di utilizzo o nel profilo.</strong>
                Causali e dettagli di assenza o presenza, periodi, orari, motivi o numero di commessa, note e
                dati dell’omessa timbratura vengono trattati esclusivamente per generare il PDF richiesto.
                Il PDF viene inviato direttamente al download e non viene archiviato dall’app.
            </p>
            <p>
                I dati di account, profilo, consenso e utilizzo sono conservati nel servizio Supabase.
                L’app usa inoltre un cookie tecnico di sessione, indispensabile per autenticazione, sicurezza
                e funzionamento del servizio; non vengono usati cookie pubblicitari o di profilazione.
            </p>
            <p>
                @if ($privacyPersistsToProfile ?? false)
                    Puoi revocare il consenso in qualsiasi momento dalla pagina del profilo; la revoca termina
                    la sessione e impedisce di continuare a usare l’app finché non presti nuovamente il consenso.
                @else
                    Se non presti il consenso, non potrai accedere alle funzionalità dell’app. Per gli utenti non
                    autenticati l’accettazione mostrata nell’interfaccia non viene salvata nel profilo; la
                    generazione del PDF richiede comunque una conferma esplicita e produce il registro di
                    utilizzo descritto sopra.
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
