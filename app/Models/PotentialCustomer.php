<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotentialCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'potential_customer_name',
        'email',
        'phone',
        'location',
        'remarks',
        'status',
        'created_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $table = 'potential_customers';

    protected $attributes = [
        'status' => 'draft',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // No need for the updated event here since Proposal model handles it
        // This prevents infinite loops
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

    // Check if this potential customer has any proposals
    public function getHasProposalsAttribute()
    {
        return $this->proposals()->exists();
    }

    // Get the latest proposal
    public function latestProposal()
    {
        return $this->hasOne(Proposal::class)->latest();
    }

    // Scope for different statuses
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

    // Helper methods
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

    // Status badge class for UI
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

    // Format status for display
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
}