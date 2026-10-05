<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        Route::get('/_testing/errors/{status}', function (int $status) {
            abort($status, 'private exception detail');
        });

        Route::get('/_testing/validation', function (\Illuminate\Http\Request $request) {
            return $request->validate(['value' => ['required']]);
        });
    }

    public function test_supported_http_errors_render_their_status_pages(): void
    {
        foreach ([401, 403, 404, 419, 429, 500, 503] as $status) {
            $response = $this->get("/_testing/errors/{$status}")
                ->assertStatus($status)
                ->assertViewIs("errors.{$status}")
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'DENY');

            if ($status === 500) {
                $response->assertDontSee('@extends');
            }
        }
    }

    public function test_unlisted_http_status_uses_the_generic_error_page(): void
    {
        $this->get('/_testing/errors/418')
            ->assertStatus(418)
            ->assertViewIs('errors.generic');
    }

    public function test_json_errors_do_not_expose_exception_details(): void
    {
        $this->getJson('/_testing/errors/500')
            ->assertStatus(500)
            ->assertJsonPath('error', true)
            ->assertJsonPath('status', 500)
            ->assertJsonMissing(['message' => 'private exception detail']);
    }

    public function test_json_validation_errors_keep_the_standard_unprocessable_status(): void
    {
        $this->getJson('/_testing/validation')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    }

    public function test_sanctum_api_authentication_errors_return_401_json(): void
    {
        $this->getJson('/api/user')
            ->assertUnauthorized()
            ->assertJsonPath('error', true)
            ->assertJsonPath('status', 401);
    }

    public function test_error_back_button_ignores_external_referers(): void
    {
        $this->withHeader('referer', 'https://untrusted.example/path')
            ->get('/_testing/errors/404')
            ->assertStatus(404)
            ->assertSee('href="'.route('showLogin').'"', false);
    }

    public function test_authenticated_error_renders_the_shared_app_layout(): void
    {
        $user = User::factory()->create(['username' => 'error.viewer']);

        $this->actingAs($user, 'web')->get('/_testing/errors/403')
            ->assertForbidden()
            ->assertSee('app-shell', false)
            ->assertSee('sidebar', false)
            ->assertSee('Back to home')
            ->assertDontSee('private exception detail');
    }
}