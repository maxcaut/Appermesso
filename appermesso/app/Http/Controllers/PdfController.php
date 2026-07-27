<?php

namespace App\Http\Controllers;

use App\Services\AppUsageTracker;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function __invoke(Request $request, AppUsageTracker $usageTracker)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'cognome' => ['required', 'string', 'max:100'],
            'matricola' => ['nullable', 'string', 'max:50'],
            'centro_costo' => ['nullable', 'string', 'max:100'],
            'livello' => ['nullable', 'string', 'max:50'],
            'qualifica' => ['nullable', 'string', 'max:100'],
            'ente' => ['nullable', 'string', 'max:150'],
            'causale' => ['nullable', 'array', 'required_without_all:causale_presenza,omessa_giorno,omessa_ingresso,omessa_uscita,omessa_inizio_pausa,omessa_termine_pausa,omessa_note'],
            'causale.*' => ['string', 'max:150'],
            'altro_permesso' => ['nullable', 'string', 'max:255'],
            'dalle_ore' => ['nullable', 'array'],
            'dalle_ore.*' => ['nullable', 'date_format:H:i'],
            'dal_giorno' => ['nullable', 'array'],
            'dal_giorno.*' => ['nullable', 'date'],
            'alle_ore' => ['nullable', 'array'],
            'alle_ore.*' => ['nullable', 'date_format:H:i'],
            'al_giorno' => ['nullable', 'array'],
            'al_giorno.*' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'causale_presenza' => ['nullable', 'array', 'required_without_all:causale,omessa_giorno,omessa_ingresso,omessa_uscita,omessa_inizio_pausa,omessa_termine_pausa,omessa_note'],
            'causale_presenza.*' => ['string', 'max:150'],
            'presenza_dalle_ore' => ['nullable', 'array'],
            'presenza_dalle_ore.*' => ['nullable', 'date_format:H:i'],
            'presenza_alle_ore' => ['nullable', 'array'],
            'presenza_alle_ore.*' => ['nullable', 'date_format:H:i'],
            'presenza_giorno' => ['nullable', 'array'],
            'presenza_giorno.*' => ['nullable', 'date'],
            'presenza_motivo' => ['nullable', 'array'],
            'presenza_motivo.*' => ['nullable', 'string', 'max:255'],
            'omessa_giorno' => ['nullable', 'date'],
            'omessa_ingresso' => ['nullable', 'date_format:H:i'],
            'omessa_uscita' => ['nullable', 'date_format:H:i'],
            'omessa_inizio_pausa' => ['nullable', 'date_format:H:i'],
            'omessa_termine_pausa' => ['nullable', 'date_format:H:i'],
            'omessa_note' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach (['nome', 'cognome', 'matricola', 'centro_costo', 'livello', 'qualifica', 'ente'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = Str::upper($data[$field]);
            }
        }

        $fileName = Str::slug(trim(($data['cognome'] ?? '').'-'.($data['nome'] ?? '')));
        $fileName = $fileName !== '' ? "richiesta-assenza-{$fileName}.pdf" : 'richiesta-assenza.pdf';

        $usageTracker->recordPdfGenerated($data['nome'], $data['cognome']);

        return Pdf::loadView('pdf.template', ['data' => $data])
            ->setPaper('a4')
            ->download($fileName);
    }
}
