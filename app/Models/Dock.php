<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dock extends Model
{
    use HasFactory, HasUuids;

    public $table = 'docks';
    protected $fillable = [
        'id',
        'external_id',
        'source_system',
        'dock_code',
        'description',
        'location',
    ];
}
