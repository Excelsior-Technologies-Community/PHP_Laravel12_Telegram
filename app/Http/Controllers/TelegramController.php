<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TelegramNotification;

class TelegramController extends Controller
{
    public function send()
    {
        $user = new User();

        $user->notify(new TelegramNotification());

        return "Telegram notification sent successfully!";
    }
}