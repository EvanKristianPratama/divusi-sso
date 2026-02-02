<?php

namespace Tests\Unit\Services;

use App\Models\SsoToken;
use App\Models\User;
use App\Services\SsoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoServiceTest extends TestCase
{
    use RefreshDatabase;

    private SsoService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SsoService::class);
        $this->user = User::factory()->create([
            'status' => 'active',
        ]);
    }

    // ==================== generateToken ====================

    public function test_generateToken_with_valid_app_returns_token(): void
    {
        // Act
        $token = $this->service->generateToken($this->user, 'cobit');

        // Assert
        $this->assertNotNull($token);
        $this->assertInstanceOf(SsoToken::class, $token);
        $this->assertEquals('cobit', $token->app);
        $this->assertEquals($this->user->id, $token->user_id);
        $this->assertEquals(64, strlen($token->token));
        $this->assertDatabaseHas('trs_sso_tokens', [
            'user_id' => $this->user->id,
            'app' => 'cobit',
        ]);
    }

    public function test_generateToken_with_disabled_app_returns_null(): void
    {
        // Act
        $token = $this->service->generateToken($this->user, 'nonexistent_app');

        // Assert
        $this->assertNull($token);
    }

    public function test_generateToken_sets_correct_expiry(): void
    {
        // Act
        $token = $this->service->generateToken($this->user, 'cobit');

        // Assert
        $expectedExpiry = now()->addMinutes(config('sso.token_expiry_minutes', 5));
        $this->assertTrue($token->expires_at->diffInSeconds($expectedExpiry) < 2);
    }

    // ==================== validateToken ====================

    public function test_validateToken_with_valid_token_returns_user_data(): void
    {
        // Arrange
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost:8001/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Act
        $result = $this->service->validateToken($token->token);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($this->user->id, $result['id']);
        $this->assertEquals($this->user->email, $result['email']);
        $this->assertEquals($this->user->name, $result['name']);
    }

    public function test_validateToken_marks_token_as_used(): void
    {
        // Arrange
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('b', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost:8001/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Act
        $this->service->validateToken($token->token);

        // Assert
        $token->refresh();
        $this->assertNotNull($token->used_at);
    }

    public function test_validateToken_with_used_token_returns_null(): void
    {
        // Arrange
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('c', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost:8001/callback',
            'expires_at' => now()->addMinutes(5),
            'used_at' => now(), // Already used
        ]);

        // Act
        $result = $this->service->validateToken($token->token);

        // Assert
        $this->assertNull($result);
    }

    public function test_validateToken_with_expired_token_returns_null(): void
    {
        // Arrange
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('d', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost:8001/callback',
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        // Act
        $result = $this->service->validateToken($token->token);

        // Assert
        $this->assertNull($result);
    }

    public function test_validateToken_with_nonexistent_token_returns_null(): void
    {
        // Act
        $result = $this->service->validateToken(str_repeat('x', 64));

        // Assert
        $this->assertNull($result);
    }

    public function test_validateToken_with_inactive_user_returns_null(): void
    {
        // Arrange
        $inactiveUser = User::factory()->create(['status' => 'suspended']);
        $token = SsoToken::create([
            'user_id' => $inactiveUser->id,
            'token' => str_repeat('e', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost:8001/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Act
        $result = $this->service->validateToken($token->token);

        // Assert
        $this->assertNull($result);
    }

    // ==================== getRedirectUrl ====================

    public function test_getRedirectUrl_returns_correct_url(): void
    {
        // Arrange
        $token = SsoToken::create([
            'user_id' => $this->user->id,
            'token' => str_repeat('f', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost:8001/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Act
        $url = $this->service->getRedirectUrl($token);

        // Assert
        $expectedUrl = 'http://localhost:8001/callback?token=' . str_repeat('f', 64);
        $this->assertEquals($expectedUrl, $url);
    }

    // ==================== getAppConfig ====================

    public function test_getAppConfig_returns_config_for_valid_app(): void
    {
        // Act
        $config = $this->service->getAppConfig('cobit');

        // Assert
        $this->assertNotNull($config);
        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('callback_url', $config);
        $this->assertArrayHasKey('enabled', $config);
    }

    public function test_getAppConfig_returns_null_for_invalid_app(): void
    {
        // Act
        $config = $this->service->getAppConfig('nonexistent');

        // Assert
        $this->assertNull($config);
    }

    // ==================== getEnabledApps ====================

    public function test_getEnabledApps_returns_only_enabled_apps(): void
    {
        // Act
        $apps = $this->service->getEnabledApps();

        // Assert
        $this->assertIsArray($apps);
        foreach ($apps as $app) {
            $this->assertTrue($app['enabled']);
        }
    }
}
