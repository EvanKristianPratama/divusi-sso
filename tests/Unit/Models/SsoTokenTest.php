<?php

namespace Tests\Unit\Models;

use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoTokenTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ==================== Relationships ====================

    public function test_belongs_to_user(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertInstanceOf(User::class, $token->user);
        $this->assertEquals($this->user->id, $token->user->id);
    }

    // ==================== Scopes ====================

    public function test_scope_valid_excludes_used_tokens(): void
    {
        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
            'used_at' => now(), // Used
        ]);

        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('b', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
            'used_at' => null, // Not used
        ]);

        $validTokens = SsoToken::valid()->get();

        $this->assertEquals(1, $validTokens->count());
        $this->assertEquals(str_repeat('b', 64), $validTokens->first()->token);
    }

    public function test_scope_valid_excludes_expired_tokens(): void
    {
        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('b', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5), // Valid
        ]);

        $validTokens = SsoToken::valid()->get();

        $this->assertEquals(1, $validTokens->count());
    }

    public function test_scope_by_token(): void
    {
        $token1 = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('b', 64),
            'app' => 'pmo',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $found = SsoToken::byToken(str_repeat('a', 64))->first();

        $this->assertEquals($token1->id, $found->id);
    }

    public function test_scope_by_app(): void
    {
        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('b', 64),
            'app' => 'pmo',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $cobitTokens = SsoToken::byApp('cobit')->get();

        $this->assertEquals(1, $cobitTokens->count());
        $this->assertEquals('cobit', $cobitTokens->first()->app);
    }

    // ==================== Helpers ====================

    public function test_isValid_returns_true_for_valid_token(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertTrue($token->isValid());
    }

    public function test_isValid_returns_false_for_used_token(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
            'used_at' => now(),
        ]);

        $this->assertFalse($token->isValid());
    }

    public function test_isValid_returns_false_for_expired_token(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->subMinutes(1),
        ]);

        $this->assertFalse($token->isValid());
    }

    public function test_markUsed_sets_used_at(): void
    {
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertNull($token->used_at);

        $token->markUsed();
        $token->refresh();

        $this->assertNotNull($token->used_at);
    }
}
