<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Pack;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Trial;
use App\Models\User;
use App\Support\BrandContext;
use App\Support\OrderFulfillment;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SalesFlowTest extends TestCase
{
    use RefreshDatabase;

    private Brand $brand;

    private Plan $plan;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brand = Brand::create(['name' => 'Alpha']);
        $this->plan = $this->makePlan($this->brand);
        $this->admin = User::create(['name' => 'Admin', 'username' => 'admin', 'email' => 'admin@test.test', 'password' => 'password', 'is_super_admin' => true]);
        $this->actingAs($this->admin)->withSession(['current_brand_id' => $this->brand->id]);
    }

    private function makePlan(Brand $brand, int $months = 3, string $price = '30.00'): Plan
    {
        return Plan::create(['brand_id' => $brand->id, 'name' => "{$months} Months", 'duration_months' => $months, 'price' => $price]);
    }

    private function makeCustomer(?Brand $brand = null, string $email = 'jane@test.test'): Customer
    {
        return Customer::create(['brand_id' => ($brand ?? $this->brand)->id, 'full_name' => 'Jane Doe', 'email' => $email]);
    }

    private function makeOrder(Customer $customer, array $attributes = []): Order
    {
        return Order::create([
            'reference_number' => Order::newReference(), 'customer_id' => $customer->id, 'plan_id' => $this->plan->id, 'brand_id' => $this->brand->id,
            'plan_name' => $this->plan->name, 'duration_months' => $this->plan->duration_months, 'subtotal' => '30.00', 'total' => '30.00', 'currency' => 'USD',
            ...$attributes,
        ]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        auth()->logout();

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_order_copies_the_price_and_discount_from_the_plan(): void
    {
        $customer = $this->makeCustomer();

        $this->post(route('orders.store'), ['customer_id' => $customer->id, 'plan_id' => $this->plan->id, 'discount' => '5.50'])->assertRedirect();

        $order = Order::sole();
        $this->assertSame('30.00', $order->subtotal);
        $this->assertSame('5.50', $order->discount);
        $this->assertSame('24.50', $order->total);
        $this->assertSame('new', $order->type);
        $this->assertSame($this->brand->id, $order->brand_id);

        $this->plan->update(['price' => '99.00']);
        $this->assertSame('30.00', $order->fresh()->subtotal);
    }

    public function test_discount_cannot_exceed_the_price(): void
    {
        $this->post(route('orders.store'), ['customer_id' => $this->makeCustomer()->id, 'plan_id' => $this->plan->id, 'discount' => '30.01'])
            ->assertSessionHasErrors('discount');
    }

    public function test_paying_a_new_order_activates_a_line_with_its_packs(): void
    {
        Carbon::setTestNow('2026-01-31 12:00:00');
        $pack = Pack::create(['type' => 'channels', 'code' => 'FR', 'name' => 'France']);
        $order = $this->makeOrder($this->makeCustomer(), ['duration_months' => 1]);

        $this->post(route('orders.pay', $order), ['outcome' => 'succeeded', 'provider' => 'paypal', 'provider_payment_id' => 'TX1', 'device_type' => 'mag', 'device_info' => 'AA:BB', 'packs' => [$pack->id]])
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $line = Subscription::sole();
        $this->assertSame('paid', $order->status);
        $this->assertSame($line->id, $order->subscription_id);
        $this->assertSame('active', $line->status);
        $this->assertSame([$pack->id], $line->packs->modelKeys());
        // Jan 31 + 1 month is Feb 28, as with SQL's INTERVAL, not Mar 3.
        $this->assertSame('2026-02-28', $line->expires_at->toDateString());
        $this->assertSame('30.00', $order->payments()->sole()->amount);
    }

    public function test_a_new_order_cannot_be_paid_without_a_device(): void
    {
        $order = $this->makeOrder($this->makeCustomer());

        $this->post(route('orders.pay', $order), ['outcome' => 'succeeded', 'provider' => 'paypal'])->assertSessionHasErrors('device_type');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_an_order_cannot_be_fulfilled_twice(): void
    {
        $order = $this->makeOrder($this->makeCustomer());
        $data = ['outcome' => 'succeeded', 'provider' => 'paypal', 'device_type' => 'mag'];

        $this->post(route('orders.pay', $order), $data);
        $this->post(route('orders.pay', $order), $data)->assertSessionHasErrors('order');

        $this->assertSame(1, Subscription::count());
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_the_same_transaction_cannot_be_recorded_twice(): void
    {
        $customer = $this->makeCustomer();
        $this->post(route('orders.pay', $this->makeOrder($customer)), ['outcome' => 'succeeded', 'provider' => 'paypal', 'provider_payment_id' => 'TX1', 'device_type' => 'mag']);

        $this->post(route('orders.pay', $this->makeOrder($customer)), ['outcome' => 'succeeded', 'provider' => 'paypal', 'provider_payment_id' => 'TX1', 'device_type' => 'mag'])
            ->assertSessionHasErrors('provider_payment_id');
    }

    public function test_a_failed_attempt_leaves_the_order_payable(): void
    {
        $order = $this->makeOrder($this->makeCustomer());

        $this->post(route('orders.pay', $order), ['outcome' => 'failed', 'provider' => 'stripe', 'failure_reason' => 'Declined']);
        $this->assertSame('failed', $order->fresh()->status);
        $this->assertSame(0, Subscription::count());

        $this->post(route('orders.pay', $order), ['outcome' => 'succeeded', 'provider' => 'stripe', 'device_type' => 'mag']);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, Subscription::count());
    }

    public function test_renewal_keeps_the_time_left(): void
    {
        Carbon::setTestNow('2026-03-01 00:00:00');
        $customer = $this->makeCustomer();
        $line = Subscription::create(['customer_id' => $customer->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'active', 'starts_at' => now()->subMonth(), 'expires_at' => '2026-03-20 00:00:00']);
        $order = $this->makeOrder($customer, ['type' => 'renewal', 'subscription_id' => $line->id]);

        $this->post(route('orders.pay', $order), ['outcome' => 'succeeded', 'provider' => 'cash']);

        $this->assertSame('2026-06-20', $line->fresh()->expires_at->toDateString());
    }

    public function test_renewing_an_expired_line_counts_from_today_and_reactivates_it(): void
    {
        Carbon::setTestNow('2026-03-01 00:00:00');
        $customer = $this->makeCustomer();
        $line = Subscription::create(['customer_id' => $customer->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'expired', 'starts_at' => '2025-01-01', 'expires_at' => '2025-02-01']);
        $order = $this->makeOrder($customer, ['type' => 'renewal', 'subscription_id' => $line->id]);

        $this->post(route('orders.pay', $order), ['outcome' => 'succeeded', 'provider' => 'cash']);

        $line->refresh();
        $this->assertSame('active', $line->status);
        $this->assertSame('2026-06-01', $line->expires_at->toDateString());
    }

    public function test_a_renewal_must_be_for_the_customers_own_line(): void
    {
        $line = Subscription::create(['customer_id' => $this->makeCustomer()->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag']);
        $other = $this->makeCustomer(email: 'other@test.test');

        $this->post(route('orders.store'), ['customer_id' => $other->id, 'plan_id' => $this->plan->id, 'subscription_id' => $line->id])
            ->assertSessionHasErrors('subscription_id');
    }

    public function test_refund_marks_order_and_payment_and_can_cancel_the_line(): void
    {
        $order = $this->makeOrder($this->makeCustomer());
        (new OrderFulfillment)->pay($order, ['provider' => 'paypal'], ['device_type' => 'mag']);

        $this->post(route('orders.refund', $order), ['cancel_line' => '1']);

        $order->refresh();
        $this->assertSame('refunded', $order->status);
        $this->assertSame('refunded', $order->payments()->sole()->status);
        $this->assertSame('cancelled', $order->subscription->status);
    }

    public function test_paying_can_convert_a_trial(): void
    {
        $customer = $this->makeCustomer();
        $trial = Trial::create(['customer_id' => $customer->id, 'brand_id' => $this->brand->id, 'device_type' => 'mag', 'status' => 'active']);

        $this->post(route('orders.pay', $this->makeOrder($customer)), ['outcome' => 'succeeded', 'provider' => 'cash', 'device_type' => 'mag', 'trial_id' => $trial->id]);

        $trial->refresh();
        $this->assertSame('converted', $trial->status);
        $this->assertSame(Subscription::sole()->id, $trial->converted_subscription_id);
    }

    public function test_expire_command_only_touches_active_records_past_their_date(): void
    {
        $customer = $this->makeCustomer();
        $make = fn (string $status, string $expires) => Subscription::create(['customer_id' => $customer->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => $status, 'starts_at' => '2020-01-01', 'expires_at' => $expires]);
        $late = $make('active', now()->subDay());
        $fine = $make('active', now()->addDay());
        $suspended = $make('suspended', now()->subDay());
        $trial = Trial::create(['customer_id' => $customer->id, 'device_type' => 'mag', 'status' => 'active', 'expires_at' => now()->subHour()]);

        $this->artisan('lines:expire')->assertSuccessful();

        $this->assertSame('expired', $late->fresh()->status);
        $this->assertSame('active', $fine->fresh()->status);
        $this->assertSame('suspended', $suspended->fresh()->status);
        $this->assertSame('expired', $trial->fresh()->status);
    }

    public function test_activating_a_subscription_by_hand_gets_an_end_date_from_the_plan(): void
    {
        Carbon::setTestNow('2026-05-01 00:00:00');

        $this->post(route('subscriptions.store'), ['customer_id' => $this->makeCustomer()->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'active'])->assertRedirect();

        $this->assertSame('2026-08-01', Subscription::sole()->expires_at->toDateString());
    }

    public function test_an_end_date_before_the_start_is_refused(): void
    {
        $this->post(route('subscriptions.store'), ['customer_id' => $this->makeCustomer()->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'pending', 'starts_at' => '2026-05-01T00:00', 'expires_at' => '2026-04-01T00:00'])
            ->assertSessionHasErrors('expires_at');
    }

    public function test_an_end_date_without_a_start_is_accepted(): void
    {
        $this->post(route('subscriptions.store'), ['customer_id' => $this->makeCustomer()->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'pending', 'expires_at' => '2027-04-01T00:00'])
            ->assertSessionHasNoErrors();

        $this->assertSame('2027-04-01', Subscription::sole()->expires_at->toDateString());
    }

    public function test_resuming_after_the_end_date_expires_the_line(): void
    {
        $line = Subscription::create(['customer_id' => $this->makeCustomer()->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'suspended', 'starts_at' => '2020-01-01', 'expires_at' => now()->subDay()]);

        $this->post(route('subscriptions.status', $line), ['action' => 'resume']);

        $this->assertSame('expired', $line->fresh()->status);
    }

    public function test_other_brands_records_are_not_reachable(): void
    {
        $other = Brand::create(['name' => 'Beta']);
        $otherPlan = $this->makePlan($other);
        $customer = $this->makeCustomer($other, 'beta@test.test');
        $order = Order::create(['reference_number' => 'REF-B', 'customer_id' => $customer->id, 'plan_id' => $otherPlan->id, 'brand_id' => $other->id, 'plan_name' => 'x', 'duration_months' => 1, 'subtotal' => 1, 'total' => 1, 'currency' => 'USD']);
        $line = Subscription::create(['customer_id' => $customer->id, 'plan_id' => $otherPlan->id, 'device_type' => 'mag']);
        $trial = Trial::create(['customer_id' => $customer->id, 'brand_id' => $other->id, 'device_type' => 'mag']);
        $contact = Contact::create(['brand_id' => $other->id, 'full_name' => 'X', 'email' => 'x@test.test', 'message' => 'Hi']);

        foreach ([
            route('customers.show', $customer), route('orders.show', $order), route('subscriptions.show', $line),
            route('trials.show', $trial), route('contacts.show', $contact), route('plans.edit', $otherPlan),
        ] as $url) {
            $this->get($url)->assertNotFound();
        }

        $this->post(route('orders.pay', $order), ['outcome' => 'failed', 'provider' => 'cash'])->assertNotFound();
        $this->post(route('orders.store'), ['customer_id' => $customer->id, 'plan_id' => $this->plan->id])->assertSessionHasErrors('customer_id');
        $this->post(route('orders.store'), ['customer_id' => $this->makeCustomer()->id, 'plan_id' => $otherPlan->id])->assertSessionHasErrors('plan_id');
        $this->get(route('customers.index'))->assertDontSee('beta@test.test');
        $this->getJson(route('customers.search', ['q' => 'beta']))->assertExactJson([]);
    }

    public function test_a_customer_who_bought_on_this_brand_is_visible_here_too(): void
    {
        $customer = $this->makeCustomer(Brand::create(['name' => 'Beta']), 'both@test.test');
        $this->makeOrder($customer);

        $this->get(route('customers.show', $customer))->assertOk();
    }

    public function test_customer_with_a_running_line_cannot_be_archived_and_archived_ones_can_be_restored(): void
    {
        $customer = $this->makeCustomer();
        $line = Subscription::create(['customer_id' => $customer->id, 'plan_id' => $this->plan->id, 'device_type' => 'mag', 'status' => 'active', 'starts_at' => now(), 'expires_at' => now()->addDay()]);

        $this->delete(route('customers.destroy', $customer))->assertSessionHas('error');
        $this->assertNotSoftDeleted($customer);

        $line->update(['status' => 'cancelled']);
        $this->delete(route('customers.destroy', $customer));
        $this->assertSoftDeleted($customer);

        // The email stays taken while archived.
        $this->post(route('customers.store'), ['full_name' => 'New', 'email' => 'JANE@test.test'])->assertSessionHasErrors('email');

        $this->post(route('customers.restore', $customer));
        $this->assertNotSoftDeleted($customer);
    }

    public function test_sold_plans_and_used_packs_cannot_be_deleted(): void
    {
        $this->makeOrder($this->makeCustomer());
        $this->delete(route('plans.destroy', $this->plan))->assertSessionHas('error');
        $this->assertModelExists($this->plan);

        $pack = Pack::create(['type' => 'vods', 'code' => 'M', 'name' => 'Movies']);
        Trial::create(['customer_id' => Customer::first()->id, 'device_type' => 'mag'])->packs()->attach($pack);
        $this->delete(route('packs.destroy', $pack))->assertSessionHas('error');
        $this->assertModelExists($pack);
    }

    public function test_opening_a_new_contact_marks_it_read_and_links_the_customer(): void
    {
        $customer = $this->makeCustomer();
        $contact = Contact::create(['brand_id' => $this->brand->id, 'full_name' => 'Jane', 'email' => 'JANE@test.test', 'message' => 'Hello']);

        $this->get(route('contacts.show', $contact))->assertOk()->assertSee('Hello');

        $contact->refresh();
        $this->assertSame('read', $contact->status);
        $this->assertSame($customer->id, $contact->customer_id);
    }

    public function test_permissions_gate_pages_and_navigation(): void
    {
        $this->seed(PermissionSeeder::class);
        $role = Role::create(['name' => 'Support']);
        $role->permissions()->attach(\App\Models\Permission::whereIn('slug', ['customers.view'])->pluck('id'));
        $user = User::create(['name' => 'Sup', 'username' => 'sup', 'email' => 'sup@test.test', 'password' => 'password']);
        $user->roles()->attach($role);
        $user->brands()->attach($this->brand);
        app()->forgetScopedInstances();

        $this->actingAs($user);
        $this->get(route('customers.index'))->assertOk()->assertDontSee(route('orders.index'));
        $this->get(route('orders.index'))->assertForbidden();
        $this->post(route('customers.store'), ['full_name' => 'A', 'email' => 'a@test.test'])->assertForbidden();
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_user_without_a_role_lands_on_no_access(): void
    {
        $user = User::create(['name' => 'New', 'username' => 'new', 'email' => 'new@test.test', 'password' => 'password']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('no-access'));
    }

    public function test_changes_reach_the_audit_log(): void
    {
        $customer = $this->makeCustomer();

        $this->assertDatabaseHas('audit_log', ['table_name' => 'customers', 'record_id' => $customer->id, 'action' => 'insert', 'user_id' => $this->admin->id]);
    }

    /**
     * Every page, empty and with data, so a typo in a view fails here rather than in the browser.
     */
    public function test_every_page_renders(): void
    {
        $paths = function () {
            $this->app[BrandContext::class] = new BrandContext;

            foreach (Route::getRoutes() as $route) {
                if (! in_array('GET', $route->methods(), true) || ! in_array('access', $route->gatherMiddleware(), true)) {
                    continue;
                }

                yield $route;
            }
        };

        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer);
        $paid = $this->makeOrder($customer);
        (new OrderFulfillment)->pay($paid, ['provider' => 'paypal', 'provider_payment_id' => 'TX', 'payload' => ['id' => 1]], ['device_type' => 'mag']);
        $trial = Trial::create(['customer_id' => $customer->id, 'brand_id' => $this->brand->id, 'device_type' => 'mag', 'status' => 'pending', 'ip_address' => '1.2.3.4']);
        $contact = Contact::create(['brand_id' => $this->brand->id, 'customer_id' => $customer->id, 'full_name' => 'Jane', 'email' => 'jane@test.test', 'message' => 'Hi']);
        $pack = Pack::create(['type' => 'channels', 'code' => 'FR', 'name' => 'France']);
        $role = Role::create(['name' => 'Support']);
        $permission = \App\Models\Permission::create(['name' => 'X', 'slug' => 'x.y']);

        $bindings = [
            'customer' => $customer, 'order' => $order, 'trial' => $trial, 'contact' => $contact, 'pack' => $pack, 'plan' => $this->plan, 'brand' => $this->brand,
            'subscription' => Subscription::sole(), 'payment' => \App\Models\Payment::sole(), 'user' => $this->admin, 'role' => $role, 'permission' => $permission,
            'audit_log' => \App\Models\AuditLog::first(),
        ];

        foreach ($paths() as $route) {
            $url = route($route->getName(), array_intersect_key($bindings, array_flip($route->parameterNames())));
            $response = $this->get($url);
            $this->assertContains($response->status(), [200, 302], "{$route->getName()} returned {$response->status()}");
        }

        // The paid order's page has a different layout from the pending one.
        $this->get(route('orders.show', $paid))->assertOk()->assertSee('Mark as refunded');
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Record a payment');
        $this->get(route('orders.create', ['subscription' => Subscription::sole()->id]))->assertOk()->assertSee('Renew subscription');
        $this->get(route('orders.create', ['trial' => $trial->id]))->assertOk();
        $this->get(route('dashboard'))->assertOk()->assertSee('Revenue');
    }
}
