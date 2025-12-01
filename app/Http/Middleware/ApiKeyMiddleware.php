<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        
        // Ambil API key dari .env
        $validApiKey = config('app.iot_api_key');
        
        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid API Key.',
            ], 401);
        }
        
        return $next($request);
    }
}