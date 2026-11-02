<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\BrandContext;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('orders.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['coupon_code' => trim((string) $this->input('coupon_code')) ?: null]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $brandId = app(BrandContext::class)->current()?->id;

        return [
            'customer_id' => ['required', 'integer', function (string $attribute, mixed $value, Closure $fail) use ($brandId) {
                if (! Customer::visibleTo($brandId)->whereKey($value)->exists()) {
                    $fail('Choose a customer of this brand.');
                }
            }],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('brand_id', $brandId)->where('is_active', true)],
            // A renewal extends a line of the same customer on the same brand.
            'subscription_id' => ['nullable', 'integer', function (string $attribute, mixed $value, Closure $fail) use ($brandId) {
                if (! Subscription::visibleTo($brandId)->whereKey($value)->where('customer_id', $this->input('customer_id'))->exists()) {
                    $fail('This subscription doesn’t belong to the customer.');
                }
            }],
            'discount' => ['nullable', 'decimal:0,2', 'min:0', function (string $attribute, mixed $value, Closure $fail) use ($brandId) {
                $plan = Plan::visibleTo($brandId)->find($this->input('plan_id'));

                if ($plan && (float) $value > (float) $plan->price) {
                    $fail("The discount can’t be more than the plan’s price ({$plan->price}).");
                }
            }],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
