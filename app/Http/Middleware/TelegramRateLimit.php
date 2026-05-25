<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class TelegramRateLimit
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $key = 'telegram_rate_limit_' . $ip;
        
        if (Cache::has($key)) {
            $attempts = Cache::get($key);
            if ($attempts >= 10) { // Max 10 requests per minute
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please wait.'
                ], 429);
            }
            Cache::increment($key);
        } else {
            Cache::put($key, 1, 60); // 1 minute expiry
        }
        
        return $next($request);
    }
}