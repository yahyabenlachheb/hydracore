<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A contact form message from a brand's website. It lands as "new" in Contact messages.
 * It never creates or changes a customer; it's only linked to one that already has that email.
 */
class ContactMessageController extends Controller
{
    /** The same message from the same email within this many minutes is a double submit. */
    private const DUPLICATE_MINUTES = 10;

    public function __invoke(Request $request): JsonResponse
    {
        /** @var Brand $brand */
        $brand = $request->attributes->get('brand');

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'ip_address' => ['nullable', 'ip'],
        ]);

        $email = strtolower(trim($data['email']));
        $message = trim($data['message']);

        $duplicate = Contact::where('brand_id', $brand->id)->where('email', $email)->where('message', $message)
            ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_MINUTES))->latest('id')->first();

        if ($duplicate) {
            return response()->json(['id' => $duplicate->id, 'status' => $duplicate->status, 'created' => false]);
        }

        $contact = Contact::create([
            'brand_id' => $brand->id,
            'customer_id' => Customer::where('email', $email)->value('id'),
            'full_name' => trim($data['full_name']),
            'email' => $email,
            'subject' => isset($data['subject']) ? trim($data['subject']) : null,
            'message' => $message,
            'status' => 'new',
            // The website passes on its visitor's IP; without it we'd only see the website's own.
            'ip_address' => $data['ip_address'] ?? $request->ip(),
        ]);

        return response()->json(['id' => $contact->id, 'status' => $contact->status, 'created' => true], 201);
    }
}
