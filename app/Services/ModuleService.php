<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;

/**
 * Service untuk Module Management
 * Single Responsibility: CRUD modules
 */
class ModuleService
{
    /**
     * Get all modules
     */
    public function getAllModules(): Collection
    {
        return Module::ordered()->get();
    }

    /**
     * Get active modules
     */
    public function getActiveModules(): Collection
    {
        return Module::active()->ordered()->get();
    }

    /**
     * Get module by key
     */
    public function getByKey(string $key): ?Module
    {
        return Module::where('key', $key)->first();
    }

    /**
     * Get module by ID
     */
    public function getById(int $id): ?Module
    {
        return Module::find($id);
    }

    /**
     * Create module
     */
    public function create(array $data): Module
    {
        return Module::create($data);
    }

    /**
     * Update module
     */
    public function update(Module $module, array $data): Module
    {
        $module->update($data);
        return $module->fresh();
    }

    /**
     * Delete module
     */
    public function delete(Module $module): bool
    {
        return $module->delete();
    }

    /**
     * Toggle module status
     */
    public function toggleStatus(Module $module): Module
    {
        $module->update(['is_active' => !$module->is_active]);
        return $module->fresh();
    }

    /**
     * Sync modules dari config
     */
    public function syncFromConfig(): void
    {
        Module::syncFromConfig();
    }

    /**
     * Update sort order
     */
    public function updateOrder(array $order): void
    {
        foreach ($order as $index => $moduleId) {
            Module::where('id', $moduleId)->update(['sort_order' => $index]);
        }
    }
}
