<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intervention extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'beneficiary_id',
        'service_id',
        'user_id',
        'intervention_date',
        'type',
        'description',
        'outcomes',
        'status'
    ];

    protected $casts = [
        'intervention_date' => 'datetime',
        'outcomes' => 'array'
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
