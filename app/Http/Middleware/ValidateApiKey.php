<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        $validApiKey = config('app.api_key');

        // Debug logging
        Log::info('API Key Validation', [
            'received_key' => $apiKey ?  substr($apiKey, 0, 20) . '...' : 'NULL',
            'valid_key' => $validApiKey ? substr($validApiKey, 0, 20) . '...' : 'NULL',
            'match' => $apiKey === $validApiKey,
        ]);

        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid API Key.'
            ], 401);
        }

        return $next($request);
    }
}
