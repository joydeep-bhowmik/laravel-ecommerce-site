<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // If linked to a user
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('size_name');                 // e.g., "free size"
            $table->decimal('price', 10, 2);             // Price of the selected size
            $table->integer('quantity')->default(1);     // User's selected quantity
            $table->decimal('length', 8, 2)->nullable(); // Length of the product
            $table->decimal('width', 8, 2)->nullable();  // Width of the product
            $table->decimal('height', 8, 2)->nullable(); // Height of the product
            $table->decimal('weight', 8, 2)->nullable(); // Weight of the product
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carts');
    }
};
