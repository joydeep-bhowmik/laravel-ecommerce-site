<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Zone name
            $table->string('country');                       // Country
            $table->string('state')->nullable();             // State (optional)
            $table->string('city')->nullable();              // City (optional)
            $table->string('postal_code_range')->nullable(); // Example: "560000-569999"
            $table->decimal('price_per_kg', 10, 2);          // Shipping cost for this zone
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
