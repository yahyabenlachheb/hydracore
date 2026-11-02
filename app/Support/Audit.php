<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Writes audit_log rows. Models with the Auditable trait call this automatically; controllers call
 * it directly for changes Eloquent doesn't fire events for, such as syncing pivot tables.
 */
class Audit
{
    public const MASK = '••••••';

    /** Columns whose changes alone aren't worth a log entry. */
    private const IGNORED = ['created_at', 'updated_at', 'created_by', 'updated_by', 'remember_token'];

    /**
     * @param  'insert'|'update'|'delete'  $action
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function record(Model $model, string $action, ?array $old, ?array $new, ?string $description = null): AuditLog
    {
        return AuditLog::create([
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'action' => $action,
            'description' => $description ?? self::describe($model, $action),
            'user_id' => auth()->id(),
            'old_values' => $old === null ? null : self::clean($model, $old),
            'new_values' => $new === null ? null : self::clean($model, $new),
        ]);
    }

    /**
     * Logs an update to a model's attributes, skipping no-op saves (only timestamps/audit columns changed).
     */
    public static function recordUpdate(Model $model): void
    {
        $changed = array_diff(array_keys($model->getChanges()), self::IGNORED);

        if ($changed === []) {
            return;
        }

        $old = array_intersect_key($model->getOriginal(), array_flip($changed));
        $new = array_intersect_key($model->getAttributes(), array_flip($changed));

        self::record($model, 'update', $old, $new);
    }

    /**
     * "Updated brand “Acme”", "Deleted user “jane”"…
     */
    public static function describe(Model $model, string $action): string
    {
        $verb = ['insert' => 'Created', 'update' => 'Updated', 'delete' => 'Deleted'][$action] ?? Str::headline($action);
        $label = Str::lower(Str::headline(class_basename($model)));
        $name = self::nameOf($model);

        return trim("{$verb} {$label}" . ($name !== null ? " “{$name}”" : " #{$model->getKey()}"));
    }

    private static function nameOf(Model $model): ?string
    {
        foreach (['name', 'full_name', 'reference_number', 'username', 'slug', 'subject', 'code', 'email'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return Str::limit($value, 80);
            }
        }

        return null;
    }

    /**
     * Drops ignored columns, masks hidden (secret) ones and makes binary values JSON-safe.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function clean(Model $model, array $values): array
    {
        $hidden = $model->getHidden();
        $clean = [];

        foreach ($values as $key => $value) {
            if (in_array($key, self::IGNORED, true)) {
                continue;
            }

            if (in_array($key, $hidden, true)) {
                $clean[$key] = $value === null ? null : self::MASK;
            } elseif (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
                $clean[$key] = '(binary)';
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
