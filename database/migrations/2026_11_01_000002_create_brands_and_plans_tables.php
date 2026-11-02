<?php

use App\Support\CheckConstraints;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique('uq_brands_name');
            $table->string('website', 190)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_brands', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['user_id', 'brand_id']);
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id');
            $table->string('name', 100);
            $table->unsignedTinyInteger('duration_months');
            $table->decimal('price', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->unsignedTinyInteger('number_of_connections')->default(1);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['brand_id', 'is_active', 'sort_order'], 'idx_plans_brand_active');
            $table->foreign('brand_id', 'fk_plans_brand')->references('id')->on('brands');
        });

        CheckConstraints::add('plans', [
            'chk_plans_duration' => 'duration_months > 0',
            'chk_plans_price' => 'price >= 0',
            'chk_plans_connections' => 'number_of_connections > 0',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('user_brands');
        Schema::dropIfExists('brands');
    }
};
