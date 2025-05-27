<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait PostgisTrait
{
    public function scopeWithDistance($query, $lat, $lng, $distance = 5000)
    {
        return $query->selectRaw("
            *,
            ST_Distance(
                ST_Transform(location::geometry, 3857),
                ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326)::geometry, 3857)
            ) as distance", [$lng, $lat])
        ->whereRaw("
            ST_DWithin(
                ST_Transform(location::geometry, 3857),
                ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326)::geometry, 3857),
                ?
            )", [$lng, $lat, $distance]);
    }

    protected function castPoint($value)
    {
        if (is_array($value)) {
            return DB::raw("ST_SetSRID(ST_MakePoint({$value['longitude']}, {$value['latitude']}), 4326)");
        }
        return $value;
    }
}
