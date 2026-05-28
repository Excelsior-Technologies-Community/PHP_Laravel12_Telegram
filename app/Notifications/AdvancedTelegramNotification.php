<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\TelegramMessageLog;

class AdvancedTelegramNotification extends Notification
{
    protected string $message;
    protected ?string $title;
    protected string $messageType;
    protected ?string $buttonText;
    protected ?string $buttonUrl;
    protected bool $isScheduled;
    protected $scheduledAt;

    public function __construct(
        string $message,
        string $title = null,
        string $messageType = 'info',
        string $buttonText = null,
        string $buttonUrl = null,
        bool $isScheduled = false,
        $scheduledAt = null
    ) {
        $this->message = $message;
        $this->title = $title;
        $this->messageType = $messageType;
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
        $this->isScheduled = $isScheduled;
        $this->scheduledAt = $scheduledAt;
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $emojis = [
            'info' => 'ℹ️',
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            'news' => '📰',
            'promo' => '🎁',
        ];

        $emoji = $emojis[$this->messageType] ?? '📝';
        $formattedMessage = '';

        if ($this->title) {
            $formattedMessage .= "*{$emoji} {$this->title}*\n\n";
        }

        $formattedMessage .= $this->message;
        $formattedMessage .= "\n\n⏰ *Sent:* " . now()->format('d-m-Y H:i:s');

        $telegramMessage = TelegramMessage::create()->content($formattedMessage);

        if ($this->buttonText && $this->buttonUrl) {
            $telegramMessage->button($this->buttonText, $this->buttonUrl);
        }

        $chatId = $notifiable->telegram_chat_id ?? $notifiable->getTelegramChatId();

        TelegramMessageLog::logOutgoing(
            (string) $chatId,
            $this->message,
            $this->title,
            $this->messageType,
            $this->isScheduled,
            $this->scheduledAt
        );

        return $telegramMessage;
    }
}