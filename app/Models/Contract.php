<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'proposal_id',
        'contract_title',
        'contract_description',
        'total_value',
        'start_date',
        'end_date',
        'payment_terms',
        'status',
        'file',
        'customer_rejected_at',
        'customer_signed_at',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_value' => 'decimal:2',
        'customer_rejected_at' => 'datetime',
        'customer_signed_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the contract.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the proposal that this contract was created from.
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Get the user who created the contract.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include signed contracts.
     */
    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    /**
     * Scope a query to only include unsigned contracts.
     */
    public function scopeUnsigned($query)
    {
        return $query->where('status', 'unsigned');
    }

    /**
     * Scope a query to only include draft contracts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if contract is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Check if contract is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'signed' && 
               $this->start_date && 
               $this->start_date->isPast() && 
               !$this->is_expired;
    }
}