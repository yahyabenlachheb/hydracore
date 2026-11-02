<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackRequest;
use App\Models\Pack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PackController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('packs.view');

        $search = trim((string) $request->query('q'));
        $type = array_key_exists((string) $request->query('type'), Pack::TYPES) ? $request->query('type') : null;

        $packs = Pack::query()
            ->withCount(['subscriptions', 'trials'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($type, fn ($query, $type) => $query->where('type', $type))
            ->orderBy('type')->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('packs.list', compact('packs', 'search', 'type'));
    }

    public function create(): View
    {
        Gate::authorize('packs.create');

        return view('packs.create', ['pack' => new Pack(['is_active' => true, 'type' => 'channels'])]);
    }

    public function store(PackRequest $request): RedirectResponse
    {
        $pack = Pack::create($request->validated());

        return redirect()->route('packs.index')->with('success', "Pack “{$pack->name}” created.");
    }

    public function edit(Pack $pack): View
    {
        Gate::authorize('packs.update');

        return view('packs.edit', compact('pack'));
    }

    public function update(PackRequest $request, Pack $pack): RedirectResponse
    {
        $pack->update($request->validated());

        return redirect()->route('packs.index')->with('success', "Pack “{$pack->name}” updated.");
    }

    public function destroy(Pack $pack): RedirectResponse
    {
        Gate::authorize('packs.delete');

        if ($pack->subscriptions()->exists() || $pack->trials()->exists()) {
            return back()->with('error', "“{$pack->name}” is on subscriptions or trials, so it can’t be deleted. Deactivate it instead.");
        }

        $pack->delete();

        return redirect()->route('packs.index')->with('success', "Pack “{$pack->name}” deleted.");
    }
}
