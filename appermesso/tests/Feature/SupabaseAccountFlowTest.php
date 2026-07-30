<?php

namespace Tests\Feature;

use App\Services\SupabaseSession;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseAccountFlowTest extends TestCase
{
    private const USER_ID = '2e7dfb2e-e616-4ad1-991b-0d7c8c42eabe';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.supabase.url' => 'https://example.supabase.co',
            'services.supabase.anon_key' => 'test-anon-key',
            'services.supabase.secret_key' => '',
        ]);

        Http::preventStrayRequests();
    }

    public function test_guest_can_still_open_the_form_and_generate_a_pdf(): void
    {
        Http::fake();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Richiesta di assenza')
            ->assertSee('Cosa vuoi comunicare?')
            ->assertSee('data-request-type="absence"', false)
            ->assertSee('data-request-type="presence"', false)
            ->assertSee('data-request-type="missing"', false)
            ->assertSee('id="form-inline-error"', false);

        $this->post(route('pdf.generate'), [
            'privacy_consent' => '1',
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'causale' => ['ferie HFEG'],
        ])->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        Http::assertNothingSent();
    }

    public function test_login_establishes_a_supabase_session_immediately(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/token?grant_type=password' => Http::response(
                $this->authPayload(),
            ),
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([]),
        ]);

        $this->withSession(['privacy_consent_seen' => true])
            ->post(route('login.attempt'), [
                'email' => 'mario@example.com',
                'password' => 'password123',
            ])->assertRedirect(route('profile.show'))
            ->assertSessionHas(SupabaseSession::SESSION_KEY.'.user.id', self::USER_ID)
            ->assertSessionHas('supabase_user.email', 'mario@example.com');

        Http::assertSent(fn (Request $request): bool => $request->url()
            === 'https://example.supabase.co/auth/v1/token?grant_type=password'
            && $request['email'] === 'mario@example.com'
            && $request['password'] === 'password123'
            && $request->hasHeader('apikey', 'test-anon-key'));
    }

    public function test_login_redirects_to_the_form_when_profile_data_already_exists(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/token?grant_type=password' => Http::response(
                $this->authPayload(),
            ),
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                $this->profile(),
            ]),
        ]);

        $this->withSession(['privacy_consent_seen' => true])
            ->post(route('login.attempt'), [
                'email' => 'mario@example.com',
                'password' => 'password123',
            ])->assertRedirect(route('home'));
    }

    public function test_login_redirects_to_profile_on_first_access(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/token?grant_type=password' => Http::response(
                $this->authPayload(),
            ),
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([]),
        ]);

        $this->withSession(['privacy_consent_seen' => true])
            ->post(route('login.attempt'), [
                'email' => 'mario@example.com',
                'password' => 'password123',
            ])->assertRedirect(route('profile.show'));
    }

    public function test_registration_establishes_a_session_without_requiring_a_second_login(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/signup' => Http::response($this->authPayload()),
        ]);

        $this->withSession(['privacy_consent_seen' => true])
            ->post(route('register.store'), [
                'email' => 'mario@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertRedirect(route('profile.show'))
            ->assertSessionHas(SupabaseSession::SESSION_KEY.'.user.id', self::USER_ID);
    }

    public function test_registration_signs_in_immediately_when_signup_returns_no_session(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/signup' => Http::response([
                'user' => [
                    'id' => self::USER_ID,
                    'email' => 'mario@example.com',
                ],
            ]),
            'https://example.supabase.co/auth/v1/token?grant_type=password' => Http::response(
                $this->authPayload(),
            ),
        ]);

        $this->withSession(['privacy_consent_seen' => true])
            ->post(route('register.store'), [
                'email' => 'mario@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertRedirect(route('profile.show'))
            ->assertSessionHas(SupabaseSession::SESSION_KEY.'.user.id', self::USER_ID);

        Http::assertSentCount(2);
    }

    public function test_password_recovery_uses_a_non_enumerating_response(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/recover*' => Http::response(status: 500),
        ]);

        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'unknown@example.com']);

        $response->assertRedirect(route('password.request'))
            ->assertSessionHas(
                'status',
                'Se esiste un account associato, riceverai le istruzioni per reimpostare la password.',
            );
    }

    public function test_password_recovery_sends_the_local_callback_as_redirect_query_parameter(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/recover*' => Http::response(),
        ]);

        $this->post(route('password.email'), [
            'email' => 'mario@example.com',
        ])->assertRedirect();

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://example.supabase.co/auth/v1/recover'
                .'?redirect_to='.rawurlencode(route('password.reset'))
            && $request['email'] === 'mario@example.com'
            && ! array_key_exists('redirect_to', $request->data()));
    }

    public function test_recovery_link_establishes_a_session_and_allows_password_reset(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/verify' => Http::response($this->authPayload()),
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'id' => self::USER_ID,
                'email' => 'mario@example.com',
            ]),
        ]);

        $this->get(route('password.reset', [
            'token_hash' => 'recovery-token',
            'type' => 'recovery',
        ]))->assertOk()
            ->assertSessionHas(SupabaseSession::SESSION_KEY.'.user.id', self::USER_ID);

        $this->post(route('password.update'), [
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('profile.show'))
            ->assertSessionHas(SupabaseSession::SESSION_KEY.'.password_recovery', false);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
            && $request->url() === 'https://example.supabase.co/auth/v1/user'
            && $request['password'] === 'new-password123'
            && $request->hasHeader('Authorization', 'Bearer access-token'));
    }

    public function test_standard_supabase_recovery_fragment_can_establish_the_server_session(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'id' => self::USER_ID,
                'email' => 'mario@example.com',
            ]),
        ]);

        $this->postJson(route('password.session'), [
            'access_token' => 'recovery-access-token',
            'refresh_token' => 'recovery-refresh-token',
            'expires_in' => 3600,
            'type' => 'recovery',
        ])->assertOk()
            ->assertJsonPath('redirect', route('password.reset'))
            ->assertSessionHas(SupabaseSession::SESSION_KEY.'.password_recovery', true);

        $this->get(route('password.reset'))
            ->assertOk()
            ->assertSee('Scegli una nuova password');

        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && $request->url() === 'https://example.supabase.co/auth/v1/user'
            && $request->hasHeader('Authorization', 'Bearer recovery-access-token'));
    }

    public function test_normal_authenticated_session_cannot_change_password_through_recovery_endpoint(): void
    {
        Http::fake();

        $this->withSession($this->authenticatedSession())
            ->post(route('password.update'), [
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        Http::assertNothingSent();
    }

    public function test_profile_routes_are_protected_from_guests(): void
    {
        $this->get(route('profile.show'))
            ->assertRedirect(route('login'));

        $this->put(route('profile.update'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_load_their_saved_profile(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                $this->profile(),
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Mario')
            ->assertSee('Rossi')
            ->assertSee('MAT-42')
            ->assertSee('Produzione');

        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && str_starts_with(
                $request->url(),
                'https://example.supabase.co/rest/v1/profiles?',
            )
            && str_contains($request->url(), 'id=eq.'.self::USER_ID)
            && $request->hasHeader('Authorization', 'Bearer access-token'));
    }

    public function test_authenticated_user_can_save_exactly_the_seven_profile_fields(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                $this->profile(),
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->put(route('profile.update'), [
                ...$this->profile(),
                'unexpected' => 'must not be persisted',
            ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('status', 'Profilo aggiornato.');

        Http::assertSent(function (Request $request): bool {
            if ($request->method() !== 'POST') {
                return false;
            }

            return $request->url()
                === 'https://example.supabase.co/rest/v1/profiles?on_conflict=id'
                && $request['id'] === self::USER_ID
                && $request['nome'] === 'Mario'
                && $request['cognome'] === 'Rossi'
                && $request['matricola'] === 'MAT-42'
                && $request['centro_costo'] === 'CC-10'
                && $request['livello'] === 'C2'
                && $request['qualifica'] === 'Impiegato'
                && $request['ente'] === 'Produzione'
                && ! array_key_exists('unexpected', $request->data())
                && $request->hasHeader('Authorization', 'Bearer access-token');
        });
    }

    public function test_saved_profile_prefills_the_pdf_form_for_authenticated_user(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                $this->profile(),
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('value="Mario"', false)
            ->assertSee('value="Rossi"', false)
            ->assertSee('value="MAT-42"', false)
            ->assertSee('value="CC-10"', false)
            ->assertSee('value="Produzione"', false)
            ->assertDontSee('Come vuoi continuare?');
    }

    public function test_authenticated_user_without_saved_consent_must_accept_it_again(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                [...$this->profile(), 'privacy_consent_at' => null],
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('privacy-consent-checkbox')
            ->assertSee(route('privacy.accept'));
    }

    public function test_authenticated_user_can_save_privacy_consent_in_their_profile(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                [...$this->profile(), 'privacy_consent_at' => now()->toIso8601String()],
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->postJson(route('privacy.accept'))
            ->assertOk()
            ->assertJson(['accepted' => true]);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://example.supabase.co/rest/v1/profiles?on_conflict=id'
            && $request['id'] === self::USER_ID
            && is_string($request['privacy_consent_at'])
            && $request->hasHeader('Authorization', 'Bearer access-token'));
    }

    public function test_authenticated_user_can_revoke_consent_from_the_profile(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                [...$this->profile(), 'privacy_consent_at' => null],
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->delete(route('privacy.revoke'))
            ->assertRedirect(route('privacy.refused'))
            ->assertSessionMissing(SupabaseSession::SESSION_KEY);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://example.supabase.co/rest/v1/profiles?on_conflict=id'
            && $request['id'] === self::USER_ID
            && array_key_exists('privacy_consent_at', $request->data())
            && $request['privacy_consent_at'] === null);
    }

    public function test_saved_consent_is_shown_in_profile_and_skips_the_dialog(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response([
                [
                    ...$this->profile(),
                    'privacy_consent_at' => '2026-07-29T10:30:00+00:00',
                ],
            ]),
        ]);

        $this->withSession($this->authenticatedSession())
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Revoca consenso')
            ->assertDontSee('privacy-consent-checkbox');
    }

    public function test_logout_clears_local_session_even_when_supabase_is_unavailable(): void
    {
        Http::fake([
            'https://example.supabase.co/auth/v1/logout' => Http::response(status: 500),
        ]);

        $this->withSession($this->authenticatedSession())
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing(SupabaseSession::SESSION_KEY)
            ->assertSessionMissing('supabase_user');

        $this->get(route('profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_profile_failure_does_not_disable_the_guest_capable_home_form(): void
    {
        Http::fake([
            'https://example.supabase.co/rest/v1/profiles*' => Http::response(status: 500),
        ]);

        $this->withSession($this->authenticatedSession())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Richiesta di assenza');
    }

    /**
     * @return array<string, mixed>
     */
    private function authPayload(): array
    {
        return [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3600,
            'user' => [
                'id' => self::USER_ID,
                'email' => 'mario@example.com',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function authenticatedSession(): array
    {
        return [
            SupabaseSession::SESSION_KEY => [
                ...$this->authPayload(),
                'expires_at' => time() + 3600,
            ],
            'supabase_user' => $this->authPayload()['user'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function profile(): array
    {
        return [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'matricola' => 'MAT-42',
            'centro_costo' => 'CC-10',
            'livello' => 'C2',
            'qualifica' => 'Impiegato',
            'ente' => 'Produzione',
        ];
    }
}
