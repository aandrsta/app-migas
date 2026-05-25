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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->double('oil_price');
            $table->double('capital_cost');
            $table->double('non_capital_cost');
            $table->double('opex_per_year');
            $table->double('tax_rate');
            $table->integer('known_years');
            $table->integer('prediction_years');
            $table->integer('depreciation_years');
            $table->string('depreciation_method');
            $table->double('discount_rate');
            $table->double('total_reserve')->nullable();
            $table->double('decline_rate')->nullable();
            $table->double('custom_depreciation_rate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
