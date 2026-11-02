<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fills created_by / updated_by from the authenticated user.
 *
 * Models declare which audit columns they have:
 *     protected array $auditColumns = ['created_by', 'updated_by'];
 *
 * Nothing is written when nobody is authenticated (seeders, console commands, queued jobs).
 */
trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::creating(function (Model $model) {
            if (($userId = auth()->id()) === null) {
                return;
            }

            foreach (['created_by', 'updated_by'] as $column) {
                if ($model->hasAuditColumn($column)) {
                    $model->setAttribute($column, $userId);
                }
            }
        });

        static::updating(function (Model $model) {
            if (($userId = auth()->id()) !== null && $model->hasAuditColumn('updated_by')) {
                $model->setAttribute('updated_by', $userId);
            }
        });
    }

    public function hasAuditColumn(string $column): bool
    {
        return in_array($column, $this->auditColumns ?? [], true);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
