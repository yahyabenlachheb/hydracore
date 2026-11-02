<?php

namespace App\Support;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cookie;

/**
 * The brand the current user is working in, kept in the session.
 *
 * Super admins can pick any active brand; everyone else is limited to the brands
 * assigned to them through user_brands.
 */
class BrandContext
{
    private const SESSION_KEY = 'current_brand_id';

    private const COOKIE_KEY = 'last_brand_id';

    private const COOKIE_MINUTES = 60 * 24 * 365;

    /** @var Collection<int, Brand>|null */
    private ?Collection $available = null;

    /**
     * @return Collection<int, Brand>
     */
    public function available(): Collection
    {
        if ($this->available !== null) {
            return $this->available;
        }

        $user = auth()->user();

        $query = $user && ! $user->isSuperAdmin() ? $user->brands() : Brand::query();

        return $this->available = $query->where('is_active', true)->orderBy('name')->get();
    }

    public function current(): ?Brand
    {
        $brands = $this->available();

        return $brands->firstWhere('id', session(self::SESSION_KEY))
            ?? $this->lastUsed()
            ?? $brands->first();
    }

    /**
     * The brand last picked in this browser, if the user can still access it. Survives logout.
     */
    public function lastUsed(): ?Brand
    {
        return $this->available()->firstWhere('id', (int) request()->cookie(self::COOKIE_KEY));
    }

    /**
     * Whether the user may manage records that belong to this brand id.
     */
    public function owns(?int $brandId): bool
    {
        return $brandId !== null && $this->available()->contains('id', $brandId);
    }

    public function canSwitchTo(Brand $brand): bool
    {
        return $this->available()->contains('id', $brand->id);
    }

    public function switchTo(Brand $brand): void
    {
        session([self::SESSION_KEY => $brand->id]);

        Cookie::queue(self::COOKIE_KEY, (string) $brand->id, self::COOKIE_MINUTES);
    }
}
