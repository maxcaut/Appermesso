<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_pdf_is_generated_from_submitted_form_data(): void
    {
        $response = $this->post(route('pdf.generate'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'matricola' => '12345',
            'causale' => ['ferie HFEG'],
            'dalle_ore' => ['08:00'],
            'dal_giorno' => ['2026-07-21'],
            'alle_ore' => ['17:00'],
            'al_giorno' => ['2026-07-21'],
            'note' => 'Richiesta concordata con il responsabile.',
            'causale_presenza' => ['straordinario giornaliero'],
            'presenza_dalle_ore' => ['18:00'],
            'presenza_alle_ore' => ['20:00'],
            'presenza_giorno' => ['2026-07-22'],
            'presenza_motivo' => ['Commessa 42'],
        ]);

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('richiesta-assenza-rossi-mario.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
