<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'potential_customer_name', // renamed from customer_name
        'email',
        'phone',
        'location', // changed from address to location (map link)
        'remarks',
        'created_by', // added
        'potential_customer_id', // nullable, set when approved
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
}