<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Notifications\AdvancedTelegramNotification;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $message;
    protected $title;
    protected $messageType;
    protected $buttonText;
    protected $buttonUrl;

    public function __construct($chatId, $message, $title, $messageType, $buttonText = null, $buttonUrl = null)
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->title = $title;
        $this->messageType = $messageType;
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
    }

    public function handle()
    {
        $user = new User();
        $user->setTelegramChatId($this->chatId);
        
        $user->notify(new AdvancedTelegramNotification(
            $this->message,
            $this->title,
            $this->messageType,
            $this->buttonText,
            $this->buttonUrl
        ));
    }
}