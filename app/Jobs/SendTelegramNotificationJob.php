<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\RateLimited;
use App\Models\User;
use App\Models\TelegramMessageLog;
use App\Notifications\AdvancedTelegramNotification;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    protected string $chatId;
    protected string $message;
    protected ?string $title;
    protected string $messageType;
    protected ?string $buttonText;
    protected ?string $buttonUrl;

    public function __construct(
        string $chatId,
        string $message,
        string $title = null,
        string $messageType = 'info',
        string $buttonText = null,
        string $buttonUrl = null
    ) {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->title = $title;
        $this->messageType = $messageType;
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
        $this->onQueue('telegram');
    }

    public function handle(): void
    {
        $user = new User();
        $user->setTelegramChatId($this->chatId);

        $user->notify(new AdvancedTelegramNotification(
            $this->message,
            $this->title,
            $this->messageType,
            $this->buttonText,
            $this->buttonUrl,
            true,
            now()
        ));
    }

    public function failed(\Throwable $exception): void
    {
        TelegramMessageLog::markFailed(
            $this->chatId,
            $this->message,
            $exception->getMessage()
        );
    }
}