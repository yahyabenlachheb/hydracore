<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('full_name', 150);
            $table->string('email', 190);
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'closed'])->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_contacts_status');
            $table->index('email', 'idx_contacts_email');
            $table->foreign('customer_id', 'fk_contacts_customer')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('brand_id', 'fk_contacts_brand')->references('id')->on('brands')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
