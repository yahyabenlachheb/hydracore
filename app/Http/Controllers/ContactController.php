<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('contacts.view');

        $filters = [
            'q' => trim((string) $request->query('q')),
            'status' => array_key_exists((string) $request->query('status'), Contact::STATUSES) ? $request->query('status') : null,
        ];

        $contacts = Contact::visibleTo($this->brandId())
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('full_name', 'like', "%{$filters['q']}%")
                ->orWhere('email', 'like', "%{$filters['q']}%")
                ->orWhere('subject', 'like', "%{$filters['q']}%")))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('contacts.list', compact('contacts', 'filters'));
    }

    /**
     * Opening a new message marks it read and links it to the customer with that email.
     */
    public function show(Contact $contact): View
    {
        Gate::authorize('contacts.view');
        $this->ensureVisible($contact);

        if (Gate::allows('contacts.update')) {
            $contact->update(array_filter([
                'status' => $contact->status === 'new' ? 'read' : null,
                'customer_id' => $contact->customer_id ?? Customer::visibleTo($this->brandId())->where('email', strtolower($contact->email))->value('id'),
            ]));
        }

        return view('contacts.show', ['contact' => $contact->load('customer')]);
    }

    public function status(Request $request, Contact $contact): RedirectResponse
    {
        Gate::authorize('contacts.update');
        $this->ensureVisible($contact);

        $contact->update($request->validate(['status' => ['required', Rule::in(array_keys(Contact::STATUSES))]]));

        return back()->with('success', 'Message marked as ' . $contact->status . '.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        Gate::authorize('contacts.delete');
        $this->ensureVisible($contact);

        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Message deleted.');
    }
}
