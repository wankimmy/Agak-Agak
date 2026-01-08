<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_date',
        'product_id',
        'store_id',
        'location',
        'price',
        'quantity_sold',
        'revenue',
        'stock_available',
        'promo_flag',
        'discount_pct',
        'holiday_flag',
        'channel',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'price' => 'decimal:2',
        'revenue' => 'decimal:2',
        'quantity_sold' => 'integer',
        'stock_available' => 'boolean',
        'promo_flag' => 'boolean',
        'discount_pct' => 'decimal:2',
        'holiday_flag' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}

