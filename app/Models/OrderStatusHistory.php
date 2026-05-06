<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrderStatusHistory extends Model
{
    use HasUuids;

    protected $table = 'order_status_history';

    public $timestamps = false;

    protected $fillable = [
        'loading_order_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at',
        'note'
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->changed_at)) {
                $model->changed_at = Carbon::now();
            }
        });
    }
}
