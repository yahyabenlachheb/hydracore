<?php

namespace App\Support;

/**
 * Permissions the code checks by slug (via @can / Gate). They are seeded by PermissionSeeder
 * and locked in the UI: renaming or deleting one would silently break those checks.
 */
final class SystemPermissions
{
    /** @var array<string, string> slug => name */
    public const ALL = [
        'customers.view' => 'View customers',
        'customers.create' => 'Create customers',
        'customers.update' => 'Edit customers',
        'customers.delete' => 'Archive and restore customers',

        'subscriptions.view' => 'View subscriptions',
        'subscriptions.create' => 'Create subscriptions by hand',
        'subscriptions.update' => 'Edit, suspend and cancel subscriptions',

        'orders.view' => 'View orders',
        'orders.create' => 'Create orders and renewals',
        'orders.pay' => 'Record payments on orders',
        'orders.cancel' => 'Cancel orders',
        'orders.refund' => 'Refund orders',

        'payments.view' => 'View payments',

        'trials.view' => 'View trials',
        'trials.create' => 'Create trials',
        'trials.update' => 'Edit, approve and reject trials',
        'trials.delete' => 'Delete trials',

        'contacts.view' => 'View contact messages',
        'contacts.update' => 'Change the status of contact messages',
        'contacts.delete' => 'Delete contact messages',

        'plans.view' => 'View plans',
        'plans.create' => 'Create plans',
        'plans.update' => 'Edit plans',
        'plans.delete' => 'Delete plans',

        'packs.view' => 'View packs',
        'packs.create' => 'Create packs',
        'packs.update' => 'Edit packs',
        'packs.delete' => 'Delete packs',

        'brands.view' => 'View brands',
        'brands.create' => 'Create brands',
        'brands.update' => 'Edit brands',
        'brands.delete' => 'Delete brands',

        'users.view' => 'View users',
        'users.create' => 'Create users',
        'users.update' => 'Edit users',
        'users.delete' => 'Delete users',

        'roles.view' => 'View roles',
        'roles.create' => 'Create roles',
        'roles.update' => 'Edit roles',
        'roles.delete' => 'Delete roles',

        'permissions.view' => 'View permissions',
        'permissions.create' => 'Create permissions',
        'permissions.update' => 'Edit permissions',
        'permissions.delete' => 'Delete permissions',

        'audit-logs.view' => 'View audit logs',
    ];

    public static function has(string $slug): bool
    {
        return array_key_exists($slug, self::ALL);
    }
}
