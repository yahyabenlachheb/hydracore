<?php

namespace App\Http\Controllers;

use App\Support\BrandContext;
use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    protected function brandId(): ?int
    {
        return app(BrandContext::class)->current()?->id;
    }

    /**
     * A record of another brand doesn't exist for this brand.
     */
    protected function ensureVisible(Model $record): void
    {
        abort_unless($record::query()->visibleTo($this->brandId())->whereKey($record->getKey())->exists(), 404);
    }
}
