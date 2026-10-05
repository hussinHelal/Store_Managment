<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'accounts.superadmin.username' => 'root.user',
            'accounts.superadmin.password' => 'super-secret-pass',
            'accounts.superadmin.name' => 'Root User',
            'accounts.admin.username' => 'store.admin',
            'accounts.admin.password' => 'admin-secret-pass',
            'accounts.admin.name' => 'Store Admin',
        ]);
        $this->artisan('app:sync-accounts')->assertExitCode(0);
    }

    public function test_profile_edit_is_limited_to_the_signed_in_user(): void
    {
        $user = User::where('system_account', 'admin')->firstOrFail();
        $other = User::factory()->create(['username' => 'another.staff']);

        $bufferLevel = ob_get_level();
        $this->actingAs($user, 'web')->get(route('profile.edit', $user))->assertOk();
        $this->assertSame($bufferLevel, ob_get_level());
            $this->actingAs($user, 'web')->getJson(route('profile.edit', $other))->assertNotFound();
            $this->assertSame($bufferLevel, ob_get_level());
    }

    public function test_password_change_requires_current_password_and_revokes_tokens(): void
    {
        $user = User::where('system_account', 'admin')->firstOrFail();
        $user->forceFill(['password' => 'current-password'])->save();
        $token = $user->createToken('profile-test', ['user:read']);

        $this->actingAs($user, 'web')->from(route('profile.edit', $user))->put(route('profile.update'), [
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-password-value',
            'password_confirmation' => 'new-password-value',
        ])->assertRedirect(route('profile.edit', $user))->assertSessionHasErrors('current_password');

        $this->put(route('profile.update'), [
            'username' => $user->username,
            'name' => 'Updated Admin',
            'email' => $user->email,
            'current_password' => 'current-password',
            'password' => 'new-password-value',
            'password_confirmation' => 'new-password-value',
        ])->assertRedirect(route('profile.index'));

        $user->refresh();
        $this->assertSame('Updated Admin', $user->name);
        $this->assertTrue(Hash::check('new-password-value', $user->password));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }
}