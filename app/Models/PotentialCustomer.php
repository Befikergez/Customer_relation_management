<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PotentialCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'potential_customer_name',
        'email',
        'phone',
        'location',
        'specific_location',
        'address',
        'map_location',
        'text_location',
        'remarks',
        'status',
        'created_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'city_id',
        'subcity_id',
        // Payment fields
        'payment_amount',
        'payment_method',
        'payment_schedule',
        'payment_date',
        'payment_remarks',
        'payment_reference',
    ];

    protected $table = 'potential_customers';

    protected $attributes = [
        'status' => 'draft',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        // No need for the updated event here since Proposal model handles it
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function subcity()
    {
        return $this->belongsTo(Subcity::class, 'subcity_id');
    }

    public function getHasProposalsAttribute()
    {
        return $this->proposals()->exists();
    }

    public function getHasPaymentsAttribute()
    {
        return $this->payments()->exists();
    }

    public function latestProposal()
    {
        return $this->hasOne(Proposal::class)->latest();
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeProposalSent($query)
    {
        return $query->where('status', 'proposal_sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeBySubcity($query, $subcityId)
    {
        return $query->where('subcity_id', $subcityId);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function hasProposalSent()
    {
        return $this->status === 'proposal_sent';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function getCityNameAttribute()
    {
        return $this->city ? $this->city->name : null;
    }

    public function getSubcityNameAttribute()
    {
        return $this->subcity ? $this->subcity->name : null;
    }

    // Updated to include specific_location
    public function getFullLocationAttribute()
    {
        $location = [];
        if ($this->specific_location) {
            $location[] = $this->specific_location;
        }
        if ($this->address) {
            $location[] = $this->address;
        }
        if ($this->subcity_name) {
            $location[] = $this->subcity_name;
        }
        if ($this->city_name) {
            $location[] = $this->city_name;
        }
        return implode(', ', $location) ?: $this->location;
    }

    public function getStatusBadgeClass()
    {
        $classes = [
            'draft' => 'bg-blue-100 text-blue-800 border-blue-200',
            'proposal_sent' => 'bg-rose-100 text-rose-800 border-rose-200',
            'accepted' => 'bg-green-100 text-green-800 border-green-200',
            'rejected' => 'bg-red-100 text-red-800 border-red-200',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }

    public function getFormattedStatus()
    {
        $statusMap = [
            'draft' => 'Draft',
            'proposal_sent' => 'Proposal Sent',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    // Payment related methods
    public function hasPayment()
    {
        return !is_null($this->payment_amount) && $this->payment_amount > 0;
    }

    public function getFormattedPaymentAmountAttribute()
    {
        return $this->payment_amount ? '$' . number_format($this->payment_amount, 2) : 'N/A';
    }

    public function getPaymentDateFormattedAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('F d, Y') : 'N/A';
    }
    
    // FIXED METHOD: Convert to customer - removed problematic columns
    public function convertToCustomer($customerData = [])
    {
        // Get the actual columns that exist in the customers table
        $customerTableColumns = DB::getSchemaBuilder()->getColumnListing('customers');
        
        // Basic data that should exist in most customer tables
        $defaultData = [
            'name' => $this->potential_customer_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'address' => $this->address,
            'remarks' => ($this->remarks ?? '') . ' (Converted from potential customer on ' . now()->format('Y-m-d H:i') . ')',
            'created_by' => auth()->id(),
            'potential_customer_id' => $this->id,
            'city_id' => $this->city_id,
            'subcity_id' => $this->subcity_id,
            'status' => 'draft',
        ];
        
        // Add optional fields only if they exist in the table
        if (in_array('map_location', $customerTableColumns) && $this->map_location) {
            $defaultData['map_location'] = $this->map_location;
        }
        
        if (in_array('text_location', $customerTableColumns) && $this->text_location) {
            $defaultData['text_location'] = $this->text_location;
        }
        
        if (in_array('specific_location', $customerTableColumns) && $this->specific_location) {
            $defaultData['specific_location'] = $this->specific_location;
        }
        
        // Payment status columns - only add if they exist
        if (in_array('payment_status', $customerTableColumns)) {
            $defaultData['payment_status'] = 'not_paid';
        }
        
        if (in_array('commission_status', $customerTableColumns)) {
            $defaultData['commission_status'] = 'not_applicable';
        }
        
        // Copy payment data if exists and columns exist
        if ($this->payment_amount) {
            if (in_array('payment_amount', $customerTableColumns)) {
                $defaultData['payment_amount'] = $this->payment_amount;
            }
            
            if (in_array('payment_method', $customerTableColumns)) {
                $defaultData['payment_method'] = $this->payment_method;
            }
            
            if (in_array('payment_schedule', $customerTableColumns)) {
                $defaultData['payment_schedule'] = $this->payment_schedule;
            }
            
            if (in_array('payment_date', $customerTableColumns)) {
                $defaultData['payment_date'] = $this->payment_date;
            }
            
            if (in_array('payment_remarks', $customerTableColumns)) {
                $defaultData['payment_remarks'] = $this->payment_remarks;
            }
            
            if (in_array('payment_reference', $customerTableColumns)) {
                $defaultData['payment_reference'] = $this->payment_reference;
            }
            
            if (in_array('total_payment_amount', $customerTableColumns)) {
                $defaultData['total_payment_amount'] = $this->payment_amount;
            }
        }
        
        // Filter out any columns that don't exist in the customers table
        $filteredData = [];
        foreach ($defaultData as $key => $value) {
            if (in_array($key, $customerTableColumns)) {
                $filteredData[$key] = $value;
            }
        }
        
        // Merge with any provided data (also filtered)
        foreach ($customerData as $key => $value) {
            if (in_array($key, $customerTableColumns)) {
                $filteredData[$key] = $value;
            }
        }
        
        // Check if customer already exists
        $existingCustomer = Customer::where('email', $this->email)->first();
        
        if ($existingCustomer) {
            $existingCustomer->update($filteredData);
            return $existingCustomer;
        } else {
            return Customer::create($filteredData);
        }
    }
}