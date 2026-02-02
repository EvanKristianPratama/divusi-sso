<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    /**
     * Test user factory creates with correct defaults
     */
    public function test_user_factory_defaults(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertNotNull($user->firebase_uid);
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test user factory can create admin
     */
    public function test_user_factory_admin(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->isAdmin());
    }

    /**
     * Test user factory can create suspended user
     */
    public function test_user_factory_suspended(): void
    {
        $user = User::factory()->suspended()->create();

        $this->assertEquals('suspended', $user->status);
        $this->assertFalse($user->isActive());
    }

    /**
     * Test user can be authenticated
     */
    public function test_user_can_be_authenticated(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user);
        $this->assertTrue(auth()->check());
    }

    /**
     * Test authenticated user details
     */
    public function test_authenticated_user_has_correct_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->actingAs($user);
        $authenticatedUser = auth()->user();

        $this->assertEquals('Test User', $authenticatedUser->name);
        $this->assertEquals('test@example.com', $authenticatedUser->email);
        $this->assertEquals('user', $authenticatedUser->role);
    }

    /**
     * Test firebase service is available
     */
    public function test_firebase_service_is_available(): void
    {
        $this->assertTrue(
            app()->bound(\App\Services\Firebase\FirebaseService::class)
        );
    }

    /**
     * Test firebase config loaded
     */
    public function test_firebase_config_is_loaded(): void
    {
        $config = config('firebase');
        
        $this->assertNotNull($config);
        $this->assertArrayHasKey('project_id', $config);
        $this->assertArrayHasKey('auth_domain', $config);
    }

    /**
     * Test user soft delete
     */
    public function test_user_soft_delete_works(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertSoftDeleted('mst_users', ['id' => $userId]);
        $this->assertNull(User::find($userId));
        $this->assertNotNull(User::withTrashed()->find($userId));
    }

    /**
     * Test user restore
     */
    public function test_user_restore_works(): void
    {
        $user = User::factory()->create();
        $user->delete();
        $user->restore();

        $this->assertNotNull(User::find($user->id));
    }

    /**
     * Test multiple users can exist
     */
    public function test_multiple_users_can_exist(): void
    {
        User::factory()->count(5)->create();

        $this->assertEquals(5, User::count());
    }

    /**
     * Test user role can be updated
     */
    public function test_user_role_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $user->update(['role' => 'admin']);

        $this->assertEquals('admin', $user->fresh()->role);
        $this->assertTrue($user->fresh()->isAdmin());
    }

    /**
     * Test user status can be updated
     */
    public function test_user_status_can_be_updated(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $user->update(['status' => 'suspended']);

        $this->assertEquals('suspended', $user->fresh()->status);
        $this->assertFalse($user->fresh()->isActive());
    }
}
