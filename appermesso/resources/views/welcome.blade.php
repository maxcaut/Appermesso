<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Appermesso — Richiesta assenza</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <main class="screen-app">
            <div class="ambient ambient-one"></div>
            <div class="ambient ambient-two"></div>

            <form id="permesso-form" class="app-shell" method="POST" action="{{ route('pdf.generate') }}">
                @csrf
                <header class="hero-panel">
                    <div class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5h7l4 4V20.5H7z"/><path d="M14 3.5v4h4M10 12h5M10 16h5"/></svg>
                    </div>
                    <div class="hero-copy">
                        <p class="eyebrow">Gestione presenze</p>
                        <h1>Richiesta di assenza</h1>
                        <p>Compila i dati, seleziona una o più causali e genera il modulo pronto per la firma.</p>
                    </div>
                    <div class="hero-badge"><span></span> Modulo digitale</div>
                </header>

                <section class="form-card">
                    <div class="section-heading">
                        <span class="step-number">01</span>
                        <div><h2>Dati del richiedente</h2><p>Inserisci le informazioni anagrafiche e aziendali.</p></div>
                    </div>
                    <div class="form-grid">
                        <label><span>Nome</span><input type="text" name="nome" autocomplete="given-name" placeholder="Es. Mario" required></label>
                        <label><span>Cognome</span><input type="text" name="cognome" autocomplete="family-name" placeholder="Es. Rossi" required></label>
                        <label><span>Matricola</span><input type="text" name="matricola" placeholder="Numero matricola"></label>
                        <label><span>Centro di costo</span><input type="text" name="centro_costo" placeholder="Centro di costo"></label>
                        <label>
                            <span>Livello</span>
                            <select name="livello">
                                <option value="D2">D2 ex 3 liv.</option>
                                <option value="C2">C2 ex 4 liv.</option>
                                <option value="C3">C3 ex 5 liv.</option>
                                <option value="B1">B1 ex 5S liv.</option>
                            </select>
                        </label>
                        <label>
                            <span>Qualifica</span>
                            <select name="qualifica">
                                <option value="Operaio">Operaio</option>
                                <option value="Impiegato">Impiegato</option>
                            </select>
                        </label>
                        <label class="field-span-2"><span>Ente</span><input type="text" name="ente" placeholder="Ente o unità organizzativa"></label>
                    </div>
                </section>

                <section class="form-card">
                    <div class="section-heading">
                        <span class="step-number">02</span>
                        <div><h2>Causali di assenza</h2><p>Puoi selezionare una o più opzioni.</p></div>
                        <span id="causali-count" class="selection-count">0 selezionate</span>
                    </div>
                    <fieldset class="causali-grid">
                        <legend class="sr-only">Seleziona le causali di assenza</legend>
                        @foreach ([
                            ['ferie HFEG', 'Ferie', 'HFEG'],
                            ['permesso non retribuito HNRE', 'Permesso non retribuito', 'HNRE'],
                            ['servizio citta TRAS', 'Servizio città', 'TRAS'],
                            ['permesso studio HSTU', 'Permesso studio', 'HSTU'],
                            ['par HPAR', 'PAR', 'HPAR'],
                            ['permesso lutto HLUT', 'Permesso lutto', 'HLUT'],
                            ['servizio fuori citta TRAS', 'Servizio fuori città', 'TRAS'],
                            ['recupero servizio elettorale HELE', 'Recupero servizio elettorale', 'HELE'],
                            ['conto ore CORF', 'Conto ore', 'CORF'],
                            ['permesso cariche elettive HPUB', 'Cariche elettive', 'HPUB'],
                            ['servizio sindacale HSIN', 'Servizio sindacale', 'HSIN'],
                            ['permesso a recupero PREC', 'Permesso a recupero', 'PREC'],
                            ['permesso visita medica HVSP', 'Visita medica', 'HVSP'],
                            ['congedo matrimoniale HCON', 'Congedo matrimoniale', 'HCON'],
                            ['altro permesso', 'Altro permesso', 'DLG 104'],
                        ] as [$value, $label, $code])
                            <label class="causale-option">
                                <input type="checkbox" name="causale[]" value="{{ $value }}">
                                <span class="custom-check" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m3 8 3 3 7-7"/></svg></span>
                                <span class="causale-copy"><strong>{{ $label }}</strong><small>{{ $code }}</small></span>
                            </label>
                        @endforeach
                    </fieldset>
                    <label id="altro-permesso-field" class="other-field is-hidden">
                        <span>Specifica altro permesso</span>
                        <input type="text" name="altro_permesso" placeholder="Inserisci la causale specifica">
                    </label>
                </section>

                <section class="form-card">
                    <div class="section-heading period-heading">
                        <span class="step-number">03</span>
                        <div><h2>Periodo di assenza</h2><p>Aggiungi tutti gli intervalli necessari.</p></div>
                        <button type="button" id="add-periodo" class="secondary-button"><span aria-hidden="true">＋</span> Aggiungi periodo</button>
                    </div>
                    <div id="periodi-screen" class="periodi-list">
                        <div class="periodo-row">
                            <div class="period-index">1</div>
                            <label><span>Dalle ore</span><input type="time" name="dalle_ore[]"></label>
                            <label><span>Dal giorno</span><input type="date" name="dal_giorno[]"></label>
                            <label><span>Alle ore</span><input type="time" name="alle_ore[]"></label>
                            <label><span>Al giorno</span><input type="date" name="al_giorno[]"></label>
                            <button type="button" class="remove-periodo" aria-label="Rimuovi periodo">×</button>
                        </div>
                    </div>
                </section>

                <section class="form-card">
                    <div class="section-heading">
                        <span class="step-number">04</span>
                        <div><h2>Note</h2><p>Aggiungi eventuali informazioni utili alla richiesta di assenza.</p></div>
                    </div>
                    <label class="notes-field">
                        <span>Note</span>
                        <textarea name="note" rows="4" maxlength="1000" placeholder="Inserisci eventuali note"></textarea>
                    </label>
                </section>

                <section class="form-card">
                    <div class="section-heading">
                        <span class="step-number">05</span>
                        <div><h2>Causali di presenza</h2><p>Seleziona una o più causali e indica gli intervalli.</p></div>
                        <span id="causali-presenza-count" class="selection-count">0 selezionate</span>
                    </div>
                    <fieldset class="causali-grid presence-causes-grid">
                        <legend class="sr-only">Seleziona le causali di presenza</legend>
                        @foreach ([
                            ['straordinario giornaliero', 'Straordinario giornaliero'],
                            ['straordinario fuori sede', 'Straordinario fuori sede'],
                            ['straordinario in giorni festivi o non lavorativi', 'Straordinario festivo o non lavorativo'],
                            ['presenza a recupero', 'Presenza a recupero'],
                        ] as [$value, $label])
                            <label class="causale-option">
                                <input type="checkbox" name="causale_presenza[]" value="{{ $value }}">
                                <span class="custom-check" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m3 8 3 3 7-7"/></svg></span>
                                <span class="causale-copy"><strong>{{ $label }}</strong></span>
                            </label>
                        @endforeach
                    </fieldset>

                    <div class="section-heading period-heading presence-period-heading">
                        <div><h3>Periodi di presenza</h3><p>Aggiungi tutti gli intervalli necessari.</p></div>
                        <button type="button" id="add-presenza" class="secondary-button"><span aria-hidden="true">＋</span> Aggiungi periodo</button>
                    </div>
                    <div id="presenze-screen" class="periodi-list">
                        <div class="periodo-row presenza-row">
                            <div class="period-index">1</div>
                            <label><span>Dalle ore</span><input type="time" name="presenza_dalle_ore[]"></label>
                            <label><span>Alle ore</span><input type="time" name="presenza_alle_ore[]"></label>
                            <label><span>Giorno</span><input type="date" name="presenza_giorno[]"></label>
                            <label><span>Motivo / N. commessa</span><input type="text" name="presenza_motivo[]" maxlength="255" placeholder="Motivo o commessa"></label>
                            <button type="button" class="remove-periodo" aria-label="Rimuovi periodo di presenza">×</button>
                        </div>
                    </div>
                </section>

                <section class="form-card">
                    <div class="section-heading">
                        <span class="step-number">06</span>
                        <div><h2>Omessa timbratura</h2><p>Inserisci i dati della timbratura omessa.</p></div>
                    </div>
                    <div class="form-grid">
                        <label><span>Giorno</span><input type="date" name="omessa_giorno"></label>
                        <label><span>Ingresso</span><input type="time" name="omessa_ingresso"></label>
                        <label><span>Uscita</span><input type="time" name="omessa_uscita"></label>
                        <label><span>Inizio pausa pranzo</span><input type="time" name="omessa_inizio_pausa"></label>
                        <label><span>Termine pausa pranzo</span><input type="time" name="omessa_termine_pausa"></label>
                    </div>
                    <label class="notes-field">
                        <span>Note</span>
                        <textarea name="omessa_note" rows="4" maxlength="1000" placeholder="Inserisci eventuali note"></textarea>
                    </label>
                </section>

                <div class="actions-panel">
                    <div><strong>Il modulo è pronto?</strong><span>Controlla i dati prima di generare il documento.</span></div>
                    <button type="submit" id="generate-pdf" class="primary-button">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14"/></svg>
                        Genera PDF
                    </button>
                </div>
            </form>
        </main>

        {{--
        <section id="pdf-export" class="pdf-export" aria-hidden="true">
            <table class="pdf-table main-table">
                <colgroup>
                    <col class="main-col-label">
                    <col class="main-col-value-wide">
                    <col class="main-col-label-wide">
                    <col class="main-col-value-small">
                    <col class="main-col-label">
                    <col class="main-col-value">
                    <col class="main-col-ente">
                </colgroup>
                <tbody>
                    <tr class="header-row">
                        <td class="logo-cell" colspan="2"><img src="{{ asset('hitachi-logo.jpg') }}" alt="Hitachi"></td>
                        <td class="document-title" colspan="5">AUTORIZZAZIONE RICHIESTA PRESENZA / ASSENZA</td>
                    </tr>
                    <tr>
                        <td class="label-cell">COGNOME</td>
                        <td><span data-pdf-field="cognome"></span></td>
                        <td class="label-cell">MATRICOLA</td>
                        <td><span data-pdf-field="matricola"></span></td>
                        <td class="label-cell">LIVELLO</td>
                        <td><span data-pdf-field="livello"></span></td>
                        <td class="label-cell">ENTE</td>
                    </tr>
                    <tr>
                        <td class="label-cell">NOME</td>
                        <td><span data-pdf-field="nome"></span></td>
                        <td class="label-cell">C.TRO DI COSTO</td>
                        <td><span data-pdf-field="centro_costo"></span></td>
                        <td class="label-cell">QUALIFICA</td>
                        <td><span data-pdf-field="qualifica"></span></td>
                        <td><span data-pdf-field="ente"></span></td>
                    </tr>
                </tbody>
            </table>

            <h2>CAUSALI ASSENZA</h2>
            <table class="pdf-table checkbox-table">
                <colgroup>
                    <col class="absence-col-1">
                    <col class="absence-col-2">
                    <col class="absence-col-3">
                    <col class="absence-col-4">
                </colgroup>
                <tbody>
                    <tr>
                        <td><span data-pdf-check="ferie HFEG"></span> ferie HFEG</td>
                        <td><span data-pdf-check="permesso non retribuito HNRE"></span> permesso non retribuito HNRE</td>
                        <td><span data-pdf-check="servizio citta TRAS"></span> servizio citta TRAS</td>
                        <td><span data-pdf-check="permesso studio HSTU"></span> permesso studio HSTU</td>
                    </tr>
                    <tr>
                        <td><span data-pdf-check="par HPAR"></span> par HPAR</td>
                        <td><span data-pdf-check="permesso lutto HLUT"></span> permesso lutto HLUT</td>
                        <td><span data-pdf-check="servizio fuori citta TRAS"></span> servizio fuori citta TRAS</td>
                        <td><span data-pdf-check="recupero servizio elettorale HELE"></span> recupero servizio elettorale HELE</td>
                    </tr>
                    <tr>
                        <td><span data-pdf-check="conto ore CORF"></span> conto ore CORF</td>
                        <td><span data-pdf-check="permesso cariche elettive HPUB"></span> permesso cariche elettive HPUB</td>
                        <td><span data-pdf-check="servizio sindacale HSIN"></span> servizio sindacale HSIN</td>
                        <td rowspan="2"><span data-pdf-check="altro permesso"></span> altro permesso (specificare): DLG 104 <span data-pdf-field="altro_permesso"></span></td>
                    </tr>
                    <tr>
                        <td><span data-pdf-check="permesso a recupero PREC"></span> permesso a recupero PREC</td>
                        <td><span data-pdf-check="permesso visita medica HVSP"></span> permesso visita medica HVSP</td>
                        <td><span data-pdf-check="congedo matrimoniale HCON"></span> congedo matrimoniale HCON</td>
                    </tr>
                </tbody>
            </table>

            <table class="pdf-table infirmary-table">
                <colgroup>
                    <col class="split-col-left">
                    <col class="split-col-right">
                </colgroup>
                <tbody>
                    <tr>
                        <td><strong>RISERVATO ALLA INFERMERIA DI FABBRICA</strong><br><span class="empty-box"></span> infortunio <span class="empty-box"></span> servizio visita specialistica</td>
                        <td><strong>VISTO INFERMERIA</strong></td>
                    </tr>
                </tbody>
            </table>

            <table class="pdf-table period-table">
                <tbody id="pdf-periodi"></tbody>
            </table>

            <table class="pdf-table notes-table">
                <colgroup>
                    <col class="split-col-left">
                    <col class="split-col-right">
                </colgroup>
                <tbody>
                    <tr><td colspan="2">Note</td></tr>
                    <tr><td>RICHIEDENTE</td><td>RESPONSABILE UNITA' ORGANIZZATIVA</td></tr>
                </tbody>
            </table>

            <h2>CAUSALI PRESENZA</h2>
            <table class="pdf-table checkbox-table two-columns">
                <colgroup>
                    <col class="split-col-left">
                    <col class="split-col-right">
                </colgroup>
                <tbody>
                    <tr><td><span class="empty-box"></span> straordinario giornaliero</td><td><span class="empty-box"></span> straordinario fuori sede</td></tr>
                    <tr><td><span class="empty-box"></span> straordinario in giorni festivi o non lavorativi</td><td><span class="empty-box"></span> presenza a recupero</td></tr>
                </tbody>
            </table>

            <table class="pdf-table presence-table">
                <tbody>
                    <tr><td>Dalle ore</td><td>alle ore</td><td>del giorno</td><td>Motivo/N.Commessa</td></tr>
                    <tr><td>Dalle ore</td><td>alle ore</td><td>del giorno</td><td>Motivo/N.Commessa</td></tr>
                    <tr><td>Dalle ore</td><td>alle ore</td><td>del giorno</td><td>Motivo/N.Commessa</td></tr>
                    <tr><td>Dalle ore</td><td>alle ore</td><td>del giorno</td><td>Motivo/N.Commessa</td></tr>
                </tbody>
            </table>

            <h2>OMESSA TIMBRATURA</h2>
            <table class="pdf-table missed-table">
                <tbody>
                    <tr><td>Giorno</td><td>Ingresso h/min.</td><td>Uscita h/min.</td></tr>
                    <tr><td></td><td>Inizio pausa pranzo h/min.</td><td>Termine pausa pranzo h/min.</td></tr>
                    <tr class="note-row"><td colspan="3">Note</td></tr>
                    <tr class="sign-row"><td>RICHIEDENTE</td><td><span class="empty-box"></span> RESPONSABILE DIRETTO</td><td><span class="empty-box"></span> RESPONSABILE U.O.</td></tr>
                </tbody>
            </table>

            <div class="date-box">DATA</div>
        </section>
        --}}
    </body>
</html>
