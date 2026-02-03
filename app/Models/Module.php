<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $table = 'mst_modules';

    protected $fillable = [
        'key',
        'name',
        'description',
        'url',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trs_user_modules')
            ->withPivot(['granted_at', 'granted_by'])
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== HELPERS ====================

    public static function syncFromConfig(): void
    {
        $apps = config('sso.apps', []);
        $order = 0;

        foreach ($apps as $key => $app) {
            self::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $app['name'],
                    'description' => $app['description'] ?? null,
                    'url' => $app['url'] ?? '#',
                    'icon' => $app['icon'] ?? 'squares-2x2',
                    'color' => $app['color'] ?? 'gray',
                    'is_active' => $app['enabled'] ?? false,
                    'sort_order' => $order++,
                ]
            );
        }
    }
}
