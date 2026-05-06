<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistEntry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'checklist_entries';
    protected $fillable = [
        'loading_order_id',
        'package_id',
        'scanned_by',
        'scanned_at',
        'scanned_code',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function loadingOrder(): BelongsTo
    {
        return $this->belongsTo(LoadingOrder::class);
    }
}
