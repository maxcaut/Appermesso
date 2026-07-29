<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') — Appermesso</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <main class="account-page">
            <div class="ambient ambient-one"></div>
            <div class="ambient ambient-two"></div>

            <div class="account-shell">
                <a href="{{ url('/') }}" class="account-brand" aria-label="Torna ad Appermesso">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5h7l4 4V20.5H7z"/><path d="M14 3.5v4h4M10 12h5M10 16h5"/></svg>
                    </span>
                    <span><strong>Appermesso</strong><small>Gestione presenze</small></span>
                </a>

                <section class="account-card">
                    <div class="account-heading">
                        <p class="eyebrow">@yield('eyebrow')</p>
                        <h1>@yield('heading')</h1>
                        @hasSection('description')
                            <p>@yield('description')</p>
                        @endif
                    </div>

                    @if (session('status'))
                        <div class="form-message is-success" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="form-message is-error" role="alert" tabindex="-1" data-error-summary>
                            Controlla i campi indicati e riprova.
                        </div>
                    @endif

                    @yield('content')
                </section>
            </div>
        </main>
    </body>
</html>
