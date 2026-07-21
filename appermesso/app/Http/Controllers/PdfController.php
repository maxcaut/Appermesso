<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'nome' => ['nullable', 'string', 'max:100'],
            'cognome' => ['nullable', 'string', 'max:100'],
            'matricola' => ['nullable', 'string', 'max:50'],
            'centro_costo' => ['nullable', 'string', 'max:100'],
            'livello' => ['nullable', 'string', 'max:50'],
            'qualifica' => ['nullable', 'string', 'max:100'],
            'ente' => ['nullable', 'string', 'max:150'],
            'causale' => ['nullable', 'array'],
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
        ]);

        $fileName = Str::slug(trim(($data['cognome'] ?? '').'-'.($data['nome'] ?? '')));
        $fileName = $fileName !== '' ? "richiesta-assenza-{$fileName}.pdf" : 'richiesta-assenza.pdf';

        return Pdf::loadView('pdf.template', ['data' => $data])
            ->setPaper('a4')
            ->download($fileName);
    }
}
