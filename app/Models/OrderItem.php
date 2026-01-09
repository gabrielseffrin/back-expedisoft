<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_items';
    protected $fillable = [
        'loading_order_id',
        'product_id',
        'quantity',
        'note',
    ];

    public function loadingOrder(): BelongsTo
    {
        return $this->belongsTo(LoadingOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
