<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A purchase: a new line or a renewal. Prices are copied from the plan so history stays correct.
 *
 * @property int $id
 * @property string $reference_number
 * @property int $customer_id
 * @property int $plan_id
 * @property int|null $subscription_id
 * @property int|null $brand_id
 * @property string $type new|renewal
 * @property string $status pending|paid|failed|cancelled|refunded
 * @property string $plan_name
 * @property int $duration_months
 * @property string $subtotal
 * @property string $discount
 * @property string $total
 * @property string $currency
 * @property string|null $coupon_code
 * @property string|null $description
 * @property string|null $ip_address
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 */
class Order extends Model
{
    use Auditable, BelongsToBrand;

    public const STATUSES = ['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded'];

    protected $fillable = [
        'reference_number', 'customer_id', 'plan_id', 'subscription_id', 'brand_id', 'type', 'status', 'plan_name',
        'duration_months', 'subtotal', 'discount', 'total', 'currency', 'coupon_code', 'description', 'ip_address', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public static function newReference(): string
    {
        return 'ORD-' . Str::upper(Str::random(10));
    }

    public function isPayable(): bool
    {
        return in_array($this->status, ['pending', 'failed'], true);
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
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
