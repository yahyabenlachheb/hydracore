<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One payment attempt on an order, with the gateway's raw reply.
 *
 * @property int $id
 * @property int $order_id
 * @property string $provider
 * @property string|null $provider_payment_id
 * @property string $amount
 * @property string $currency
 * @property string $status pending|succeeded|failed|refunded
 * @property string|null $failure_reason
 * @property array|null $payload
 */
class Payment extends Model
{
    use Auditable;

    public const STATUSES = ['pending' => 'Pending', 'succeeded' => 'Succeeded', 'failed' => 'Failed', 'refunded' => 'Refunded'];

    public const PROVIDERS = ['paypal' => 'PayPal', 'stripe' => 'Stripe', 'crypto' => 'Crypto', 'bank_transfer' => 'Bank transfer', 'cash' => 'Cash', 'manual' => 'Other / manual'];

    protected $fillable = ['order_id', 'provider', 'provider_payment_id', 'amount', 'currency', 'status', 'failure_reason', 'payload'];

    /** Webhook bodies can hold personal data and are large: masked in the audit log. */
    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    public function scopeVisibleTo(Builder $query, ?int $brandId): Builder
    {
        return $query->whereIn('payments.order_id', Order::visibleTo($brandId)->select('id'));
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
