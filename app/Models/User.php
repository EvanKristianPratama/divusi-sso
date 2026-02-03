<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = 'mst_users';

    protected $fillable = [
        'firebase_uid',
        'provider',
        'name',
        'email',
        'avatar_url',
        'role',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function ssoTokens(): HasMany
    {
        return $this->hasMany(SsoToken::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'trs_user_modules')
            ->withPivot(['granted_at', 'granted_by'])
            ->withTimestamps();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==================== ACCESSORS ====================

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->deleted_at);
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccess(): bool
    {
        return $this->isActive() && $this->isApproved();
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeByFirebaseUid($query, string $uid)
    {
        return $query->where('firebase_uid', $uid);
    }

    // ==================== HELPERS ====================

    public function approve(User $admin): bool
    {
        return $this->update([
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(): bool
    {
        return $this->update([
            'approval_status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function hasModuleAccess(string $moduleKey): bool
    {
        return $this->modules()->where('key', $moduleKey)->exists();
    }

    public function grantModule(Module $module, ?User $grantedBy = null): void
    {
        $this->modules()->syncWithoutDetaching([
            $module->id => [
                'granted_at' => now(),
                'granted_by' => $grantedBy?->id,
            ]
        ]);
    }

    public function revokeModule(Module $module): void
    {
        $this->modules()->detach($module->id);
    }

    public function syncModules(array $moduleIds, ?User $grantedBy = null): void
    {
        $syncData = [];
        foreach ($moduleIds as $moduleId) {
            $syncData[$moduleId] = [
                'granted_at' => now(),
                'granted_by' => $grantedBy?->id,
            ];
        }
        $this->modules()->sync($syncData);
    }
}