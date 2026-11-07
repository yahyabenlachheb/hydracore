<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // SHA-256 of the brand's API key. The key itself is only shown once, by brands:api-key.
            $table->char('api_token', 64)->nullable()->unique('uq_brands_api_token')->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('uq_brands_api_token');
            $table->dropColumn('api_token');
        });
    }
};
