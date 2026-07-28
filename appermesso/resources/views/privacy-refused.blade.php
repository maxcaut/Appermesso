<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Appermesso — Consenso non prestato</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="antialiased">
        <main class="privacy-refused-page">
            <div class="ambient ambient-one"></div>
            <div class="ambient ambient-two"></div>

            <section class="privacy-refused-card">
                <div class="privacy-consent-icon privacy-refused-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 5.5 5.8v5.1c0 4.3 2.7 8.2 6.5 10.1 3.8-1.9 6.5-5.8 6.5-10.1V5.8z"/>
                        <path d="m9.5 9.5 5 5m0-5-5 5"/>
                    </svg>
                </div>

                <p class="eyebrow">Consenso non prestato</p>
                <h1>Non puoi utilizzare Appermesso</h1>
                <p>
                    Senza il consenso al trattamento dei dati personali non è possibile accedere alle funzionalità
                    dell’app né generare il documento.
                </p>
                <a href="{{ url('/') }}" class="primary-button privacy-return-button">Torna alla richiesta di consenso</a>
            </section>
        </main>
    </body>
</html>
