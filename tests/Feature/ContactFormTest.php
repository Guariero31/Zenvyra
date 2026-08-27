<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Mail\ContactFormMail;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['te_slug' => 'loja-teste', 'te_ativo' => true]);

        $this->admin = User::factory()->create(['email' => 'admin@loja.com']);
        $this->tenant->users()->attach($this->admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        TenantTheme::firstOrCreate(
            ['tenant_id' => $this->tenant->getKey()],
            [...TenantTheme::defaults($this->tenant->getKey()), 'store_config' => null],
        );
    }

    public function test_contact_page_renders(): void
    {
        $response = $this->get("/{$this->tenant->te_slug}/contato");

        $response->assertOk();
        $response->assertSee('Enviar mensagem');
    }

    public function test_valid_submission_sends_mail_to_admin(): void
    {
        Mail::fake();

        $this->post("/{$this->tenant->te_slug}/contato", [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'message' => 'Gostaria de saber mais sobre os produtos.',
        ])->assertRedirect("/{$this->tenant->te_slug}/contato");

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->senderName === 'João Silva'
                && $mail->senderEmail === 'joao@example.com'
                && str_contains($mail->message, 'Gostaria de saber');
        });
    }

    public function test_mail_is_sent_to_store_config_email_when_set(): void
    {
        Mail::fake();

        /** @var TenantTheme $theme */
        $theme = TenantTheme::where('tenant_id', $this->tenant->getKey())->first();
        $theme->update(['store_config' => ['email' => 'contato@lojateste.com']]);

        $this->post("/{$this->tenant->te_slug}/contato", [
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'message' => 'Preciso de informações sobre entrega.',
        ]);

        Mail::assertSent(ContactFormMail::class, fn (ContactFormMail $m) => in_array('contato@lojateste.com', array_column($m->to, 'address'), true)
        );
    }

    public function test_mail_is_sent_to_admin_email_when_no_store_config_email(): void
    {
        Mail::fake();

        $this->post("/{$this->tenant->te_slug}/contato", [
            'name' => 'Carlos',
            'email' => 'carlos@example.com',
            'message' => 'Tenho uma dúvida sobre pagamento.',
        ]);

        Mail::assertSent(ContactFormMail::class, fn (ContactFormMail $m) => in_array('admin@loja.com', array_column($m->to, 'address'), true)
        );
    }

    public function test_validation_fails_with_missing_fields(): void
    {
        Mail::fake();

        $this->post("/{$this->tenant->te_slug}/contato", [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_validation_fails_with_short_message(): void
    {
        Mail::fake();

        $this->post("/{$this->tenant->te_slug}/contato", [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'message' => 'Oi',
        ])->assertSessionHasErrors(['message']);

        Mail::assertNothingSent();
    }

    public function test_validation_fails_with_invalid_email(): void
    {
        Mail::fake();

        $this->post("/{$this->tenant->te_slug}/contato", [
            'name' => 'Ana',
            'email' => 'nao-e-email',
            'message' => 'Mensagem suficientemente longa para passar na validação.',
        ])->assertSessionHasErrors(['email']);

        Mail::assertNothingSent();
    }

    public function test_success_flash_is_shown_after_submission(): void
    {
        Mail::fake();

        $this->followingRedirects()
            ->post("/{$this->tenant->te_slug}/contato", [
                'name' => 'Pedro',
                'email' => 'pedro@example.com',
                'message' => 'Mensagem de teste do formulário de contato.',
            ])
            ->assertSee('Mensagem enviada');
    }

    public function test_reply_to_is_set_to_sender_email(): void
    {
        Mail::fake();

        $this->post("/{$this->tenant->te_slug}/contato", [
            'name' => 'Lucia',
            'email' => 'lucia@example.com',
            'message' => 'Quero saber sobre disponibilidade dos produtos.',
        ]);

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->envelope()->replyTo[0]->address === 'lucia@example.com';
        });
    }

    public function test_inactive_tenant_returns_404(): void
    {
        $inactive = Tenant::factory()->create(['te_slug' => 'loja-inativa', 'te_ativo' => false]);

        $this->post("/{$inactive->te_slug}/contato", [
            'name' => 'X',
            'email' => 'x@x.com',
            'message' => 'teste',
        ])->assertNotFound();
    }
}
