<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('payments.view');

        $filters = [
            'q' => trim((string) $request->query('q')),
            'status' => array_key_exists((string) $request->query('status'), Payment::STATUSES) ? $request->query('status') : null,
            'provider' => array_key_exists((string) $request->query('provider'), Payment::PROVIDERS) ? $request->query('provider') : null,
        ];

        $payments = Payment::visibleTo($this->brandId())
            ->with('order:id,reference_number,customer_id', 'order.customer:id,full_name,email')
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('provider_payment_id', 'like', "%{$filters['q']}%")
                ->orWhereHas('order', fn ($query) => $query->where('reference_number', 'like', "%{$filters['q']}%"))))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['provider'], fn ($query, $provider) => $query->where('provider', $provider))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('payments.list', compact('payments', 'filters'));
    }

    public function show(Payment $payment): View
    {
        Gate::authorize('payments.view');
        $this->ensureVisible($payment);

        return view('payments.show', ['payment' => $payment->load('order.customer')]);
    }
}
