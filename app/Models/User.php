<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'mst_users';

    protected $fillable = [
        'firebase_uid',
        'name',
        'email',
        'role',
        'status',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function ssoTokens(): HasMany
    {
        return $this->hasMany(SsoToken::class);
    }

    // ==================== ACCESSORS ====================

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->deleted_at);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByFirebaseUid($query, string $uid)
    {
        return $query->where('firebase_uid', $uid);
    }
}