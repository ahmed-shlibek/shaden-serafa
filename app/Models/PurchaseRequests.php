<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequests extends Model
{
    protected $casts = [

    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
