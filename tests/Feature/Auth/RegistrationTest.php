<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSee('Username');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', $this->validData());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['username' => 'test_user', 'email' => 'test@example.com']);
    }

    public function test_password_is_stored_hashed(): void
    {
        $this->post('/register', $this->validData());

        $user = User::where('username', 'test_user')->first();
        $this->assertNotSame('password123', $user->password);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        User::factory()->create(['username' => 'taken_name']);

        $this->post('/register', $this->validData(['username' => 'taken_name']))
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_invalid_username_is_rejected(): void
    {
        foreach (['ab', 'has space', 'bad-name!', str_repeat('a', 31)] as $bad) {
            $this->post('/register', $this->validData(['username' => $bad]))
                ->assertSessionHasErrors('username');
        }
        $this->assertGuest();
    }

    public function test_password_must_be_confirmed(): void
    {
        $this->post('/register', $this->validData(['password_confirmation' => 'different']))
            ->assertSessionHasErrors('password');
    }
}
