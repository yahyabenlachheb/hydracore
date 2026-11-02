<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $table_name
 * @property int $record_id
 * @property string $action insert|update|delete
 * @property string|null $description
 * @property int|null $user_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property Carbon $created_at
 * @property-read User|null $user
 */
class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'audit_log';

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'description',
        'user_id',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
