<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyConsentTest extends TestCase
{
    public function test_home_page_shows_the_privacy_consent_dialog(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Consenso al trattamento dei dati personali')
            ->assertSee('privacy-consent-checkbox')
            ->assertSee(route('privacy.refused'));
    }

    public function test_refusal_page_explains_that_the_app_cannot_be_used(): void
    {
        $this->get(route('privacy.refused'))
            ->assertOk()
            ->assertSee('Non puoi utilizzare Appermesso');
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
