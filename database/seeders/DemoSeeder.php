<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Pack;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Trial;
use App\Support\OrderFulfillment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Made-up data to explore the panel with. Never run this on a production database.
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->warn('Adding sample data.');
        mt_srand(7);

        $brands = collect(['Demo TV', 'Sample Stream'])->map(fn (string $name) => Brand::firstOrCreate(['name' => $name], ['website' => 'https://' . str_replace(' ', '', strtolower($name)) . '.example.com']));

        $packs = collect([
            ['channels', 'FR', 'France'], ['channels', 'UK', 'United Kingdom'], ['channels', 'SPORT', 'Sports'],
            ['channels', 'KIDS', 'Kids'], ['vods', 'MOVIES', 'Movies'], ['vods', 'SERIES', 'Series'],
        ])->map(fn (array $pack) => Pack::firstOrCreate(['type' => $pack[0], 'code' => $pack[1]], ['name' => $pack[2]]));

        foreach ($brands as $brand) {
            $plans = collect([[1, '14.99'], [3, '34.99'], [6, '54.99'], [12, '89.99']])->map(fn (array $plan, int $i) => Plan::firstOrCreate(
                ['brand_id' => $brand->id, 'duration_months' => $plan[0]],
                ['name' => $plan[0] . ' ' . ($plan[0] === 1 ? 'Month' : 'Months'), 'price' => $plan[1], 'currency' => 'USD', 'number_of_connections' => $plan[0] >= 6 ? 2 : 1, 'sort_order' => $i],
            ));

            $this->sales($brand, $plans, $packs);
        }
    }

    private function sales(Brand $brand, $plans, $packs): void
    {
        $fulfillment = new OrderFulfillment;
        $first = ['Alex', 'Sam', 'Jordan', 'Taylor', 'Morgan', 'Casey', 'Riley', 'Jamie', 'Robin', 'Charlie'];
        $last = ['Martin', 'Bernard', 'Smith', 'Jones', 'Garcia', 'Silva', 'Dubois', 'Khan', 'Rossi', 'Novak'];
        $devices = array_keys(Subscription::DEVICE_TYPES);
        $slug = str_replace(' ', '', strtolower($brand->name));

        $today = now();

        for ($i = 0; $i < 60; $i++) {
            $name = $first[mt_rand(0, 9)] . ' ' . $last[mt_rand(0, 9)];
            $signedUp = $today->copy()->subDays(mt_rand(0, 95))->subMinutes(mt_rand(0, 1400));
            Carbon::setTestNow($signedUp);

            $customer = Customer::firstOrCreate(['email' => "customer{$i}@{$slug}.example.com"], [
                'brand_id' => $brand->id,
                'full_name' => $name,
                'phone' => '+1 555 01' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'country' => ['US', 'FR', 'GB', 'CA', 'DE'][mt_rand(0, 4)],
            ]);
            $line = ['device_type' => $devices[mt_rand(0, count($devices) - 1)], 'device_info' => sprintf('00:1A:79:%02X:%02X:%02X', mt_rand(0, 255), mt_rand(0, 255), $i), 'packs' => $packs->random(mt_rand(1, 4))->pluck('id')->all()];

            if ($i % 4 === 0) {
                $trial = Trial::create(['customer_id' => $customer->id, 'brand_id' => $brand->id, 'device_type' => $line['device_type'], 'device_info' => $line['device_info'], 'status' => $i % 8 === 0 ? 'active' : 'expired', 'expires_at' => now()->addDay(), 'ip_address' => '198.51.100.' . mt_rand(1, 20)]);
                $trial->packs()->sync($line['packs']);
            }

            $roll = mt_rand(1, 100);
            if ($roll <= 15) {
                $this->order($customer, $brand, $plans->random());
            } elseif ($roll <= 90) {
                $order = $this->order($customer, $brand, $plans->random());
                $fulfillment->pay($order, ['provider' => ['paypal', 'stripe', 'crypto'][mt_rand(0, 2)], 'provider_payment_id' => 'demo_' . uniqid()], [...$line, 'trial_id' => isset($trial) && $i % 12 === 0 ? $trial->id : null]);
            }
            unset($trial);
        }

        foreach ([['Can’t connect on my TV', 'Hello, the app shows a loading screen since yesterday.'], ['Renewal price', 'How much is the 12-month plan with two screens?'], [null, 'Do you offer a trial for MAG boxes?']] as $n => [$subject, $message]) {
            Carbon::setTestNow($today->copy()->subHours($n * 7));
            Contact::create(['brand_id' => $brand->id, 'full_name' => 'Pat Visitor ' . ($n + 1), 'email' => "visitor{$n}@example.com", 'subject' => $subject, 'message' => $message, 'ip_address' => '203.0.113.' . ($n + 1)]);
            Carbon::setTestNow();
        }

        Carbon::setTestNow();
        $this->command?->call('lines:expire');
    }

    private function order(Customer $customer, Brand $brand, Plan $plan): Order
    {
        return Order::create([
            'reference_number' => Order::newReference(),
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'brand_id' => $brand->id,
            'plan_name' => $plan->name,
            'duration_months' => $plan->duration_months,
            'subtotal' => $plan->price,
            'total' => $plan->price,
            'currency' => $plan->currency,
        ]);
    }
}
