<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification
{
    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->content("*Laravel 12 Notification*\n\nThis is *bold text*\nThis is _italic text_\n\n[Visit Laravel](https://laravel.com)");
    }
}