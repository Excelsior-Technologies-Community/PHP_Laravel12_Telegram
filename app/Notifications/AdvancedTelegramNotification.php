<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class AdvancedTelegramNotification extends Notification
{
    protected $message;
    protected $title;
    protected $messageType;
    protected $buttonText;
    protected $buttonUrl;

    public function __construct($message, $title = null, $messageType = 'info', $buttonText = null, $buttonUrl = null)
    {
        $this->message = $message;
        $this->title = $title;
        $this->messageType = $messageType;
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        // Emoji based on message type
        $emojis = [
            'info' => 'ℹ️',
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌'
        ];
        
        $emoji = $emojis[$this->messageType] ?? '📝';
        
        // Format message with title
        $formattedMessage = "";
        
        if ($this->title) {
            $formattedMessage .= "*{$emoji} {$this->title}*\n\n";
        }
        
        $formattedMessage .= $this->message;
        
        // Add timestamp
        $formattedMessage .= "\n\n⏰ *Sent:* " . now()->format('d-m-Y H:i:s');
        
        $telegramMessage = TelegramMessage::create()
            ->content($formattedMessage);
        
        // Add button if provided
        if ($this->buttonText && $this->buttonUrl) {
            $telegramMessage->button($this->buttonText, $this->buttonUrl);
        }
        
        return $telegramMessage;
    }
}