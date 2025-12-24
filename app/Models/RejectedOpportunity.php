<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'potential_customer_name',
        'email',
        'phone',
        'text_location',
        'map_location',
        'specific_location',
        'created_by',
        'remarks',
        'reason',
        'rejected_from',
        'original_id',
        'city_id',
        'subcity_id'
    ];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function subcity()
    {
        return $this->belongsTo(Subcity::class);
    }

    public function scopeFromSource($query, $source)
    {
        return $query->where('rejected_from', $source);
    }

    public function scopeFromOpportunities($query)
    {
        return $query->where('rejected_from', 'opportunity');
    }

    public function scopeFromPotentialCustomers($query)
    {
        return $query->where('rejected_from', 'potential_customer');
    }

    public function scopeFromCustomers($query)
    {
        return $query->where('rejected_from', 'customer');
    }

    // Updated search scope to include all location fields
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('potential_customer_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('text_location', 'like', "%{$search}%")
              ->orWhere('map_location', 'like', "%{$search}%")
              ->orWhere('specific_location', 'like', "%{$search}%")
              ->orWhere('remarks', 'like', "%{$search}%")
              ->orWhere('reason', 'like', "%{$search}%")
              ->orWhereHas('city', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('subcity', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeBySubcity($query, $subcityId)
    {
        return $query->where('subcity_id', $subcityId);
    }

    // Get full location with all location fields
    public function getFullLocationAttribute()
    {
        $locationParts = [];
        
        if ($this->text_location) {
            $locationParts[] = $this->text_location;
        }
        
        if ($this->specific_location) {
            $locationParts[] = $this->specific_location;
        }
        
        if ($this->subcity) {
            $locationParts[] = $this->subcity->name;
        }
        
        if ($this->city) {
            $locationParts[] = $this->city->name;
        }
        
        return implode(', ', $locationParts) ?: null;
    }

    // Helper method to check if map location is a valid URL
    public function getHasValidMapLocationAttribute()
    {
        if (!$this->map_location) {
            return false;
        }
        
        return filter_var($this->map_location, FILTER_VALIDATE_URL) !== false;
    }

    // Get display location - prefers text location if available
    public function getDisplayLocationAttribute()
    {
        if ($this->text_location) {
            return $this->text_location;
        }
        
        if ($this->specific_location) {
            return $this->specific_location;
        }
        
        if ($this->city || $this->subcity) {
            return $this->full_location;
        }
        
        return 'Location not specified';
    }
}