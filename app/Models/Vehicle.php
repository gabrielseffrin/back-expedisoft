<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vehicles';
    protected $fillable = [
        'plate',
        'model',
        'carrier_id',
    ];

    public function carrier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
