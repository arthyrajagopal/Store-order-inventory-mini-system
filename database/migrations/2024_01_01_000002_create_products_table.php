<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->unsignedInteger('stock_on_hand')->default(0);
            $table->timestamps();

            // Speeds up the low-stock query.
            $table->index('stock_on_hand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
