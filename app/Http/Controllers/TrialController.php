<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrialRequest;
use App\Models\Customer;
use App\Models\Pack;
use App\Models\Trial;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TrialController extends Controller
{
    private const PER_PAGE = 25;

    /** action => statuses it can start from */
    private const TRANSITIONS = [
        'approve' => ['pending', 'rejected'],
        'reject' => ['pending', 'active'],
        'expire' => ['active'],
    ];

    public function index(Request $request): View
    {
        Gate::authorize('trials.view');

        $filters = [
            'q' => trim((string) $request->query('q')),
            'status' => array_key_exists((string) $request->query('status'), Trial::STATUSES) ? $request->query('status') : null,
        ];

        $trials = Trial::visibleTo($this->brandId())
            ->with(['customer:id,full_name,email', 'packs:id,name'])
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('device_info', 'like', "%{$filters['q']}%")
                ->orWhere('ip_address', 'like', "%{$filters['q']}%")
                ->orWhereHas('customer', fn ($query) => $query->matching($filters['q']))))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('trials.list', compact('trials', 'filters'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('trials.create');

        return view('trials.create', [
            'trial' => new Trial,
            'customer' => $request->integer('customer') ? Customer::visibleTo($this->brandId())->find($request->integer('customer')) : null,
            'packs' => $this->packOptions(),
        ]);
    }

    public function store(TrialRequest $request): RedirectResponse
    {
        $trial = DB::transaction(function () use ($request) {
            $trial = Trial::create([...$request->safe()->except('packs'), 'brand_id' => $this->brandId(), 'status' => 'pending']);
            $trial->packs()->sync($request->validated('packs'));

            return $trial;
        });

        return redirect()->route('trials.show', $trial)->with('success', 'Trial created. Approve it to start it.');
    }

    public function show(Trial $trial): View
    {
        Gate::authorize('trials.view');
        $this->ensureVisible($trial);

        // The same person coming back for more free trials shows up as the same IP or device.
        $others = fn (string $column, ?string $value) => $value === null ? 0
            : Trial::visibleTo($this->brandId())->where($column, $value)->whereKeyNot($trial->id)->count();

        return view('trials.show', [
            'trial' => $trial->load(['customer', 'packs', 'convertedSubscription.plan:id,name']),
            'sameIp' => $others('ip_address', $trial->ip_address),
            'sameDevice' => $others('device_info', $trial->device_info),
            'sameCustomer' => $others('customer_id', (string) $trial->customer_id),
            'transitions' => array_keys(array_filter(self::TRANSITIONS, fn (array $from) => in_array($trial->status, $from, true))),
        ]);
    }

    public function edit(Trial $trial): View
    {
        Gate::authorize('trials.update');
        $this->ensureVisible($trial);

        return view('trials.edit', [
            'trial' => $trial->load(['customer', 'packs:id']),
            'customer' => $trial->customer,
            'packs' => $this->packOptions($trial),
        ]);
    }

    public function update(TrialRequest $request, Trial $trial): RedirectResponse
    {
        $this->ensureVisible($trial);

        DB::transaction(function () use ($request, $trial) {
            $trial->update($request->safe()->except(['packs', 'customer_id']));

            $old = $trial->packs()->orderBy('code')->pluck('code')->all();
            $trial->packs()->sync($request->validated('packs'));
            $new = $trial->packs()->orderBy('code')->pluck('code')->all();

            // Pivot syncs don't fire model events, so log pack changes explicitly.
            if ($old !== $new) {
                Audit::record($trial, 'update', ['packs' => $old], ['packs' => $new], "Changed packs of trial #{$trial->id}");
            }
        });

        return redirect()->route('trials.show', $trial)->with('success', 'Trial updated.');
    }

    public function status(Request $request, Trial $trial): RedirectResponse
    {
        Gate::authorize('trials.update');
        $this->ensureVisible($trial);

        $action = (string) $request->input('action');

        if (! in_array($trial->status, self::TRANSITIONS[$action] ?? [], true)) {
            return back()->with('error', "A {$trial->status} trial can’t be changed this way.");
        }

        $trial->update(match ($action) {
            'approve' => ['status' => 'active', 'expires_at' => $trial->expires_at?->isFuture() ? $trial->expires_at : now()->addHours(Trial::DEFAULT_HOURS)],
            'reject' => ['status' => 'rejected'],
            'expire' => ['status' => 'expired', 'expires_at' => now()],
        });

        return back()->with('success', 'Trial is now ' . $trial->status . '.');
    }

    public function destroy(Trial $trial): RedirectResponse
    {
        Gate::authorize('trials.delete');
        $this->ensureVisible($trial);

        if ($trial->status === 'converted') {
            return back()->with('error', 'This trial led to a sale; it’s kept for conversion reporting.');
        }

        $trial->delete();

        return redirect()->route('trials.index')->with('success', 'Trial deleted.');
    }

    /**
     * @return Collection<int, Pack>
     */
    private function packOptions(?Trial $trial = null): Collection
    {
        return Pack::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhereIn('id', $trial?->packs->modelKeys() ?? []))
            ->orderBy('type')->orderBy('name')->get();
    }
}
