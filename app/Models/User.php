<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $telegramChatId = null;

    public function routeNotificationForTelegram()
    {
        return $this->telegramChatId ?? "987654321"; // Default chat ID
    }

    public function setTelegramChatId($chatId)
    {
        $this->telegramChatId = $chatId;
        return $this;
    }
}