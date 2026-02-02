<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FirebaseSetupTest extends TestCase
{
    /**
     * Test database connection
     */
    public function test_database_connection(): void
    {
        $count = User::count();
        $this->assertIsInt($count);
    }

    /**
     * Test user factory
     */
    public function test_user_factory(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertNotNull($user->id);
        $this->assertNotNull($user->firebase_uid);
        $this->assertEquals('active', $user->status);
    }

    /**
     * Test user isActive method
     */
    public function test_user_is_active(): void
    {
        $activeUser = User::factory()->create(['status' => 'active']);
        $suspendedUser = User::factory()->create(['status' => 'suspended']);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($suspendedUser->isActive());
    }

    /**
     * Test user isAdmin method
     */
    public function test_user_is_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test user soft delete
     */
    public function test_user_soft_delete(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();
        $this->assertSoftDeleted('mst_users', ['id' => $userId]);
    }

    /**
     * Test firebase config
     */
    public function test_firebase_config(): void
    {
        $config = config('firebase');
        
        $this->assertArrayHasKey('project_id', $config);
        $this->assertArrayHasKey('auth_domain', $config);
        $this->assertArrayHasKey('api_key', $config);
    }

    /**
     * Test firebase service registered
     */
    public function test_firebase_service_registered(): void
    {
        $this->assertTrue(
            app()->bound(\App\Services\Firebase\FirebaseService::class)
        );
    }
}
