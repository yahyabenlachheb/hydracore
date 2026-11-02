<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $brand_id
 * @property string $device_type
 * @property string|null $device_info
 * @property string $status pending|active|expired|converted|rejected
 * @property Carbon|null $expires_at
 * @property int|null $converted_subscription_id
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 */
class Trial extends Model
{
    use Auditable, BelongsToBrand;

    public const STATUSES = ['pending' => 'Pending', 'active' => 'Active', 'expired' => 'Expired', 'converted' => 'Converted', 'rejected' => 'Rejected'];

    /** How long a trial runs when approved without an end date. */
    public const DEFAULT_HOURS = 24;

    protected $fillable = ['customer_id', 'brand_id', 'device_type', 'device_info', 'status', 'expires_at', 'converted_subscription_id', 'ip_address'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * @return BelongsToMany<Pack, $this>
     */
    public function packs(): BelongsToMany
    {
        return $this->belongsToMany(Pack::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function convertedSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'converted_subscription_id');
    }
}
