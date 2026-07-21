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
        .logo img { width: 118px; }
        .title { font-size: 15px; font-weight: bold; text-align: center; }
        .section-title { margin: 0; padding: 4px 6px; border: 1px solid #000; background: #dce6f1; font-size: 14px; font-weight: normal; }
        .box { display: inline-block; width: 9px; height: 9px; margin-right: 4px; border: 1px solid #000; vertical-align: -1px; }
        .box.checked { background: #000; }
        .tall td { height: 37px; }
        .signature td { height: 52px; font-weight: bold; }
        .notes td { height: 34px; }
        .date { width: 150px; margin: 6px 12px 0 auto; padding: 7px; border: 2px solid #000; font-weight: bold; }
    </style>
</head>
<body>
@php
    $value = fn (string $key) => $data[$key] ?? '';
    $causali = $data['causale'] ?? [];
    $checked = fn (string $causale) => in_array($causale, $causali, true) ? ' checked' : '';
    $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';
    $periodCount = max(3, count($data['dalle_ore'] ?? []));
@endphp

<table>
    <tr class="header">
        <td class="logo" colspan="2"><img src="{{ public_path('hitachi-logo.jpg') }}" alt="Hitachi"></td>
        <td class="title" colspan="5">AUTORIZZAZIONE RICHIESTA PRESENZA / ASSENZA</td>
    </tr>
    <tr><td>COGNOME</td><td>{{ $value('cognome') }}</td><td>MATRICOLA</td><td>{{ $value('matricola') }}</td><td>LIVELLO</td><td>{{ $value('livello') }}</td><td>ENTE</td></tr>
    <tr><td>NOME</td><td>{{ $value('nome') }}</td><td>C.TRO DI COSTO</td><td>{{ $value('centro_costo') }}</td><td>QUALIFICA</td><td>{{ $value('qualifica') }}</td><td>{{ $value('ente') }}</td></tr>
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
        <td rowspan="2"><span class="box{{ $checked('altro permesso') }}"></span>altro permesso (DLG 104): {{ in_array('altro permesso', $causali, true) ? $value('altro_permesso') : '' }}</td>
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
            <td>Dalle ore {{ $data['dalle_ore'][$index] ?? '' }}</td>
            <td>del giorno {{ $formatDate($data['dal_giorno'][$index] ?? null) }}</td>
            <td>alle ore {{ $data['alle_ore'][$index] ?? '' }}</td>
            <td>del giorno {{ $formatDate($data['al_giorno'][$index] ?? null) }}</td>
        </tr>
    @endfor
</table>

<table><tr class="notes"><td colspan="2">Note</td></tr><tr class="signature"><td>RICHIEDENTE</td><td>RESPONSABILE UNITÀ ORGANIZZATIVA</td></tr></table>

<h2 class="section-title">CAUSALI PRESENZA</h2>
<table>
    <tr><td><span class="box"></span>straordinario giornaliero</td><td><span class="box"></span>straordinario fuori sede</td></tr>
    <tr><td><span class="box"></span>straordinario in giorni festivi o non lavorativi</td><td><span class="box"></span>presenza a recupero</td></tr>
</table>
<table>@for ($row = 0; $row < 4; $row++)<tr><td>Dalle ore</td><td>alle ore</td><td>del giorno</td><td>Motivo/N. Commessa</td></tr>@endfor</table>

<h2 class="section-title">OMESSA TIMBRATURA</h2>
<table>
    <tr><td>Giorno</td><td>Ingresso h/min.</td><td>Uscita h/min.</td></tr>
    <tr><td></td><td>Inizio pausa pranzo h/min.</td><td>Termine pausa pranzo h/min.</td></tr>
    <tr class="tall"><td colspan="3">Note</td></tr>
    <tr class="signature"><td>RICHIEDENTE</td><td><span class="box"></span>RESPONSABILE DIRETTO</td><td><span class="box"></span>RESPONSABILE U.O.</td></tr>
</table>

<div class="date">DATA {{ now()->format('d/m/Y') }}</div>
</body>
</html>
