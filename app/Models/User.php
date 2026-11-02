<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasAuditColumns;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property bool $is_super_admin
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Role> $roles
 * @property-read Collection<int, Brand> $brands
 * @property-read Collection<int, AuditLog> $auditLogs
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasAuditColumns, HasFactory, Notifiable;

    protected array $auditColumns = ['created_by', 'updated_by'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_super_admin',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Find a user by either username or email, for login forms.
     */
    public static function findByLogin(string $login): ?self
    {
        $field = str_contains($login, '@') ? 'email' : 'username';
        $value = $field === 'email' ? strtolower(trim($login)) : trim($login);

        return static::query()->where($field, $value)->first();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /** @var array<int, string>|null Permission slugs from all of the user's roles, loaded once per request. */
    private ?array $permissionSlugs = null;

    public function hasPermission(string $slug): bool
    {
        return in_array($slug, $this->permissionSlugs(), true);
    }

    /**
     * @return array<int, string>
     */
    public function permissionSlugs(): array
    {
        return $this->permissionSlugs ??= Permission::query()
            ->whereHas('roles.users', fn ($query) => $query->whereKey($this->getKey()))
            ->pluck('slug')
            ->all();
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn (?string $value) => $value === null ? null : strtolower(trim($value)));
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withPivot('created_at');
    }

    /**
     * @return BelongsToMany<Brand, $this>
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'user_brands')->withPivot('created_at');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
