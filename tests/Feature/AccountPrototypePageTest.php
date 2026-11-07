<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AccountPrototypePageTest extends TestCase
{
    public function test_public_auth_forms_are_available(): void
    {
        $this->get(route('account.login'))->assertOk()->assertSee('Masuk JasaWarga');
        $this->get(route('account.register'))->assertOk()->assertSee('Daftar sebagai Warga');
        $this->get(route('partner.register'))->assertOk()->assertSee('Daftar Mitra Jasa');
        $this->get(route('account.forgot'))->assertOk()->assertSee('Lupa password?');
    }

    public function test_account_pages_now_require_a_session(): void
    {
        $this->get('/akun')->assertRedirect(route('account.login'));
        $this->get('/akun/edit')->assertRedirect(route('account.login'));
        $this->get('/akun/alamat')->assertRedirect(route('account.login'));
    }

    public function test_authenticated_citizen_can_see_real_account_summary(): void
    {
        $this->actingAs(new User([
            'name' => 'Warga Pengujian', 'email' => 'warga@example.test',
            'role' => 'citizen', 'account_status' => 'active',
        ]));
        $this->get('/akun')->assertOk()->assertSee('Warga Pengujian')->assertSee('PostgreSQL');
        $this->get('/akun/alamat')->assertOk()->assertSee('belum dipindahkan');
    }
}
