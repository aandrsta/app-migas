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
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->double('production');
            $table->double('income');
            $table->double('capital');
            $table->double('non_capital');
            $table->double('opex');
            $table->double('depreciation');
            $table->double('taxable_income');
            $table->double('tax');
            $table->double('ncf');
            $table->double('cumulative_ncf');
            $table->double('discount_factor');
            $table->double('pv_ncf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
