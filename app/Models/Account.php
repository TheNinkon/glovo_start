<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'status',
        'date_of_delivery',
        'date_of_return',
    ];

    protected $casts = [
        'date_of_delivery' => 'date',
        'date_of_return' => 'date',
    ];

    // RELACIONES
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->whereNull('end_date');
    }

    // SCOPES
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            return $query->where('courier_id', 'LIKE', "%{$term}%");
        }
        return $query;
    }
}
