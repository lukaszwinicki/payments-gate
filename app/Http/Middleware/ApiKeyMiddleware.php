<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKeyHeader = $request->header('X-API-KEY');
        $apiKeyMerchant = Merchant::where('api_key', $apiKeyHeader)->first();

        if (!$apiKeyHeader || $apiKeyMerchant === null) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
