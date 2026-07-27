<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4; margin: 11mm 9mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #000; font-family: DejaVu Sans, sans-serif; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { height: 20px; padding: 3px 5px; border: 1px solid #000; vertical-align: top; }
        .header td { height: 54px; vertical-align: middle; }
        .logo { width: 28%; text-align: center; }
        .logo-crop { height: 48px; overflow: hidden; text-align: center; }
        .logo img { display: inline-block; width: 130px; margin-top: -12px; }
        .title { font-size: 15px; font-weight: bold; text-align: center; }
        .user-value { font-size: 9px; font-weight: bold; }
        .presence-reason-label { display: block; white-space: nowrap; }
        .presence-reason-value { display: block; }
        .section-title { margin: 0; padding: 4px 6px; border: 1px solid #000; background: #dce6f1; font-size: 14px; font-weight: normal; }
        .box { display: inline-block; width: 9px; height: 9px; margin-right: 4px; border: 1px solid #000; vertical-align: -1px; }
        .box.checked { background: #000; }
        .tall td { height: 37px; }
        .signature td { height: 52px; font-weight: bold; }
        .notes td { height: 34px; }
        .notes-value { white-space: pre-wrap; }
        .date { width: 150px; margin: 6px 12px 0 auto; padding: 7px; border: 2px solid #000; font-weight: bold; }
    </style>
</head>
<body>
@php
    $value = fn (string $key) => $data[$key] ?? '';
    $causali = $data['causale'] ?? [];
    $causaliPresenza = $data['causale_presenza'] ?? [];
    $checked = fn (string $causale) => in_array($causale, $causali, true) ? ' checked' : '';
    $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';
    $periodCount = max(3, count($data['dalle_ore'] ?? []));
    $presenceCount = max(4, count($data['presenza_dalle_ore'] ?? []));
    $presenceChecked = fn (string $causale) => in_array($causale, $causaliPresenza, true) ? ' checked' : '';
@endphp

<table>
    <tr class="header">
        <td class="logo" colspan="2"><div class="logo-crop"><img src="{{ public_path('hitachi.png') }}" alt="Hitachi"></div></td>
        <td class="title" colspan="5">AUTORIZZAZIONE RICHIESTA PRESENZA / ASSENZA</td>
    </tr>
    <tr><td>COGNOME</td><td class="user-value">{{ $value('cognome') }}</td><td>MATRICOLA</td><td class="user-value">{{ $value('matricola') }}</td><td>LIVELLO</td><td class="user-value">{{ $value('livello') }}</td><td>ENTE</td></tr>
    <tr><td>NOME</td><td class="user-value">{{ $value('nome') }}</td><td>C.TRO DI COSTO</td><td class="user-value">{{ $value('centro_costo') }}</td><td>QUALIFICA</td><td class="user-value">{{ $value('qualifica') }}</td><td class="user-value">{{ $value('ente') }}</td></tr>
</table>

<h2 class="section-title">CAUSALI ASSENZA</h2>
<table>
    <tr>
        <td><span class="box{{ $checked('ferie HFEG') }}"></span>ferie HFEG</td>
        <td><span class="box{{ $checked('permesso non retribuito HNRE') }}"></span>permesso non retribuito HNRE</td>
        <td><span class="box{{ $checked('servizio citta TRAS') }}"></span>servizio città TRAS</td>
        <td><span class="box{{ $checked('permesso studio HSTU') }}"></span>permesso studio HSTU</td>
    </tr>
    <tr>
        <td><span class="box{{ $checked('par HPAR') }}"></span>PAR HPAR</td>
        <td><span class="box{{ $checked('permesso lutto HLUT') }}"></span>permesso lutto HLUT</td>
        <td><span class="box{{ $checked('servizio fuori citta TRAS') }}"></span>servizio fuori città TRAS</td>
        <td><span class="box{{ $checked('recupero servizio elettorale HELE') }}"></span>recupero servizio elettorale HELE</td>
    </tr>
    <tr>
        <td><span class="box{{ $checked('conto ore CORF') }}"></span>conto ore CORF</td>
        <td><span class="box{{ $checked('permesso cariche elettive HPUB') }}"></span>cariche elettive HPUB</td>
        <td><span class="box{{ $checked('servizio sindacale HSIN') }}"></span>servizio sindacale HSIN</td>
        <td rowspan="2"><span class="box{{ $checked('altro permesso') }}"></span>altro permesso (DLG 104): <span class="user-value">{{ in_array('altro permesso', $causali, true) ? $value('altro_permesso') : '' }}</span></td>
    </tr>
    <tr>
        <td><span class="box{{ $checked('permesso a recupero PREC') }}"></span>permesso a recupero PREC</td>
        <td><span class="box{{ $checked('permesso visita medica HVSP') }}"></span>visita medica HVSP</td>
        <td><span class="box{{ $checked('congedo matrimoniale HCON') }}"></span>congedo matrimoniale HCON</td>
    </tr>
</table>

<table><tr><td><b>RISERVATO ALLA INFERMERIA DI FABBRICA</b><br><span class="box"></span>infortunio &nbsp; <span class="box"></span>servizio visita specialistica</td><td><b>VISTO INFERMERIA</b></td></tr></table>

<table>
    @for ($index = 0; $index < $periodCount; $index++)
        <tr>
            <td>Dalle ore <span class="user-value">{{ $data['dalle_ore'][$index] ?? '' }}</span></td>
            <td>del giorno <span class="user-value">{{ $formatDate($data['dal_giorno'][$index] ?? null) }}</span></td>
            <td>alle ore <span class="user-value">{{ $data['alle_ore'][$index] ?? '' }}</span></td>
            <td>del giorno <span class="user-value">{{ $formatDate($data['al_giorno'][$index] ?? null) }}</span></td>
        </tr>
    @endfor
</table>

<table><tr class="notes"><td colspan="2">Note: <span class="user-value notes-value">{{ $value('note') }}</span></td></tr><tr class="signature"><td>RICHIEDENTE</td><td>RESPONSABILE UNITÀ ORGANIZZATIVA</td></tr></table>

<h2 class="section-title">CAUSALI PRESENZA</h2>
<table>
    <tr><td><span class="box{{ $presenceChecked('straordinario giornaliero') }}"></span>straordinario giornaliero</td><td><span class="box{{ $presenceChecked('straordinario fuori sede') }}"></span>straordinario fuori sede</td></tr>
    <tr><td><span class="box{{ $presenceChecked('straordinario in giorni festivi o non lavorativi') }}"></span>straordinario in giorni festivi o non lavorativi</td><td><span class="box{{ $presenceChecked('presenza a recupero') }}"></span>presenza a recupero</td></tr>
</table>
<table>
    @for ($row = 0; $row < $presenceCount; $row++)
        <tr>
            <td>Dalle ore <span class="user-value">{{ $data['presenza_dalle_ore'][$row] ?? '' }}</span></td>
            <td>alle ore <span class="user-value">{{ $data['presenza_alle_ore'][$row] ?? '' }}</span></td>
            <td>del giorno <span class="user-value">{{ $formatDate($data['presenza_giorno'][$row] ?? null) }}</span></td>
            <td><span class="presence-reason-label">Motivo/N. Commessa</span><span class="user-value presence-reason-value">{{ $data['presenza_motivo'][$row] ?? '' }}</span></td>
        </tr>
    @endfor
</table>

<h2 class="section-title">OMESSA TIMBRATURA</h2>
<table>
    <tr>
        <td>Giorno <span class="user-value">{{ $formatDate($value('omessa_giorno')) }}</span></td>
        <td>Ingresso h/min. <span class="user-value">{{ $value('omessa_ingresso') }}</span></td>
        <td>Uscita h/min. <span class="user-value">{{ $value('omessa_uscita') }}</span></td>
    </tr>
    <tr>
        <td></td>
        <td>Inizio pausa pranzo h/min. <span class="user-value">{{ $value('omessa_inizio_pausa') }}</span></td>
        <td>Termine pausa pranzo h/min. <span class="user-value">{{ $value('omessa_termine_pausa') }}</span></td>
    </tr>
    <tr class="tall"><td colspan="3">Note <span class="user-value notes-value">{{ $value('omessa_note') }}</span></td></tr>
    <tr class="signature"><td>RICHIEDENTE</td><td><span class="box"></span>RESPONSABILE DIRETTO</td><td><span class="box"></span>RESPONSABILE U.O.</td></tr>
</table>

<div class="date">DATA {{ now()->format('d/m/Y') }}</div>
</body>
</html>
