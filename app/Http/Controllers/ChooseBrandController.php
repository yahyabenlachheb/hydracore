<?php

namespace App\Http\Controllers;

use App\Support\BrandContext;
use Illuminate\View\View;

class ChooseBrandController extends Controller
{
    /**
     * Full-screen brand picker shown right after login.
     */
    public function __invoke(BrandContext $context): View
    {
        return view('brands.choose', [
            'brands' => $context->available(),
            'lastBrandId' => $context->lastUsed()?->id,
        ]);
    }
}
