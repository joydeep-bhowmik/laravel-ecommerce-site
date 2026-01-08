<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('address');
            $table->json('items');
            $table->enum('status', ['onprocess', 'delayed', 'shipped', 'cancelled', 'delivered'])->default('onprocess');
            $table->enum('payment_method', ['cod', 'online'])->default('online');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->string('tracking_id')->nullable();
            $table->string('tracking_link')->nullable();
            $table->text('notes')->nullable();
            $table->string('r_order_id')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
