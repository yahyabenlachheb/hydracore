<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Trial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialApiTest extends TestCase
{
    use RefreshDatabase;

    private Brand $brand;

    private string $key = 'hc_test_key';

    protected function setUp(): void
    {
        parent::setUp();

        $this->brand = Brand::create(['name' => 'Alpha', 'api_token' => hash('sha256', $this->key)]);
    }

    private function send(array $data, ?string $key = null)
    {
        return $this->withToken($key ?? $this->key)->postJson('/api/v1/trials', $data + [
            'full_name' => 'Jane Doe',
            'email' => 'Jane@Test.test',
            'phone' => '+1 555 0100',
            'ip_address' => '203.0.113.7',
            'device_info' => 'Mozilla/5.0',
        ]);
    }

    public function test_it_needs_a_valid_key(): void
    {
        $this->postJson('/api/v1/trials', [])->assertUnauthorized();
        $this->send([], 'wrong')->assertUnauthorized();

        $this->brand->update(['is_active' => false]);
        $this->send([])->assertUnauthorized();

        $this->assertSame(0, Trial::count());
    }

    public function test_it_creates_a_customer_and_a_pending_trial_on_the_keys_brand(): void
    {
        $this->send(['notes' => 'Firestick please'])->assertCreated()->assertJson(['status' => 'pending', 'created' => true]);

        $customer = Customer::sole();
        $this->assertSame('jane@test.test', $customer->email);
        $this->assertSame($this->brand->id, $customer->brand_id);
        $this->assertStringContainsString('Firestick please', $customer->notes);

        $trial = Trial::sole();
        $this->assertSame($this->brand->id, $trial->brand_id);
        $this->assertSame($customer->id, $trial->customer_id);
        $this->assertSame('203.0.113.7', $trial->ip_address);
        $this->assertSame('Not given', $trial->device_type);
    }

    public function test_a_repeat_request_reuses_the_open_trial_and_keeps_staff_details(): void
    {
        $customer = Customer::create(['full_name' => 'Jane D.', 'email' => 'jane@test.test', 'phone' => '111']);

        $first = $this->send([])->assertCreated()->json('id');
        $this->send([])->assertOk()->assertJson(['id' => $first, 'created' => false]);

        $this->assertSame(1, Trial::count());
        $this->assertSame('111', $customer->fresh()->phone);
        $this->assertSame('Jane D.', $customer->fresh()->full_name);

        // Once that trial is over, a new request is a new trial.
        Trial::find($first)->update(['status' => 'expired']);
        $this->send([])->assertCreated();
        $this->assertSame(2, Trial::count());
    }

    public function test_known_devices_are_stored_by_key(): void
    {
        $this->send(['email' => 'a@test.test', 'device_type' => 'firestick'])->assertCreated();
        $this->send(['email' => 'b@test.test', 'device_type' => 'fire tv stick'])->assertCreated();
        $this->send(['email' => 'c@test.test', 'device_type' => 'Formuler Z11'])->assertCreated();

        $this->assertSame(['firestick', 'firestick', 'Formuler Z11'], Trial::orderBy('id')->pluck('device_type')->all());
    }

    public function test_it_validates_the_request(): void
    {
        $this->send(['email' => 'not-an-email'])->assertUnprocessable()->assertJsonValidationErrors('email');
    }
}
