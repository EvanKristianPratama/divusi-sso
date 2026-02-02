<?php

namespace Tests\Feature;

use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'status' => 'active',
        ]);
    }

    // ==================== POST /api/sso/validate ====================

    public function test_validate_with_valid_token_returns_user(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/sso/validate', [
            'token' => $token->token,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'user' => [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                ],
            ]);
    }

    public function test_validate_with_invalid_token_returns_401(): void
    {
        $response = $this->postJson('/api/sso/validate', [
            'token' => str_repeat('x', 64),
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_validate_with_expired_token_returns_401(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->postJson('/api/sso/validate', [
            'token' => $token->token,
        ]);

        $response->assertStatus(401);
    }

    public function test_validate_with_used_token_returns_401(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
            'used_at' => now(),
        ]);

        $response = $this->postJson('/api/sso/validate', [
            'token' => $token->token,
        ]);

        $response->assertStatus(401);
    }

    public function test_validate_requires_token(): void
    {
        $response = $this->postJson('/api/sso/validate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_validate_requires_64_char_token(): void
    {
        $response = $this->postJson('/api/sso/validate', [
            'token' => 'short_token',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    // ==================== POST /api/sso/generate ====================

    public function test_generate_requires_authentication(): void
    {
        $response = $this->postJson('/api/sso/generate', [
            'app' => 'cobit',
        ]);

        $response->assertStatus(401);
    }

    public function test_generate_with_valid_app_returns_token(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/sso/generate', [
                'app' => 'cobit',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'redirect_url',
                'expires_at',
                'status',
            ]);

        $this->assertDatabaseHas('trs_sso_tokens', [
            'user_id' => $this->user->id,
            'app' => 'cobit',
        ]);
    }

    public function test_generate_with_invalid_app_returns_404(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/sso/generate', [
                'app' => 'nonexistent',
            ]);

        $response->assertStatus(404);
    }

    public function test_generate_requires_app_parameter(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/sso/generate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['app']);
    }

    // ==================== GET /sso/redirect/{app} ====================

    public function test_redirect_requires_authentication(): void
    {
        $response = $this->get('/sso/redirect/cobit');

        $response->assertRedirect('/login');
    }

    public function test_redirect_with_valid_app_redirects_to_callback(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/sso/redirect/cobit');

        $response->assertRedirect();
        
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContains('token=', $redirectUrl);
        $this->assertStringContains(config('sso.apps.cobit.callback_url'), $redirectUrl);
    }

    public function test_redirect_with_invalid_app_returns_back(): void
    {
        $response = $this->actingAs($this->user)
            ->from('/dashboard')
            ->get('/sso/redirect/nonexistent');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Helper untuk check substring
     */
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '$haystack' contains '$needle'"
        );
    }
}
