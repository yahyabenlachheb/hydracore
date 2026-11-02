<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasAuditColumns;
use App\Support\SystemPermissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Role> $roles
 */
class Permission extends Model
{
    use Auditable, HasAuditColumns;

    protected array $auditColumns = ['created_by', 'updated_by'];

    protected $fillable = [
        'name',
        'slug',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')->withPivot('created_at');
    }

    /**
     * Checked by slug in the code, so it can't be renamed or deleted.
     */
    public function isSystem(): bool
    {
        return SystemPermissions::has($this->slug);
    }
}
