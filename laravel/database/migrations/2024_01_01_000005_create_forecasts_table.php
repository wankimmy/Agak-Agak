<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->decimal('forecast_value', 10, 2);
            $table->decimal('lower_bound', 10, 2)->nullable();
            $table->decimal('upper_bound', 10, 2)->nullable();
            $table->string('forecast_type')->default('daily'); // daily, weekly, monthly
            $table->timestamps();

            $table->index('forecast_date');
            $table->index(['product_id', 'forecast_date']);
            $table->index(['store_id', 'forecast_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};

