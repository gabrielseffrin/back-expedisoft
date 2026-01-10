<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'destinations';
    protected $fillable = [
        'name',
        'adress',
    ];
}
