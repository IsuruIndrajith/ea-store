<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = env('INTERNAL_API_KEY');

        if (! $key || $request->bearerToken() !== $key) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
