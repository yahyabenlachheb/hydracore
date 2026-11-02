<?php

namespace App\Http\Middleware;

use App\Support\BrandContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Plans, orders and the like always belong to a brand. A super admin can reach this
 * point before any brand exists; send them to create one.
 */
class EnsureBrandSelected
{
    public function __construct(private BrandContext $brands) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->brands->current() === null) {
            return redirect()->route('brands.choose');
        }

        return $next($request);
    }
}
