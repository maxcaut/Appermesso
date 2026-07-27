<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
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

    public function test_pdf_requires_at_least_one_absence_or_presence_cause(): void
    {
        $response = $this->from('/')->post(route('pdf.generate'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
        ]);

        $response->assertRedirect('/')
            ->assertSessionHasErrors(['causale', 'causale_presenza']);
    }

    public function test_personal_data_is_uppercased_for_the_pdf_view(): void
    {
        $response = $this->post(route('pdf.generate'), [
            'nome' => 'Màrio',
            'cognome' => 'Rossi',
            'matricola' => 'ab123',
            'centro_costo' => 'Centro nord',
            'livello' => 'quadro',
            'qualifica' => 'tecnico',
            'ente' => 'Unità operativa',
            'causale' => ['ferie HFEG'],
        ]);

        $response->assertOk();

        $pdf = $response->getContent();
        $this->assertStringNotContainsString('Màrio', $pdf);
        $this->assertStringNotContainsString('Centro nord', $pdf);
    }

    public function test_name_and_last_name_are_required(): void
    {
        $response = $this->from('/')->post(route('pdf.generate'), [
            'causale' => ['ferie HFEG'],
        ]);

        $response->assertRedirect('/')
            ->assertSessionHasErrors(['nome', 'cognome']);
    }

    public function test_pdf_usage_is_recorded_in_supabase(): void
    {
        config([
            'services.supabase.url' => 'https://example.supabase.co',
            'services.supabase.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'https://example.supabase.co/rest/v1/app_usage' => Http::response(status: 201),
        ]);

        $response = $this->post(route('pdf.generate'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'causale' => ['ferie HFEG'],
        ]);

        $response->assertOk();

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://example.supabase.co/rest/v1/app_usage'
                && $request->hasHeader('apikey', 'test-secret')
                && $request['first_name'] === 'MARIO'
                && $request['last_name'] === 'ROSSI'
                && count($request->data()) === 2;
        });
    }

    public function test_supabase_failure_does_not_block_pdf_generation(): void
    {
        config([
            'services.supabase.url' => 'https://example.supabase.co',
            'services.supabase.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'https://example.supabase.co/rest/v1/app_usage' => Http::response(status: 500),
        ]);

        $response = $this->post(route('pdf.generate'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'causale' => ['ferie HFEG'],
        ]);

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
