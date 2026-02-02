<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use App\Services\Firebase\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock FirebaseService
        $mockFirebase = Mockery::mock(FirebaseService::class);
        $mockFirebase->shouldReceive('verifyToken')
            ->andReturn([
                'firebase_uid' => 'test_firebase_uid_123',
                'email' => 'test@example.com',
                'name' => 'Test User',
            ]);

        $this->app->instance(FirebaseService::class, $mockFirebase);
        $this->service = app(AuthService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ==================== loginWithFirebase ====================

    public function test_loginWithFirebase_creates_new_user_if_not_exists(): void
    {
        // Act
        $result = $this->service->loginWithFirebase('fake_token');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Login berhasil', $result['message']);
        $this->assertDatabaseHas('mst_users', [
            'firebase_uid' => 'test_firebase_uid_123',
            'email' => 'test@example.com',
        ]);
    }

    public function test_loginWithFirebase_uses_existing_user(): void
    {
        // Arrange
        $existingUser = User::factory()->create([
            'firebase_uid' => 'test_firebase_uid_123',
            'email' => 'test@example.com',
            'status' => 'active',
        ]);

        // Act
        $result = $this->service->loginWithFirebase('fake_token');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals($existingUser->id, $result['user']['id']);
        $this->assertEquals(1, User::count()); // No new user created
    }

    public function test_loginWithFirebase_fails_for_inactive_user(): void
    {
        // Arrange
        User::factory()->create([
            'firebase_uid' => 'test_firebase_uid_123',
            'status' => 'suspended',
        ]);

        // Act
        $result = $this->service->loginWithFirebase('fake_token');

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('blocked', $result['status']);
    }

    public function test_loginWithFirebase_logs_user_in(): void
    {
        // Act
        $this->service->loginWithFirebase('fake_token');

        // Assert
        $this->assertTrue(Auth::check());
        $this->assertEquals('test@example.com', Auth::user()->email);
    }

    // ==================== registerWithFirebase ====================

    public function test_registerWithFirebase_creates_new_user(): void
    {
        // Act
        $result = $this->service->registerWithFirebase('fake_token');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Registrasi berhasil', $result['message']);
        $this->assertDatabaseHas('mst_users', [
            'firebase_uid' => 'test_firebase_uid_123',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_registerWithFirebase_fails_if_user_exists(): void
    {
        // Arrange
        User::factory()->create([
            'firebase_uid' => 'test_firebase_uid_123',
        ]);

        // Act
        $result = $this->service->registerWithFirebase('fake_token');

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('exists', $result['status']);
    }

    // ==================== logout ====================

    public function test_logout_clears_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        Auth::login($user);
        $this->assertTrue(Auth::check());

        // Act
        $this->service->logout();

        // Assert
        $this->assertFalse(Auth::check());
    }

    // ==================== getCurrentUser ====================

    public function test_getCurrentUser_returns_user_data_when_authenticated(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin',
        ]);
        Auth::login($user);

        // Act
        $result = $this->service->getCurrentUser();

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals('admin', $result['role']);
    }

    public function test_getCurrentUser_returns_null_when_not_authenticated(): void
    {
        // Act
        $result = $this->service->getCurrentUser();

        // Assert
        $this->assertNull($result);
    }
}
