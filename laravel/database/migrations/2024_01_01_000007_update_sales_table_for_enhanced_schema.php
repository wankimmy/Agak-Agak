<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Rename quantity to quantity_sold for clarity
            $table->renameColumn('quantity', 'quantity_sold');
            
            // Rename total_amount to revenue
            $table->renameColumn('total_amount', 'revenue');
            
            // Add new required fields
            $table->boolean('stock_available')->default(1)->after('revenue'); // 1 = available, 0 = out of stock (at START of day)
            
            // Add optional forecast-enhancing columns
            $table->boolean('promo_flag')->default(0)->after('stock_available'); // 0/1
            $table->decimal('discount_pct', 5, 2)->nullable()->after('promo_flag'); // percentage
            $table->boolean('holiday_flag')->default(0)->after('discount_pct'); // 0/1
            $table->string('channel')->nullable()->after('holiday_flag'); // online, retail, marketplace
            
            // Add indexes for filtering
            $table->index('stock_available');
            $table->index('promo_flag');
            $table->index('holiday_flag');
            $table->index('channel');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['stock_available', 'promo_flag', 'holiday_flag', 'channel']);
            $table->dropColumn(['stock_available', 'promo_flag', 'discount_pct', 'holiday_flag', 'channel']);
            $table->renameColumn('quantity_sold', 'quantity');
            $table->renameColumn('revenue', 'total_amount');
        });
    }
};

