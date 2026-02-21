<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequests extends Model
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to only include non-deleted records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', 0);
    }

    /**
     * Scope to filter records within a date range,
     * checking both created_at and api_created_at.
     */
    public function scopeInDateRange(Builder $query, CarbonInterface $from, CarbonInterface $to): Builder
    {
        return $query->where(function (Builder $q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to])
                ->orWhereBetween('api_created_at', [$from, $to]);
        });
    }

    /**
     * Scope to filter by currency codes.
     *
     * @param  array<string>  $codes
     */
    public function scopeWithCurrency(Builder $query, array $codes = ['USD', 'LYD']): Builder
    {
        return $query->whereIn('currency_code', $codes);
    }
}
