<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SsoToken extends Model
{
    protected $table = 'trs_sso_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'app',
        'callback_url',
        'used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    public function scopeByToken(Builder $query, string $token): Builder
    {
        return $query->where('token', $token);
    }

    public function scopeByApp(Builder $query, string $app): Builder
    {
        return $query->where('app', $app);
    }

    // ==================== HELPERS ====================

    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }

    public function markUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }
}
