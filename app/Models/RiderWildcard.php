<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderWildcard extends Model
{
    use HasFactory;
    protected $fillable = ['rider_id', 'forecast_id', 'tokens'];

    public function rider(): BelongsTo { return $this->belongsTo(Rider::class); }
    public function forecast(): BelongsTo { return $this->belongsTo(Forecast::class); }
}
