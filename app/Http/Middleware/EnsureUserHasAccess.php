<?php

namespace App\Http\Middleware;

use App\Support\BrandContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps signed-in users out of the app until they have a role with at least one
 * permission and an active brand. Super admins always pass.
 */
class EnsureUserHasAccess
{
    public function __construct(private BrandContext $brands) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! self::hasAccess($request, $this->brands)) {
            return redirect()->route('no-access');
        }

        return $next($request);
    }

    public static function hasAccess(Request $request, BrandContext $brands): bool
    {
        return ! in_array(true, self::missing($request, $brands), true);
    }

    /**
     * What the user still lacks, as ['role' => bool, 'permission' => bool, 'brand' => bool].
     *
     * @return array{role: bool, permission: bool, brand: bool}
     */
    public static function missing(Request $request, BrandContext $brands): array
    {
        $user = $request->user();

        if ($user === null || $user->isSuperAdmin()) {
            return ['role' => false, 'permission' => false, 'brand' => false];
        }

        return [
            'role' => ! $user->roles()->exists(),
            'permission' => ! $user->roles()->whereHas('permissions')->exists(),
            'brand' => $brands->available()->isEmpty(),
        ];
    }
}
