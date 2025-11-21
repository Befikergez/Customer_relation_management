<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'potential_customer_id',
        'title', 
        'description',
        'price',
        'file',
        'created_by',
        'status',
        'is_rejected',
        'customer_approved_at',
        'customer_rejected_at',
        'customer_review'
    ];

    protected $casts = [
        'is_rejected' => 'boolean',
        'customer_approved_at' => 'datetime',
        'customer_rejected_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($proposal) {
            // When a proposal is created, update the potential customer status to 'proposal_sent'
            $potentialCustomer = $proposal->potentialCustomer;
            
            if ($potentialCustomer && $potentialCustomer->status === 'draft') {
                // Use withoutEvents to prevent any potential loops
                $potentialCustomer->withoutEvents(function () use ($potentialCustomer) {
                    $potentialCustomer->update(['status' => 'proposal_sent']);
                });
            }
        });

        static::updated(function ($proposal) {
            // If proposal status changes to signed, you might want to update potential customer
            // For example, if proposal is signed, you might want to auto-approve the customer
            if ($proposal->isDirty('status') && $proposal->status === 'signed') {
                $potentialCustomer = $proposal->potentialCustomer;
                if ($potentialCustomer && $potentialCustomer->status === 'proposal_sent') {
                    $potentialCustomer->withoutEvents(function () use ($potentialCustomer) {
                        $potentialCustomer->update(['status' => 'accepted']);
                    });
                }
            }
        });

        static::deleted(function ($proposal) {
            // When a proposal is deleted, check if we should revert the potential customer status
            $potentialCustomer = $proposal->potentialCustomer;
            
            if ($potentialCustomer && $potentialCustomer->proposals()->count() === 0) {
                // Only revert to draft if there are no other proposals
                $potentialCustomer->withoutEvents(function () use ($potentialCustomer) {
                    $potentialCustomer->update(['status' => 'draft']);
                });
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function potentialCustomer()
    {
        return $this->belongsTo(PotentialCustomer::class, 'potential_customer_id');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopeUnsigned($query)
    {
        return $query->where('status', 'unsigned');
    }

    public function scopeRejected($query)
    {
        return $query->where('is_rejected', true);
    }

    public function scopeNotRejected($query)
    {
        return $query->where('is_rejected', false);
    }

    // Helper methods for status checking
    public function isUnsigned()
    {
        return $this->status === 'unsigned';
    }

    public function isSigned()
    {
        return $this->status === 'signed';
    }

    public function isRejected()
    {
        return $this->is_rejected === true;
    }

    public function isApprovedByCustomer()
    {
        return !is_null($this->customer_approved_at);
    }

    public function isRejectedByCustomer()
    {
        return !is_null($this->customer_rejected_at);
    }

    // Status badge class for UI
    public function getStatusBadgeClass()
    {
        if ($this->is_rejected) {
            return 'bg-red-100 text-red-800 border-red-200';
        }

        if ($this->isSigned()) {
            return 'bg-green-100 text-green-800 border-green-200';
        }

        if ($this->isApprovedByCustomer()) {
            return 'bg-blue-100 text-blue-800 border-blue-200';
        }

        if ($this->isRejectedByCustomer()) {
            return 'bg-orange-100 text-orange-800 border-orange-200';
        }

        return 'bg-gray-100 text-gray-800 border-gray-200';
    }

    // Format status for display
    public function getFormattedStatus()
    {
        if ($this->is_rejected) {
            return 'Rejected';
        }

        if ($this->isSigned()) {
            return 'Signed';
        }

        if ($this->isApprovedByCustomer()) {
            return 'Customer Approved';
        }

        if ($this->isRejectedByCustomer()) {
            return 'Customer Rejected';
        }

        return 'Unsigned';
    }
}