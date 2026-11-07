<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Trial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * A trial request from a brand's website. It lands as a pending trial for staff to approve.
 */
class TrialRequestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Brand $brand */
        $brand = $request->attributes->get('brand');

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'size:2'],
            'device_type' => ['nullable', 'string', 'max:50'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        [$trial, $created] = DB::transaction(function () use ($data, $brand, $request) {
            $customer = $this->customerFor($data, $brand);

            // Sending the form twice shouldn't queue two trials.
            $open = Trial::where('customer_id', $customer->id)->where('brand_id', $brand->id)
                ->whereIn('status', ['pending', 'active'])->latest('id')->first();

            if ($open) {
                return [$open, false];
            }

            return [Trial::create([
                'customer_id' => $customer->id,
                'brand_id' => $brand->id,
                'device_type' => $this->deviceType($data['device_type'] ?? null),
                'device_info' => $data['device_info'] ?? null,
                // The website passes on its visitor's IP; without it we'd only see the website's own.
                'ip_address' => $data['ip_address'] ?? $request->ip(),
                'status' => 'pending',
            ]), true];
        });

        return response()->json(['id' => $trial->id, 'status' => $trial->status, 'created' => $created], $created ? 201 : 200);
    }

    /**
     * A known device type, given as its key ("firestick") or label ("Fire TV Stick"), becomes the key,
     * so the panel shows it like any other. Anything else is kept as sent.
     */
    private function deviceType(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 'Not given';
        }

        foreach (Subscription::DEVICE_TYPES as $key => $label) {
            if (strcasecmp($value, $key) === 0 || strcasecmp($value, $label) === 0) {
                return $key;
            }
        }

        return $value;
    }

    /**
     * An email is one customer across brands, so a returning visitor reuses their record.
     * Their details only fill gaps: what staff entered wins over what a visitor types.
     *
     * @param  array<string, mixed>  $data
     */
    private function customerFor(array $data, Brand $brand): Customer
    {
        $email = strtolower(trim($data['email']));
        $country = isset($data['country']) ? strtoupper($data['country']) : null;

        $customer = Customer::withTrashed()->where('email', $email)->lockForUpdate()->first();

        if (! $customer) {
            $customer = new Customer(['brand_id' => $brand->id, 'full_name' => $data['full_name'], 'email' => $email]);
        } elseif ($customer->trashed()) {
            $customer->restore();
        }

        $customer->phone ??= $data['phone'] ?? null;
        $customer->country ??= $country;

        if (! empty($data['notes'])) {
            $note = '[' . now()->format('Y-m-d H:i') . " trial request, {$brand->name}] " . trim($data['notes']);
            $customer->notes = $customer->notes ? $customer->notes . "\n\n" . $note : $note;
        }

        $customer->save();

        return $customer;
    }
}
