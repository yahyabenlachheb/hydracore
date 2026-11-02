<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An IPTV line.
 *
 * @property int $id
 * @property int $customer_id
 * @property int $plan_id
 * @property string $device_type
 * @property string|null $device_info
 * @property string $status pending|active|expired|cancelled|suspended
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $cancelled_at
 */
class Subscription extends Model
{
    use Auditable;

    public const STATUSES = ['pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired', 'cancelled' => 'Cancelled'];

    public const DEVICE_TYPES = ['smart_tv' => 'Smart TV', 'android' => 'Android', 'firestick' => 'Fire TV Stick', 'mag' => 'MAG box', 'ios' => 'iOS', 'pc' => 'PC / Mac', 'other' => 'Other'];

    protected $fillable = ['customer_id', 'plan_id', 'device_type', 'device_info', 'status', 'starts_at', 'expires_at', 'cancelled_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * A line belongs to the brand of its plan.
     */
    public function scopeVisibleTo(Builder $query, ?int $brandId): Builder
    {
        return $query->whereIn('subscriptions.plan_id', Plan::visibleTo($brandId)->select('id'));
    }

    /**
     * Active lines expiring within the given number of days.
     */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->where('status', 'active')->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return BelongsToMany<Pack, $this>
     */
    public function packs(): BelongsToMany
    {
        // Not Laravel's default name, which would be pack_subscription.
        return $this->belongsToMany(Pack::class, 'subscription_pack');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
