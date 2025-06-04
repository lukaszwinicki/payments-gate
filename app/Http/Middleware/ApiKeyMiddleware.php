<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $merchant = Merchant::where('api_key', $apiKey)->first();

        if (!$apiKey || $merchant === null) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Auth::login($merchant);

        return $next($request);
    }
}
