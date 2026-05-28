<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramSubscription;
use App\Models\TelegramMessageLog;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::channel('telegram')->info('Webhook received', ['update' => $update]);

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        } elseif (isset($update['my_chat_member'])) {
            $this->handleChatMember($update['my_chat_member']);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleMessage(array $message): void
    {
        $chat = $message['chat'];
        $text = $message['text'] ?? '';
        $chatId = (string) $chat['id'];

        TelegramSubscription::findOrCreateFromTelegram($chat);

        TelegramMessageLog::logIncoming($chatId, $text);

        $command = strtolower(trim($text));

        match (true) {
            str_starts_with($command, '/start') => $this->handleStart($chatId, $chat),
            str_starts_with($command, '/stop') => $this->handleStop($chatId),
            str_starts_with($command, '/help') => $this->handleHelp($chatId),
            str_starts_with($command, '/status') => $this->handleStatus($chatId),
            default => $this->handleDefault($chatId, $text),
        };
    }

    protected function handleStart(string $chatId, array $chat): void
    {
        $name = $chat['first_name'] ?? 'there';

        $this->sendMessage($chatId,
            "👋 *Welcome, {$name}!*\n\n" .
            "You are now subscribed to notifications.\n\n" .
            "📌 *Available commands:*\n" .
            "/start - Subscribe\n" .
            "/stop - Unsubscribe\n" .
            "/status - Check your status\n" .
            "/help - Show help"
        );
    }

    protected function handleStop(string $chatId): void
    {
        $subscription = TelegramSubscription::where('chat_id', $chatId)->first();

        if ($subscription) {
            $subscription->unsubscribe();
        }

        $this->sendMessage($chatId,
            "👋 *You have been unsubscribed.*\n\n" .
            "Send /start anytime to re-subscribe."
        );
    }

    protected function handleHelp(string $chatId): void
    {
        $this->sendMessage($chatId,
            "ℹ️ *Help Center*\n\n" .
            "This bot sends you important notifications.\n\n" .
            "📌 *Commands:*\n" .
            "/start - Subscribe to notifications\n" .
            "/stop - Unsubscribe\n" .
            "/status - View your subscription status\n" .
            "/help - Show this help message"
        );
    }

    protected function handleStatus(string $chatId): void
    {
        $subscription = TelegramSubscription::where('chat_id', $chatId)->first();

        if (!$subscription) {
            $this->sendMessage($chatId, "❌ You are not registered. Send /start to subscribe.");
            return;
        }

        $status = $subscription->is_active ? '✅ Active' : '❌ Inactive';
        $since = $subscription->subscribed_at?->format('d M Y');

        $this->sendMessage($chatId,
            "📊 *Your Status*\n\n" .
            "Status: {$status}\n" .
            "Subscribed since: {$since}\n" .
            "Chat ID: `{$chatId}`"
        );
    }

    protected function handleDefault(string $chatId, string $text): void
    {
        $this->sendMessage($chatId,
            "🤖 I received your message.\n\nSend /help to see available commands."
        );
    }

    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = (string) $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        $callbackId = $callbackQuery['id'];

        $this->answerCallbackQuery($callbackId, 'Response received!');

        match ($data) {
            'yes' => $this->sendMessage($chatId, '✅ You selected Yes!'),
            'no' => $this->sendMessage($chatId, '❌ You selected No!'),
            default => $this->sendMessage($chatId, "You clicked: {$data}"),
        };
    }

    protected function handleChatMember(array $chatMember): void
    {
        $chatId = (string) $chatMember['chat']['id'];
        $newStatus = $chatMember['new_chat_member']['status'];

        if (in_array($newStatus, ['kicked', 'left'])) {
            TelegramSubscription::where('chat_id', $chatId)
                ->update(['is_active' => false, 'unsubscribed_at' => now()]);
        }
    }

    protected function sendMessage(string $chatId, string $text, array $replyMarkup = []): void
    {
        $botToken = config('services.telegram-bot-api.token');
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $client->post($url, ['json' => $payload]);

            TelegramMessageLog::logOutgoing($chatId, $text);
        } catch (\Exception $e) {
            Log::channel('telegram')->error('Failed to send message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function answerCallbackQuery(string $callbackId, string $text = ''): void
    {
        $botToken = config('services.telegram-bot-api.token');
        $url = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $client->post($url, [
                'json' => [
                    'callback_query_id' => $callbackId,
                    'text' => $text,
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('telegram')->error('Failed to answer callback', ['error' => $e->getMessage()]);
        }
    }
}