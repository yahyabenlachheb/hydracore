<?php

namespace App\Models\Concerns;

use App\Support\Audit;
use Illuminate\Database\Eloquent\Model;

/**
 * Records every create, update and delete of the model in audit_log, with old/new values.
 * Secrets listed in the model's $hidden are masked. Pivot syncs aren't model events, so
 * controllers log those themselves with Audit::record().
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => Audit::record($model, 'insert', null, $model->getAttributes()));

        static::updated(fn (Model $model) => Audit::recordUpdate($model));

        static::deleted(fn (Model $model) => Audit::record($model, 'delete', $model->getOriginal(), null));
    }
}
