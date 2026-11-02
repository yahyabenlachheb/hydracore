<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Support\BrandContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchBrandController extends Controller
{
    public function __invoke(Request $request, Brand $brand, BrandContext $context): RedirectResponse
    {
        abort_unless($context->canSwitchTo($brand), 403);

        $context->switchTo($brand);

        // Picked from the post-login brand picker: continue to the page the user originally asked for.
        if ($request->boolean('continue')) {
            return redirect()->intended(route('home'));
        }

        return back()->with('success', "Switched to {$brand->name}.");
    }
}
