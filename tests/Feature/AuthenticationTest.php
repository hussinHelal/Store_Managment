<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_user_can_log_in_with_username_and_session_is_regenerated(): void
    {
        $user = User::factory()->create([
            'username' => 'cashier.one',
            'password' => Hash::make('correct-password'),
        ]);

        $this->get('/showLogin')->assertOk();
        $oldSessionId = session()->getId();

        $this->post('/login', [
            'username' => 'cashier.one',
            'password' => 'correct-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_remember_me_login_sets_a_remember_token(): void
    {
        $user = User::factory()->create([
            'username' => 'remember.user',
            'password' => Hash::make('correct-password'),
        ]);

        $this->get('/showLogin')
            ->assertOk()
            ->assertSee('name="remember" id="remember" value="1"', false);

        $this->post('/login', [
            'username' => 'remember.user',
            'password' => 'correct-password',
            'remember' => 'on',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotEmpty($user->fresh()->remember_token);
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        User::factory()->create([
            'username' => 'disabled.user',
            'password' => Hash::make('correct-password'),
            'is_active' => false,
        ]);

        $this->from('/showLogin')->post('/login', [
            'username' => 'disabled.user',
            'password' => 'correct-password',
        ])->assertRedirect('/showLogin')->assertSessionHasErrors('username');

        $this->assertGuest('web');
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create(['username' => 'cashier.one']);
        $this->actingAs($user, 'web');

        $this->post('/logout')->assertRedirect(route('showLogin'));

        $this->assertGuest('web');
    }

    public function test_registration_urls_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
        $this->get('/showRegister')->assertNotFound();
    }
}
