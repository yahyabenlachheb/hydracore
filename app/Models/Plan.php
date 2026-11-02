<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $brand_id
 * @property string $name
 * @property int $duration_months
 * @property string $price
 * @property string $currency
 * @property int $number_of_connections
 * @property bool $is_active
 * @property int $sort_order
 */
class Plan extends Model
{
    use Auditable;

    protected $fillable = ['brand_id', 'name', 'duration_months', 'price', 'currency', 'number_of_connections', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Every plan belongs to a brand; unlike other tables there are no shared ones.
     */
    public function scopeVisibleTo(Builder $query, ?int $brandId): Builder
    {
        return $query->where('plans.brand_id', $brandId);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
