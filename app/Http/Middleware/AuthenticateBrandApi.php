<?php

namespace App\Http\Middleware;

use App\Models\Brand;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a brand's website call the API with "Authorization: Bearer <key>".
 * The brand comes from the key, so a site can only write to its own brand.
 */
class AuthenticateBrandApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) $request->bearerToken();
        $brand = $key === '' ? null : Brand::where('api_token', hash('sha256', $key))->where('is_active', true)->first();

        if (! $brand) {
            return response()->json(['message' => 'Invalid or missing API key.'], 401);
        }

        $request->attributes->set('brand', $brand);

        return $next($request);
    }
}
