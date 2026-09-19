<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->unsignedInteger('quantity');

            // Snapshots: prices/tax rates can change after the sale, but an
            // order must always reflect what was actually charged.
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_percentage', 5, 2);

            $table->decimal('line_subtotal', 10, 2);
            $table->decimal('line_tax', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
