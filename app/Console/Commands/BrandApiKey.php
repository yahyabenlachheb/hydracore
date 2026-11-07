<?php

namespace App\Console\Commands;

use App\Models\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BrandApiKey extends Command
{
    protected $signature = 'brands:api-key {brand : Brand id or name} {--revoke : Remove the key instead}';

    protected $description = 'Creates a new API key for a brand website (replacing the old one), or revokes it';

    public function handle(): int
    {
        $value = (string) $this->argument('brand');
        $brand = Brand::where(ctype_digit($value) ? 'id' : 'name', $value)->first();

        if (! $brand) {
            $this->error("No brand \"{$value}\".");

            return self::FAILURE;
        }

        if ($this->option('revoke')) {
            $brand->update(['api_token' => null]);
            $this->info("Revoked the API key of {$brand->name}.");

            return self::SUCCESS;
        }

        $key = 'hc_' . Str::random(48);
        $brand->update(['api_token' => hash('sha256', $key)]);

        $this->info("New API key for {$brand->name}. It is shown only once, and any old key stops working:");
        $this->line($key);

        return self::SUCCESS;
    }
}
