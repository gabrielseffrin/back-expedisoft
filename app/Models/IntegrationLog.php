<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'integration_logs';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'endpoint',
        'payload',
        'http_status',
        'error_message',
        'received_at'
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
