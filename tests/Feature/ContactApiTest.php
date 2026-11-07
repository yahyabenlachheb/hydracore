<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
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
        return $this->withToken($key ?? $this->key)->postJson('/api/v1/contacts', $data + [
            'full_name' => 'Jane Doe',
            'email' => 'Jane@Test.test',
            'subject' => 'Channels',
            'message' => 'Do you have sports channels?',
            'ip_address' => '203.0.113.7',
        ]);
    }

    public function test_it_needs_a_valid_key(): void
    {
        $this->postJson('/api/v1/contacts', [])->assertUnauthorized();
        $this->send([], 'wrong')->assertUnauthorized();

        $this->assertSame(0, Contact::count());
    }

    public function test_it_stores_a_new_message_on_the_keys_brand(): void
    {
        $this->send([])->assertCreated()->assertJson(['status' => 'new', 'created' => true]);

        $contact = Contact::sole();
        $this->assertSame($this->brand->id, $contact->brand_id);
        $this->assertSame('jane@test.test', $contact->email);
        $this->assertSame('Do you have sports channels?', $contact->message);
        $this->assertSame('203.0.113.7', $contact->ip_address);
        $this->assertNull($contact->customer_id);
        $this->assertSame(0, Customer::count());
    }

    public function test_it_links_an_existing_customer_without_changing_them(): void
    {
        $customer = Customer::create(['full_name' => 'Jane D.', 'email' => 'jane@test.test']);

        $this->send([])->assertCreated();

        $this->assertSame($customer->id, Contact::sole()->customer_id);
        $this->assertSame('Jane D.', $customer->fresh()->full_name);
    }

    public function test_a_double_submit_is_stored_once(): void
    {
        $id = $this->send([])->json('id');
        $this->send([])->assertOk()->assertJson(['id' => $id, 'created' => false]);
        $this->send(['message' => 'And movies?'])->assertCreated();

        $this->assertSame(2, Contact::count());
    }

    public function test_it_validates_the_request(): void
    {
        $this->send(['message' => ''])->assertUnprocessable()->assertJsonValidationErrors('message');
    }
}
