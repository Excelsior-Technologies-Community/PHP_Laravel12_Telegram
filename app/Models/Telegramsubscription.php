<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TelegramSubscription extends Model
{
    protected $fillable = [
        'chat_id',
        'username',
        'first_name',
        'last_name',
        'type',
        'is_active',
        'preferences',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'preferences' => 'array',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('type', 'private');
    }

    public function scopeGroups(Builder $query): Builder
    {
        return $query->whereIn('type', ['group', 'supergroup']);
    }

    public function scopeChannels(Builder $query): Builder
    {
        return $query->where('type', 'channel');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function subscribe(): void
    {
        $this->update([
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    public static function findOrCreateFromTelegram(array $chat): self
    {
        return self::updateOrCreate(
            ['chat_id' => (string) $chat['id']],
            [
                'username' => $chat['username'] ?? null,
                'first_name' => $chat['first_name'] ?? null,
                'last_name' => $chat['last_name'] ?? null,
                'type' => $chat['type'] ?? 'private',
                'is_active' => true,
                'subscribed_at' => now(),
            ]
        );
    }
}