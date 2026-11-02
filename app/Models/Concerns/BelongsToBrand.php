<?php

namespace App\Models\Concerns;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For tables with a brand_id. Records without a brand are shared, so every brand sees them.
 */
trait BelongsToBrand
{
    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeVisibleTo(Builder $query, ?int $brandId): Builder
    {
        return $query->where(fn (Builder $query) => $query->whereNull($this->qualifyColumn('brand_id'))->orWhere($this->qualifyColumn('brand_id'), $brandId));
    }
}
