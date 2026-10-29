<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_director_is_redirected_to_kardex(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Director', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Director');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/kardex');
    }

    public function test_docente_is_redirected_to_mis_cursos(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Docente', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Docente');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/mis-cursos');
    }

    public function test_estudiante_is_redirected_to_mi_panel(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Estudiante');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/mi-panel');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_demo_accounts_can_authenticate(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Docente', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);

        $this->seed(\Database\Seeders\DemoAccountsSeeder::class);

        $docenteRes = $this->post('/login', [
            'email' => 'docente1@academico.test',
            'password' => 'password',
        ]);
        $docenteRes->assertRedirect('/mis-cursos');

        $this->post('/logout');

        $estudianteRes = $this->post('/login', [
            'email' => 'estudiante1@academico.test',
            'password' => 'password',
        ]);
        $estudianteRes->assertRedirect('/mi-panel');
    }
}
