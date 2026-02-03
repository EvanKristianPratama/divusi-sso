<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service untuk User Management
 * Single Responsibility: CRUD users, approval, dan module assignment
 */
class UserService
{
    /**
     * Get paginated users dengan filter
     */
    public function getUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['modules', 'approvedBy'])
            ->withCount('modules');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by approval status
        if (!empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        // Filter by role
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get user detail dengan relations
     */
    public function getUser(int $id): ?User
    {
        return User::with(['modules', 'approvedBy'])->find($id);
    }

    /**
     * Approve user
     */
    public function approveUser(User $user, User $admin): array
    {
        if ($user->isApproved()) {
            return ['success' => false, 'message' => 'User sudah di-approve'];
        }

        $user->approve($admin);

        return ['success' => true, 'message' => 'User berhasil di-approve'];
    }

    /**
     * Reject user
     */
    public function rejectUser(User $user): array
    {
        if ($user->isRejected()) {
            return ['success' => false, 'message' => 'User sudah di-reject'];
        }

        $user->reject();

        return ['success' => true, 'message' => 'User berhasil di-reject'];
    }

    /**
     * Update user data
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete user (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Assign modules ke user
     */
    public function assignModules(User $user, array $moduleIds, User $admin): void
    {
        $user->syncModules($moduleIds, $admin);
    }

    /**
     * Get pending users count
     */
    public function getPendingCount(): int
    {
        return User::pending()->count();
    }

    /**
     * Get dashboard stats
     */
    public function getStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::active()->approved()->count(),
            'pending_users' => User::pending()->count(),
            'rejected_users' => User::where('approval_status', 'rejected')->count(),
            'admin_count' => User::where('role', 'admin')->count(),
        ];
    }

    /**
     * Pre-register email (admin daftarkan email yang boleh akses)
     */
    public function preRegisterEmail(string $email, string $role, array $moduleIds, User $admin): User
    {
        $user = User::create([
            'firebase_uid' => 'pending_' . md5($email . time()),
            'email' => $email,
            'name' => explode('@', $email)[0],
            'role' => $role,
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        if (!empty($moduleIds)) {
            $user->syncModules($moduleIds, $admin);
        }

        return $user;
    }

    /**
     * Bulk approve users
     */
    public function bulkApprove(array $userIds, User $admin): int
    {
        $count = 0;
        foreach ($userIds as $id) {
            $user = User::find($id);
            if ($user && $user->isPending()) {
                $user->approve($admin);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Bulk reject users
     */
    public function bulkReject(array $userIds): int
    {
        $count = 0;
        foreach ($userIds as $id) {
            $user = User::find($id);
            if ($user && $user->isPending()) {
                $user->reject();
                $count++;
            }
        }
        return $count;
    }
}
