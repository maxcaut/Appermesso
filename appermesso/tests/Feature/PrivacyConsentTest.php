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

    public function test_guest_must_accept_policy_before_opening_login(): void
    {
        $this->get(route('login'))
            ->assertRedirect(route('home'));

        $this->postJson(route('privacy.accept'))
            ->assertOk()
            ->assertJson([
                'accepted' => true,
                'stored' => false,
            ])
            ->assertSessionHas('privacy_consent_seen', true);

        $this->get(route('login'))
            ->assertOk();
    }

    public function test_guest_sees_policy_again_after_reloading_home(): void
    {
        $this->postJson(route('privacy.accept'))
            ->assertOk();

        $this->get(route('home'))
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
