<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A contact form message.
 *
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $brand_id
 * @property string $full_name
 * @property string $email
 * @property string|null $subject
 * @property string $message
 * @property string $status new|read|replied|closed
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 */
class Contact extends Model
{
    use Auditable, BelongsToBrand;

    public const STATUSES = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed'];

    protected $fillable = ['customer_id', 'brand_id', 'full_name', 'email', 'subject', 'message', 'status', 'ip_address'];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }
}
