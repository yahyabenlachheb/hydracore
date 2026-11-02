# HydraCore

Admin panel for selling IPTV subscriptions under several brands: customers, plans, packs, orders,
payments, subscriptions, trials and contact messages. Laravel 12, Blade, Tailwind 4, MariaDB.

## Run it locally

```sh
composer install
npm install && npm run build
cp .env.example .env            # then set DB_* and, optionally, ADMIN_*
php artisan key:generate
php artisan migrate --seed      # prints the admin password unless ADMIN_PASSWORD is set
php artisan serve
```

Sample data, for exploring only: `php artisan db:seed --class=DemoSeeder`.
Back to an empty database, **deleting everything**: `php artisan migrate:fresh --seed`.

## In production

Run the scheduler every minute. It expires lines and trials that passed their end date:

```cron
* * * * * cd /path/to/hydracore && php artisan schedule:run >> /dev/null 2>&1
```

Also set `APP_ENV=production` and `APP_DEBUG=false`, and use a database user limited to this database.

## How it fits together

- **Brands.** Plans, orders, trials and contacts belong to the brand picked in the sidebar. Rows
  without a brand are shared. Customers are visible on any brand they signed up, bought or tried on,
  because an email is unique across brands.
- **Selling.** An order copies the plan's price, then `App\Support\OrderFulfillment` does the rest
  when a payment is recorded: it creates or extends the line in one transaction, with the order locked.
- **Access.** Super admins can do anything. Everyone else needs a role with permissions and an assigned
  brand. Slugs are listed in `App\Support\SystemPermissions`; run `php artisan db:seed --class=PermissionSeeder`
  after adding one.
- **History.** Every change to a model is recorded in the audit log.

## Connecting the website

The panel doesn't include the public checkout or a payment webhook. A webhook should call
`OrderFulfillment::pay()` or `::fail()`, so it follows the same rules as the panel.

`orders` has no columns for the device and packs chosen at checkout, yet a new line needs them. Staff
enter them when recording the payment; an automatic webhook needs somewhere to read them from, such as
new columns on `orders`.

## Tests

```sh
php artisan test
```

They run on in-memory SQLite, which skips the CHECK constraints: those only exist on MariaDB/MySQL.
