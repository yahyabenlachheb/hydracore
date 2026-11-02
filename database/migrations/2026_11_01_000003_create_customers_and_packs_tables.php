<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('full_name', 150);
            $table->string('email', 190)->unique('uq_customers_email');
            $table->string('phone', 30)->nullable()->index('idx_customers_phone');
            $table->char('country', 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('brand_id', 'fk_customers_brand')->references('id')->on('brands')->nullOnDelete();
        });

        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['channels', 'vods']);
            $table->string('code', 100);
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'code'], 'uq_packs_type_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packs');
        Schema::dropIfExists('customers');
    }
};
