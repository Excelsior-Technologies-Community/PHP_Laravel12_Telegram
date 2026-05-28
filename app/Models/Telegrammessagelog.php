<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TelegramMessageLog extends Model
{
    protected $fillable = [
        'chat_id',
        'direction',
        'message_type',
        'content',
        'title',
        'status',
        'error_message',
        'is_scheduled',
        'scheduled_at',
    ];

    protected $casts = [
        'is_scheduled' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    public function scopeOutgoing(Builder $query): Builder
    {
        return $query->where('direction', 'outgoing');
    }

    public function scopeIncoming(Builder $query): Builder
    {
        return $query->where('direction', 'incoming');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public static function logOutgoing(string $chatId, string $content, string $title = null, string $messageType = 'info', bool $isScheduled = false, $scheduledAt = null): self
    {
        return self::create([
            'chat_id' => $chatId,
            'direction' => 'outgoing',
            'message_type' => $messageType,
            'content' => $content,
            'title' => $title,
            'status' => 'sent',
            'is_scheduled' => $isScheduled,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public static function logIncoming(string $chatId, string $content): self
    {
        return self::create([
            'chat_id' => $chatId,
            'direction' => 'incoming',
            'message_type' => 'text',
            'content' => $content,
            'status' => 'received',
        ]);
    }

    public static function markFailed(string $chatId, string $content, string $error): self
    {
        return self::create([
            'chat_id' => $chatId,
            'direction' => 'outgoing',
            'content' => $content,
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }
}