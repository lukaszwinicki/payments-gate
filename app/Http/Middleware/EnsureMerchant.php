<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMerchant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $principal = $request->user();

        $merchant = match(true) {
            $principal instanceof Merchant => $principal,
            $principal instanceof User => $principal->merchant()->first(),
            default => null
        };

        if(!$merchant) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->attributes->set('merchant', $merchant);

        return $next($request);
    }
}
