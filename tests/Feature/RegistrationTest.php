<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_page_renders(): void
    {
        $response = $this->get('/cadastro');

        $response->assertOk();
        $response->assertViewIs('register');
    }

    public function test_registration_requires_all_fields(): void
    {
        $response = $this->post('/cadastro', []);

        $response->assertSessionHasErrors(['store_name', 'name', 'email', 'password']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/cadastro', [
            'store_name'            => 'Minha Loja',
            'name'                  => 'João Silva',
            'email'                 => 'joao@example.com',
            'password'              => 'senhasegura123',
            'password_confirmation' => 'diferente',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_registration_requires_minimum_password_length(): void
    {
        $response = $this->post('/cadastro', [
            'store_name'            => 'Minha Loja',
            'name'                  => 'João Silva',
            'email'                 => 'joao@example.com',
            'password'              => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
