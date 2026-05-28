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
        return $this->telegramChatId;
    }

   
    public function getTelegramChatId()
    {
        return $this->telegramChatId;
    }

  
    public function setTelegramChatId($chatId)
    {
        $this->telegramChatId = $chatId;
        return $this;
    }
}