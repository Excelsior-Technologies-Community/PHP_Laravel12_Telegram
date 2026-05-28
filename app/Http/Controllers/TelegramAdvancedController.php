<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TelegramSubscription;
use App\Models\TelegramMessageLog;
use App\Jobs\SendTelegramNotificationJob;
use App\Notifications\AdvancedTelegramNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;

class TelegramAdvancedController extends Controller
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 15]);
    }

    public function showForm()
    {
        return view('telegram.send-form');
    }

    public function dashboard()
    {
        return view('telegram.dashboard');
    }

    public function sendAdvanced(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'chat_ids' => 'required',
                'content' => 'required|string',
                'title' => 'nullable|string|max:100',
                'message_type' => 'nullable|string|in:info,success,warning,error,news,promo',
                'button_text' => 'nullable|string|max:64',
                'button_url' => 'nullable|url',
                'schedule_time' => 'nullable|date|after:now',
            ]);

            $chatIds = array_filter(array_map('trim', explode(',', $request->chat_ids)));
            $scheduleTime = $request->schedule_time;
            $isScheduled = (bool) $scheduleTime;

            foreach ($chatIds as $chatId) {
                if ($isScheduled) {
                    SendTelegramNotificationJob::dispatch(
                        $chatId,
                        $request->content,
                        $request->title,
                        $request->message_type ?? 'info',
                        $request->button_text,
                        $request->button_url
                    )->delay(now()->parse($scheduleTime));

                    TelegramMessageLog::create([
                        'chat_id' => $chatId,
                        'direction' => 'outgoing',
                        'message_type' => $request->message_type ?? 'info',
                        'content' => $request->content,
                        'title' => $request->title,
                        'status' => 'scheduled',
                        'is_scheduled' => true,
                        'scheduled_at' => $scheduleTime,
                    ]);
                } else {
                    $user = new User();
                    $user->setTelegramChatId($chatId);

                    $user->notify(new AdvancedTelegramNotification(
                        $request->content,
                        $request->title,
                        $request->message_type ?? 'info',
                        $request->button_text,
                        $request->button_url
                    ));
                }
            }

            $count = count($chatIds);
            $message = $isScheduled
                ? "Scheduled for {$count} recipient(s) at {$scheduleTime}"
                : "Sent successfully to {$count} recipient(s)";

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendBulk(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'chat_ids' => 'required|array',
                'chat_ids.*' => 'required|string',
                'message' => 'required|string',
                'title' => 'nullable|string',
                'message_type' => 'nullable|string|in:info,success,warning,error,news,promo',
            ]);

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($request->chat_ids as $chatId) {
                try {
                    $user = new User();
                    $user->setTelegramChatId($chatId);

                    $user->notify(new AdvancedTelegramNotification(
                        $request->message,
                        $request->title ?? 'Bulk Notification',
                        $request->message_type ?? 'info'
                    ));

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Chat {$chatId}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Sent: {$successCount}, Failed: {$failedCount}",
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendToSubscribers(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string',
                'title' => 'nullable|string',
                'message_type' => 'nullable|string|in:info,success,warning,error,news,promo',
                'target' => 'nullable|string|in:all,private,groups,channels',
            ]);

            $query = TelegramSubscription::active();

            match ($request->target ?? 'all') {
                'private' => $query->private(),
                'groups' => $query->groups(),
                'channels' => $query->channels(),
                default => null,
            };

            $subscriptions = $query->get();
            $successCount = 0;
            $failedCount = 0;

            foreach ($subscriptions as $subscription) {
                SendTelegramNotificationJob::dispatch(
                    $subscription->chat_id,
                    $request->message,
                    $request->title,
                    $request->message_type ?? 'info'
                );
                $successCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Queued for {$successCount} subscribers",
                'total_subscribers' => $subscriptions->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendWithImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'chat_id' => 'required|string',
                'image' => 'required|image|max:5120',
                'caption' => 'nullable|string|max:1024',
            ]);

            $botToken = config('services.telegram-bot-api.token');
            $url = "https://api.telegram.org/bot{$botToken}/sendPhoto";
            $image = $request->file('image');

            $response = $this->http->post($url, [
                'multipart' => [
                    ['name' => 'chat_id', 'contents' => $request->chat_id],
                    ['name' => 'photo', 'contents' => fopen($image->getPathname(), 'r'), 'filename' => $image->getClientOriginalName()],
                    ['name' => 'caption', 'contents' => $request->caption ?? ''],
                    ['name' => 'parse_mode', 'contents' => 'Markdown'],
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            if ($result['ok']) {
                TelegramMessageLog::logOutgoing($request->chat_id, $request->caption ?? '[Image]', 'Image');
                return response()->json(['success' => true, 'message' => 'Image sent successfully!']);
            }

            throw new \Exception('Telegram API returned error');

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendWithInlineKeyboard(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'chat_id' => 'required|string',
                'message' => 'required|string',
                'buttons' => 'nullable|array',
            ]);

            $botToken = config('services.telegram-bot-api.token');
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $keyboard = $request->buttons ?? [
                'inline_keyboard' => [
                    [
                        ['text' => '👍 Yes', 'callback_data' => 'yes'],
                        ['text' => '👎 No', 'callback_data' => 'no'],
                    ],
                    [
                        ['text' => '🔗 Visit Website', 'url' => config('app.url')],
                    ],
                ],
            ];

            $response = $this->http->post($url, [
                'json' => [
                    'chat_id' => $request->chat_id,
                    'text' => $request->message,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode($keyboard),
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            if ($result['ok']) {
                TelegramMessageLog::logOutgoing($request->chat_id, $request->message);
                return response()->json(['success' => true, 'message' => 'Message with keyboard sent!']);
            }

            throw new \Exception('Failed to send message');

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getBotInfo(): JsonResponse
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            $response = $this->http->get("https://api.telegram.org/bot{$botToken}/getMe");
            $result = json_decode($response->getBody(), true);

            return response()->json(['success' => true, 'bot_info' => $result['result']]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUpdates(Request $request): JsonResponse
    {
        try {
            $offset = $request->get('offset', 0);
            $botToken = config('services.telegram-bot-api.token');
            $response = $this->http->get("https://api.telegram.org/bot{$botToken}/getUpdates?offset={$offset}&limit=20");
            $result = json_decode($response->getBody(), true);

            return response()->json(['success' => true, 'updates' => $result['result']]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function setWebhook(Request $request): JsonResponse
    {
        try {
            $request->validate(['url' => 'required|url']);

            $botToken = config('services.telegram-bot-api.token');
            $response = $this->http->post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'json' => [
                    'url' => $request->url,
                    'allowed_updates' => ['message', 'callback_query', 'my_chat_member'],
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            return response()->json(['success' => $result['ok'], 'message' => $result['description']]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAnalytics(): JsonResponse
    {
        try {
            $analytics = [
                'total_sent' => TelegramMessageLog::outgoing()->count(),
                'sent_today' => TelegramMessageLog::outgoing()->today()->count(),
                'total_received' => TelegramMessageLog::incoming()->count(),
                'received_today' => TelegramMessageLog::incoming()->today()->count(),
                'failed' => TelegramMessageLog::failed()->count(),
                'scheduled' => TelegramMessageLog::where('status', 'scheduled')->count(),
                'total_subscribers' => TelegramSubscription::active()->count(),
                'private_subscribers' => TelegramSubscription::active()->private()->count(),
                'group_subscribers' => TelegramSubscription::active()->groups()->count(),
                'channel_subscribers' => TelegramSubscription::active()->channels()->count(),
            ];

            return response()->json(['success' => true, 'analytics' => $analytics]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSubscribers(Request $request): JsonResponse
    {
        try {
            $subscribers = TelegramSubscription::active()
                ->latest()
                ->paginate(20);

            return response()->json(['success' => true, 'subscribers' => $subscribers]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}