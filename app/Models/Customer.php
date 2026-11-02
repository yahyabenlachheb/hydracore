<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $brand_id
 * @property string $full_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $country
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 */
class Customer extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['brand_id', 'full_name', 'email', 'phone', 'country', 'notes'];

    /**
     * An email is unique across brands, so someone who signed up on one brand and bought on another
     * is the same customer: both brands see them. Customers without a brand are shared.
     */
    public function scopeVisibleTo(Builder $query, ?int $brandId): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->whereNull('customers.brand_id')
            ->orWhere('customers.brand_id', $brandId)
            ->orWhereHas('orders', fn (Builder $query) => $query->where('brand_id', $brandId))
            ->orWhereHas('trials', fn (Builder $query) => $query->where('brand_id', $brandId))
            ->orWhereHas('subscriptions', fn (Builder $query) => $query->whereIn('plan_id', Plan::visibleTo($brandId)->select('id'))));
    }

    /**
     * Name or email contains the term. Grouped, so it's safe inside whereHas().
     */
    public function scopeMatching(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->where('customers.full_name', 'like', "%{$term}%")
            ->orWhere('customers.email', 'like', "%{$term}%"));
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn (?string $value) => $value === null ? null : strtolower(trim($value)));
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

    /**
     * @return HasMany<Trial, $this>
     */
    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class);
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
