<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\SendTelegramNotificationJob;
use App\Notifications\AdvancedTelegramNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TelegramAdvancedController extends Controller
{
    // Show send form
    public function showForm()
    {
        return view('telegram.send-form');
    }

    // Send advanced notification
    public function sendAdvanced(Request $request)
    {
        try {
            $request->validate([
                'chat_ids' => 'required',
                'content' => 'required',
                'title' => 'nullable',
                'message_type' => 'nullable',
                'button_text' => 'nullable',
                'button_url' => 'nullable',
                'schedule_time' => 'nullable|date'
            ]);

            $chatIds = explode(',', $request->chat_ids);
            $chatIds = array_map('trim', $chatIds);
            
            $scheduleTime = $request->schedule_time;
            
            foreach ($chatIds as $chatId) {
                if ($scheduleTime) {
                    // Schedule for later
                    SendTelegramNotificationJob::dispatch(
                        $chatId,
                        $request->content,
                        $request->title,
                        $request->message_type,
                        $request->button_text,
                        $request->button_url
                    )->delay(now()->parse($scheduleTime));
                } else {
                    // Send immediately
                    $user = new User();
                    $user->setTelegramChatId($chatId);
                    
                    $user->notify(new AdvancedTelegramNotification(
                        $request->content,
                        $request->title,
                        $request->message_type,
                        $request->button_text,
                        $request->button_url
                    ));
                }
            }
            
            $message = $scheduleTime 
                ? "Notifications scheduled successfully for " . count($chatIds) . " recipient(s)!"
                : "Notifications sent successfully to " . count($chatIds) . " recipient(s)!";
            
            return response()->json(['success' => true, 'message' => $message]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Send bulk notification to multiple users
    public function sendBulk(Request $request)
    {
        try {
            $request->validate([
                'chat_ids' => 'required|array',
                'message' => 'required'
            ]);
            
            $successCount = 0;
            $failedCount = 0;
            
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
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Sent to $successCount users, Failed: $failedCount"
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Send image with caption
    public function sendWithImage(Request $request)
    {
        try {
            $request->validate([
                'chat_id' => 'required',
                'image' => 'required|image|max:5120', // Max 5MB
                'caption' => 'nullable'
            ]);
            
            $chatId = $request->chat_id;
            $image = $request->file('image');
            $caption = $request->caption ?? 'Image from Laravel';
            
            // You need to implement image sending logic here
            // Using Telegram Bot API directly
            $botToken = config('services.telegram-bot-api.token');
            $url = "https://api.telegram.org/bot{$botToken}/sendPhoto";
            
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'multipart' => [
                    [
                        'name' => 'chat_id',
                        'contents' => $chatId
                    ],
                    [
                        'name' => 'photo',
                        'contents' => fopen($image->getPathname(), 'r'),
                        'filename' => $image->getClientOriginalName()
                    ],
                    [
                        'name' => 'caption',
                        'contents' => $caption
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['ok']) {
                return response()->json(['success' => true, 'message' => 'Image sent successfully!']);
            } else {
                throw new \Exception('Failed to send image');
            }
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Get bot information
    public function getBotInfo()
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            $url = "https://api.telegram.org/bot{$botToken}/getMe";
            
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            $result = json_decode($response->getBody(), true);
            
            return response()->json([
                'success' => true,
                'bot_info' => $result['result']
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Get updates (messages from users)
    public function getUpdates(Request $request)
    {
        try {
            $offset = $request->get('offset', 0);
            $botToken = config('services.telegram-bot-api.token');
            $url = "https://api.telegram.org/bot{$botToken}/getUpdates?offset={$offset}";
            
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            $result = json_decode($response->getBody(), true);
            
            return response()->json([
                'success' => true,
                'updates' => $result['result']
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Send inline keyboard
    public function sendWithInlineKeyboard(Request $request)
    {
        try {
            $request->validate([
                'chat_id' => 'required',
                'message' => 'required'
            ]);
            
            $botToken = config('services.telegram-bot-api.token');
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '👍 Yes', 'callback_data' => 'yes'],
                        ['text' => '👎 No', 'callback_data' => 'no']
                    ],
                    [
                        ['text' => '🔗 Visit Website', 'url' => 'https://laravel.com']
                    ]
                ]
            ];
            
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'json' => [
                    'chat_id' => $request->chat_id,
                    'text' => $request->message,
                    'reply_markup' => json_encode($keyboard)
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['ok']) {
                return response()->json(['success' => true, 'message' => 'Message with keyboard sent!']);
            } else {
                throw new \Exception('Failed to send message');
            }
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Show dashboard
public function dashboard()
{
    return view('telegram.dashboard');
}
}