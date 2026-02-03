<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\User;
use App\Services\ModuleService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Admin Panel
 * Thin Controller: Business logic di Services
 */
class AdminController extends Controller
{
    public function __construct(
        private UserService $userService,
        private ModuleService $moduleService
    ) {}

    /**
     * Admin Dashboard
     */
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $this->userService->getStats(),
            'pendingUsers' => User::pending()->latest()->take(5)->get(),
            'recentUsers' => User::latest()->take(5)->get(),
        ]);
    }

    /**
     * User Management - List
     */
    public function users(Request $request): Response
    {
        $filters = $request->only(['status', 'approval_status', 'role', 'search', 'sort_by', 'sort_dir', 'per_page']);
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $this->userService->getUsers($filters),
            'modules' => $this->moduleService->getAllModules(),
            'filters' => $filters,
            'stats' => $this->userService->getStats(),
        ]);
    }

    /**
     * User Detail
     */
    public function userShow(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load(['modules', 'approvedBy']),
            'modules' => $this->moduleService->getAllModules(),
        ]);
    }

    /**
     * Approve User
     */
    public function userApprove(User $user): RedirectResponse
    {
        $result = $this->userService->approveUser($user, auth()->user());
        
        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Reject User
     */
    public function userReject(User $user): RedirectResponse
    {
        $result = $this->userService->rejectUser($user);
        
        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Update User
     */
    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|in:admin,user',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $this->userService->updateUser($user, $validated);
        
        return back()->with('success', 'User berhasil diupdate');
    }

    /**
     * Delete User
     */
    public function userDestroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }

        $this->userService->deleteUser($user);
        
        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus');
    }

    /**
     * Assign Modules ke User
     */
    public function userAssignModules(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'module_ids' => 'array',
            'module_ids.*' => 'exists:mst_modules,id',
        ]);

        $this->userService->assignModules(
            $user,
            $validated['module_ids'] ?? [],
            auth()->user()
        );
        
        return back()->with('success', 'Akses modul berhasil diupdate');
    }

    /**
     * Bulk Approve Users
     */
    public function userBulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:mst_users,id',
        ]);

        $count = $this->userService->bulkApprove($validated['user_ids'], auth()->user());
        
        return back()->with('success', "{$count} user berhasil di-approve");
    }

    /**
     * Bulk Reject Users
     */
    public function userBulkReject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:mst_users,id',
        ]);

        $count = $this->userService->bulkReject($validated['user_ids']);
        
        return back()->with('success', "{$count} user berhasil di-reject");
    }

    /**
     * Pre-register Email
     */
    public function userPreRegister(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:mst_users,email',
            'role' => 'required|in:admin,user',
            'module_ids' => 'array',
            'module_ids.*' => 'exists:mst_modules,id',
        ]);

        $this->userService->preRegisterEmail(
            $validated['email'],
            $validated['role'],
            $validated['module_ids'] ?? [],
            auth()->user()
        );
        
        return back()->with('success', 'Email berhasil didaftarkan');
    }

    /**
     * Module Management - List
     */
    public function modules(): Response
    {
        return Inertia::render('Admin/Modules/Index', [
            'modules' => $this->moduleService->getAllModules()->loadCount('users'),
        ]);
    }

    /**
     * Module Store
     */
    public function moduleStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:mst_modules,key|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|url',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $this->moduleService->create($validated);
        
        return back()->with('success', 'Modul berhasil ditambahkan');
    }

    /**
     * Module Update
     */
    public function moduleUpdate(Request $request, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'url' => 'sometimes|url',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $this->moduleService->update($module, $validated);
        
        return back()->with('success', 'Modul berhasil diupdate');
    }

    /**
     * Module Toggle Status
     */
    public function moduleToggle(Module $module): RedirectResponse
    {
        $this->moduleService->toggleStatus($module);
        
        return back()->with('success', 'Status modul berhasil diubah');
    }

    /**
     * Module Destroy
     */
    public function moduleDestroy(Module $module): RedirectResponse
    {
        $this->moduleService->delete($module);
        
        return back()->with('success', 'Modul berhasil dihapus');
    }

    /**
     * Sync Modules from Config
     */
    public function modulesSync(): RedirectResponse
    {
        $this->moduleService->syncFromConfig();
        
        return back()->with('success', 'Modul berhasil disinkronkan dari config');
    }
}
