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
        'location',
        'created_by',
        'remarks',
        'reason',
        'rejected_from',
        'original_id' // Add this back
    ];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope to filter by rejection source
    public function scopeFromSource($query, $source)
    {
        return $query->where('rejected_from', $source);
    }

    // Scope for opportunities
    public function scopeFromOpportunities($query)
    {
        return $query->where('rejected_from', 'opportunity');
    }

    // Scope for potential customers
    public function scopeFromPotentialCustomers($query)
    {
        return $query->where('rejected_from', 'potential_customer');
    }

    // Scope for customers
    public function scopeFromCustomers($query)
    {
        return $query->where('rejected_from', 'customer');
    }
}