<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'location',
        'specific_location',
        'remarks',
        'status',
        'created_by',
        'potential_customer_id',
        'city_id',
        'subcity_id',
        // Payment columns
        'payment_status',
        'total_payment_amount',
        'paid_amount',
        'remaining_amount',
        // Commission columns
        'commission_user_id',
        'commission_rate',
        'commission_amount',
        'commission_status',
        'paid_commission',
        // Approval columns
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejection_reason',
        'rejected_by',
    ];

    protected $casts = [
        'total_payment_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'paid_commission' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected $appends = [
        'payment_progress',
        'commission_progress',
        'payment_status_color',
        'commission_status_color',
    ];

    // Relationships
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function subcity(): BelongsTo
    {
        return $this->belongsTo(Subcity::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commissionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commission_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Accessors
    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_payment_amount <= 0) {
            return 0;
        }
        return min(100, ($this->paid_amount / $this->total_payment_amount) * 100);
    }

    public function getCommissionProgressAttribute(): float
    {
        if ($this->commission_amount <= 0) {
            return 0;
        }
        return min(100, ($this->paid_commission / $this->commission_amount) * 100);
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'bg-green-500',
            'half_paid' => 'bg-yellow-500',
            'pending' => 'bg-blue-500',
            default => 'bg-red-500',
        };
    }

    public function getCommissionStatusColorAttribute(): string
    {
        return match($this->commission_status) {
            'paid' => 'bg-green-500',
            'pending' => 'bg-yellow-500',
            default => 'bg-red-500',
        };
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeContractCreated($query)
    {
        return $query->where('status', 'contract_created');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeWithCommission($query)
    {
        return $query->whereNotNull('commission_user_id');
    }

    // Methods
    public function updatePaymentStatus(): void
    {
        if ($this->total_payment_amount <= 0) {
            $this->payment_status = 'not_paid';
        } elseif ($this->paid_amount >= $this->total_payment_amount) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount >= ($this->total_payment_amount * 0.5)) {
            $this->payment_status = 'half_paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'pending';
        } else {
            $this->payment_status = 'not_paid';
        }
        
        $this->remaining_amount = max(0, $this->total_payment_amount - $this->paid_amount);
        $this->save();
    }

    public function updateCommissionStatus(): void
    {
        if ($this->commission_amount <= 0) {
            $this->commission_status = 'not_paid';
        } elseif ($this->paid_commission >= $this->commission_amount) {
            $this->commission_status = 'paid';
        } elseif ($this->paid_commission > 0) {
            $this->commission_status = 'pending';
        } else {
            $this->commission_status = 'not_paid';
        }
        
        $this->save();
    }

    public function calculateCommission(): void
    {
        if ($this->commission_rate && $this->commission_user_id) {
            $this->commission_amount = ($this->total_payment_amount * $this->commission_rate) / 100;
            $this->updateCommissionStatus();
            $this->save();
        }
    }
}