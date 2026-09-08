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
        Schema::create('sku_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 10)->unique();
            $table->string('label')->nullable();
            $table->string('strategy');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('special_quantity')->nullable();
            $table->unsignedInteger('special_price_cents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_pricings');
    }
};
