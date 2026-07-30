<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyConsentTest extends TestCase
{
    public function test_home_page_shows_the_initial_access_choice(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Come vuoi continuare?')
            ->assertSee('Accedi')
            ->assertSee('Registrati')
            ->assertSee('Continua come ospite')
            ->assertSee(route('home', ['ospite' => 1]), false)
            ->assertDontSee('privacy-consent-checkbox');
    }

    public function test_guest_choice_shows_the_privacy_consent_dialog(): void
    {
        $this->get(route('home', ['ospite' => 1]))
            ->assertOk()
            ->assertSee('Consenso al trattamento dei dati personali')
            ->assertSee('Dati memorizzati per ogni PDF generato.')
            ->assertSee('Dati memorizzati se crei un account.')
            ->assertSee('matricola, centro di costo')
            ->assertSee('cookie pubblicitari o di profilazione')
            ->assertSee('Il PDF viene inviato direttamente al download e non viene archiviato')
            ->assertSee('privacy-consent-checkbox')
            ->assertSee(route('privacy.refused'));
    }

    public function test_refusal_page_explains_that_the_app_cannot_be_used(): void
    {
        $this->get(route('privacy.refused'))
            ->assertOk()
            ->assertSee('Non puoi utilizzare Appermesso');
    }

    public function test_guest_can_open_login_and_registration_without_accepting_policy(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Bentornato');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Crea il tuo account');
    }

    public function test_guest_sees_policy_again_after_reloading_guest_mode(): void
    {
        $this->postJson(route('privacy.accept'))
            ->assertOk();

        $this->get(route('home', ['ospite' => 1]))
            ->assertOk()
            ->assertSee('privacy-consent-checkbox')
            ->assertSee('name="privacy_consent" value=""', false);
    }

    public function test_pdf_generation_requires_explicit_privacy_consent(): void
    {
        $this->post(route('pdf.generate'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'causale' => ['ferie HFEG'],
        ])->assertSessionHasErrors('privacy_consent');
    }
}
