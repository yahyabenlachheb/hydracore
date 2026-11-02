<?php

use App\Support\CheckConstraints;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 100)->unique('uq_orders_reference');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->enum('type', ['new', 'renewal'])->default('new');
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('plan_name', 100);
            $table->unsignedTinyInteger('duration_months');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->char('currency', 3);
            $table->string('coupon_code', 50)->nullable();
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at'], 'idx_orders_customer');
            $table->index(['status', 'created_at'], 'idx_orders_status');
            $table->index('subscription_id', 'idx_orders_subscription');
            $table->foreign('customer_id', 'fk_orders_customer')->references('id')->on('customers');
            $table->foreign('plan_id', 'fk_orders_plan')->references('id')->on('plans');
            $table->foreign('subscription_id', 'fk_orders_sub')->references('id')->on('subscriptions')->nullOnDelete();
            $table->foreign('brand_id', 'fk_orders_brand')->references('id')->on('brands')->nullOnDelete();
        });

        CheckConstraints::add('orders', [
            'chk_orders_total' => 'total = subtotal - discount AND total >= 0',
            'chk_orders_paid' => "status <> 'paid' OR paid_at IS NOT NULL",
        ]);

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('provider', 30);
            $table->string('provider_payment_id', 150)->nullable();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending');
            $table->string('failure_reason')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_payment_id'], 'uq_payments_provider_ref');
            $table->index('order_id', 'idx_payments_order');
            $table->foreign('order_id', 'fk_payments_order')->references('id')->on('orders');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('orders');
    }
};
