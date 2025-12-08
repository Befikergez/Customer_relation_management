<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'potential_customer_name',
        'email',
        'phone',
        'location',
        'map_location',
        'text_location', // Added specific_location
        'remarks',
        'created_by',
        'potential_customer_id',
        'city_id',
        'subcity_id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function potentialCustomer()
    {
        return $this->belongsTo(PotentialCustomer::class);
    }

    public function rejectedOpportunities()
    {
        return $this->hasMany(RejectedOpportunity::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function subcity()
    {
        return $this->belongsTo(Subcity::class);
    }

    // Get full location with specific location
    public function getFullLocationAttribute()
    {
        $locationParts = [];
        
        if ($this->specific_location) {
            $locationParts[] = $this->specific_location;
        }
        
        if ($this->subcity) {
            $locationParts[] = $this->subcity->name;
        }
        
        if ($this->city) {
            $locationParts[] = $this->city->name;
        }
        
        return implode(', ', $locationParts) ?: $this->location;
    }
}