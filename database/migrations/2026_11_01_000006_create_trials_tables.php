<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('device_type', 50);
            $table->string('device_info')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'converted', 'rejected'])->default('pending');
            $table->dateTime('expires_at')->nullable();
            $table->unsignedBigInteger('converted_subscription_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('customer_id', 'idx_trials_customer');
            $table->index('ip_address', 'idx_trials_ip');
            $table->foreign('customer_id', 'fk_trials_customer')->references('id')->on('customers');
            $table->foreign('brand_id', 'fk_trials_brand')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('converted_subscription_id', 'fk_trials_sub')->references('id')->on('subscriptions')->nullOnDelete();
        });

        // SQLite (the test database) has no prefix indexes.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('trials', fn (Blueprint $table) => $table->index('device_info', 'idx_trials_device'));
        } else {
            DB::statement('CREATE INDEX idx_trials_device ON trials (device_info(100))');
        }

        Schema::create('pack_trial', function (Blueprint $table) {
            $table->unsignedBigInteger('trial_id');
            $table->unsignedBigInteger('pack_id');

            $table->primary(['trial_id', 'pack_id']);
            $table->index('pack_id', 'idx_pt_pack');
            $table->foreign('trial_id', 'fk_pt_trial')->references('id')->on('trials')->cascadeOnDelete();
            $table->foreign('pack_id', 'fk_pt_pack')->references('id')->on('packs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_trial');
        Schema::dropIfExists('trials');
    }
};
