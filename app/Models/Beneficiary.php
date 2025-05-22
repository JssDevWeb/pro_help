<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Beneficiary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'identifier',
        'name',
        'birth_date',
        'gender',
        'latitude',
        'longitude',
        'needs',
        'notes'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'needs' => 'array'
    ];

    public function setLastKnownLocationAttribute($value)
    {
        if (isset($value['latitude']) && isset($value['longitude'])) {
            $this->attributes['latitude'] = $value['latitude'];
            $this->attributes['longitude'] = $value['longitude'];
            DB::statement('UPDATE beneficiaries SET last_known_location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [
                $value['longitude'],
                $value['latitude'],
                $this->id
            ]);
        }
    }

    public function getLastKnownLocationAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

    public function scopeNearby($query, $lat, $lng, $radius = 5000)
    {
        return $query->selectRaw("*, ST_DistanceSphere(last_known_location, ST_MakePoint(?, ?)::geography) as distance", [$lng, $lat])
            ->whereRaw("ST_DWithin(last_known_location::geography, ST_MakePoint(?, ?)::geography, ?)", [$lng, $lat, $radius]);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }
}
