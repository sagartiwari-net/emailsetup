<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = config('mail-system.api_key_header', 'X-API-Key');
        $plainKey = $request->header($header);

        if (! $plainKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required.',
            ], 401);
        }

        $apiKey = ApiKey::findByPlainKey($plainKey);

        if (! $apiKey || ! $apiKey->tenant?->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API key.',
            ], 401);
        }

        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('tenant', $apiKey->tenant);
        $request->attributes->set('domain', $apiKey->domain);

        return $next($request);
    }
}
