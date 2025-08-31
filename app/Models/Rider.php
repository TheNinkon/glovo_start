<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'supervisor_id',
        'user_id',
    ];

    // RELACIONES
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Rider::class, 'supervisor_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    // SCOPES
    /**
     * Filtra por un término de búsqueda en nombre, email o teléfono.
     *
     * @param Builder $query
     * @param string|null $search
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Filtra por un estado específico.
     *
     * @param Builder $query
     * @param string|null $status
     * @return Builder
     */
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }
}
