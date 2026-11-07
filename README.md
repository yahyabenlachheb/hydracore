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

Each brand's website calls the API with its own key, and the brand comes from the key:

```sh
php artisan brands:api-key "SKYWINGIPTV"            # prints a new key once; the old one stops working
php artisan brands:api-key "SKYWINGIPTV" --revoke
```

`POST /api/v1/trials` with `Authorization: Bearer <key>` and JSON `full_name`, `email` (required),
`phone`, `country` (2 letters), `device_type`, `device_info`, `ip_address` (the visitor's), `notes`.
It finds or creates the customer by email, filling only empty fields and appending notes, and adds a
pending trial. If that customer already has a pending or active trial on the brand, it returns that one
(200, `"created": false`) instead of a second one (201).

`POST /api/v1/contacts` with `full_name`, `email`, `message` (required), `subject`, `ip_address`.
It stores a "new" contact message. It never creates or changes a customer, only links one that already
has that email. The same message from the same email within 10 minutes is stored once.

Both are limited to 60 requests a minute.

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
