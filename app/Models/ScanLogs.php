<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanLogs extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'scan_logs';

    protected $fillable = [
        'loading_order_id',
        'operator_id',
        'package_id',
        'scanned_code',
        'payload',
        'status',
        'error_message',
        'scanned_at',
    ];
}
