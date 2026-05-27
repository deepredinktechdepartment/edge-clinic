<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $providedKey = $request->bearerToken() ?: $request->header('X-API-Key');
        $keys = config('partner_api.keys', []);

        if (! is_string($providedKey) || $providedKey === '' || empty($keys)) {
            return response()->json([
                'message' => 'Unauthorized partner API request.',
            ], 401);
        }

        foreach ($keys as $clientCode => $clientKey) {
            if (is_string($clientKey) && hash_equals($clientKey, $providedKey)) {
                $request->attributes->set('partner_client', $clientCode);
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Invalid partner API key.',
        ], 401);
    }
}
