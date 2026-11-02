<?php

use App\Support\CheckConstraints;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('device_type', 50);
            $table->string('device_info')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled', 'suspended'])->default('pending');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('customer_id', 'idx_subs_customer');
            $table->index(['status', 'expires_at'], 'idx_subs_status_expiry');
            $table->foreign('customer_id', 'fk_subs_customer')->references('id')->on('customers');
            $table->foreign('plan_id', 'fk_subs_plan')->references('id')->on('plans');
        });

        CheckConstraints::add('subscriptions', [
            'chk_subs_dates' => 'expires_at IS NULL OR starts_at IS NULL OR expires_at > starts_at',
            'chk_subs_active' => "status <> 'active' OR expires_at IS NOT NULL",
        ]);

        Schema::create('subscription_pack', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('pack_id');

            $table->primary(['subscription_id', 'pack_id']);
            $table->index('pack_id', 'idx_sp_pack');
            $table->foreign('subscription_id', 'fk_sp_sub')->references('id')->on('subscriptions')->cascadeOnDelete();
            $table->foreign('pack_id', 'fk_sp_pack')->references('id')->on('packs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_pack');
        Schema::dropIfExists('subscriptions');
    }
};
