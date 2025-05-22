<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'organization_id',
        'type',
        'description',
        'latitude',
        'longitude',
        'schedule',
        'capacity',
        'is_active'
    ];

    protected $casts = [
        'schedule' => 'array',
        'is_active' => 'boolean',
    ];

    public function setLocationAttribute($value)
    {
        if (isset($value['latitude']) && isset($value['longitude'])) {
            $this->attributes['latitude'] = $value['latitude'];
            $this->attributes['longitude'] = $value['longitude'];
            DB::statement('UPDATE services SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [
                $value['longitude'],
                $value['latitude'],
                $this->id
            ]);
        }
    }

    public function getLocationAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

    public function scopeNearby($query, $lat, $lng, $radius = 5000)
    {
        return $query->selectRaw("*, ST_DistanceSphere(location, ST_MakePoint(?, ?)::geography) as distance", [$lng, $lat])
            ->whereRaw("ST_DWithin(location::geography, ST_MakePoint(?, ?)::geography, ?)", [$lng, $lat, $radius]);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }
}
