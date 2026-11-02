<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A channel or VOD pack. Shared by all brands.
 *
 * @property int $id
 * @property string $type channels|vods
 * @property string $code
 * @property string $name
 * @property bool $is_active
 */
class Pack extends Model
{
    use Auditable;

    public const TYPES = ['channels' => 'Channels', 'vods' => 'VOD'];

    protected $fillable = ['type', 'code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return BelongsToMany<Subscription, $this>
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'subscription_pack');
    }

    /**
     * @return BelongsToMany<Trial, $this>
     */
    public function trials(): BelongsToMany
    {
        return $this->belongsToMany(Trial::class);
    }
}
