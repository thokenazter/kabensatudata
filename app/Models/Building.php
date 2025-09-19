<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_number',
        'village_id',
        'latitude',
        'longitude',
        // 'address',
        // 'notes',
        // 'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the village that owns the building.
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Get the families for the building.
     */
    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    /**
     * Accessor untuk mendapatkan koordinat sebagai array.
     *
     * @return array
     */
    public function getCoordinatesAttribute()
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude
        ];
    }

    /**
     * Validator untuk koordinat.
     *
     * @param float $latitude
     * @param float $longitude
     * @return boolean
     */
    public static function validateCoordinates($latitude, $longitude)
    {
        // Pastikan nilai adalah angka atau string numerik
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        // Konversi ke float untuk validasi
        $lat = (float) $latitude;
        $lng = (float) $longitude;

        // Validasi range
        return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
    }

    /**
     * Scope untuk menemukan bangunan dalam radius tertentu.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $latitude
     * @param float $longitude
     * @param float $radius Radius dalam kilometer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNearby($query, $latitude, $longitude, $radius = 1)
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;

        // Validasi koordinat
        if (!self::validateCoordinates($lat, $lng)) {
            return $query->whereRaw('1 = 0'); // Return empty result set if coordinates are invalid
        }

        // Haversine formula
        $haversine = "(
            6371 * acos(
                cos(radians($lat)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians($lng)) 
                + sin(radians($lat)) 
                * sin(radians(latitude))
            )
        )";

        return $query
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} < ?", [$radius])
            ->orderBy('distance');
    }

    /**
     * Mutator untuk latitude.
     *
     * @param mixed $value
     * @return void
     */
    public function setLatitudeAttribute($value)
    {
        $this->attributes['latitude'] = is_numeric($value) ? (float) $value : null;
    }

    /**
     * Mutator untuk longitude.
     *
     * @param mixed $value
     * @return void
     */
    public function setLongitudeAttribute($value)
    {
        $this->attributes['longitude'] = is_numeric($value) ? (float) $value : null;
    }
}
