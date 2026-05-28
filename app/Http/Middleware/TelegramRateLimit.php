<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TelegramRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = 'telegram_rate_limit_' . $ip;
        $maxAttempts = 10;
        $decaySeconds = 60;

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please wait a moment.',
                'retry_after' => $decaySeconds,
            ], 429);
        }

        Cache::put($key, $attempts + 1, $decaySeconds);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));

        return $response;
    }
}