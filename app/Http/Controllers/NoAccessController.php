<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureUserHasAccess;
use App\Support\BrandContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoAccessController extends Controller
{
    public function __invoke(Request $request, BrandContext $brands): View|RedirectResponse
    {
        // Access may have been granted since; don't leave the user stuck here.
        if (EnsureUserHasAccess::hasAccess($request, $brands)) {
            return redirect()->route('brands.choose');
        }

        return view('auth.no-access', [
            'missing' => EnsureUserHasAccess::missing($request, $brands),
        ]);
    }
}
