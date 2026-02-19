<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'api_created_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function purchases_requests(): HasMany
    {
        return $this->hasMany(PurchaseRequests::class);
    }
}
