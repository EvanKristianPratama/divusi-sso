<?php

namespace Tests\Unit\Models;

use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Relationships ====================

    public function test_has_many_sso_tokens(): void
    {
        $user = User::factory()->create();

        SsoToken::create([
            'user_id' => $user->id,
            'token' => str_repeat('a', 64),
            'app' => 'cobit',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        SsoToken::create([
            'user_id' => $user->id,
            'token' => str_repeat('b', 64),
            'app' => 'pmo',
            'callback_url' => 'http://localhost/callback',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertEquals(2, $user->ssoTokens()->count());
    }

    // ==================== Accessors ====================

    public function test_isActive_returns_true_for_active_user(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->assertTrue($user->isActive());
    }

    public function test_isActive_returns_false_for_suspended_user(): void
    {
        $user = User::factory()->create([
            'status' => 'suspended',
        ]);

        $this->assertFalse($user->isActive());
    }

    public function test_isActive_returns_false_for_soft_deleted_user(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->delete(); // Soft delete

        $this->assertFalse($user->isActive());
    }

    public function test_isAdmin_returns_true_for_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->assertTrue($user->isAdmin());
    }

    public function test_isAdmin_returns_false_for_regular_user(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->assertFalse($user->isAdmin());
    }

    // ==================== Scopes ====================

    public function test_scope_active(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'suspended']);

        $activeUsers = User::active()->get();

        $this->assertEquals(2, $activeUsers->count());
    }

    public function test_scope_by_firebase_uid(): void
    {
        $user = User::factory()->create([
            'firebase_uid' => 'unique_uid_123',
        ]);

        User::factory()->create([
            'firebase_uid' => 'other_uid_456',
        ]);

        $found = User::byFirebaseUid('unique_uid_123')->first();

        $this->assertEquals($user->id, $found->id);
    }

    // ==================== Soft Deletes ====================

    public function test_soft_delete_excludes_from_queries(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->delete();

        $this->assertEquals(1, User::count());
        $this->assertEquals(2, User::withTrashed()->count());
    }

    public function test_soft_deleted_user_can_be_restored(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertEquals(0, User::where('id', $user->id)->count());

        $user->restore();

        $this->assertEquals(1, User::where('id', $user->id)->count());
    }
}
