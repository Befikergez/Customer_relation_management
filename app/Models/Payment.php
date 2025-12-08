<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'potential_customer_id',
        'customer_id',
        'amount',
        'method',
        'schedule',
        'payment_date',
        'reference_number',
        'remarks',
        'created_by',
        'is_active',
        // New commission columns
        'commission_earned',
        'commission_paid',
        'commission_payment_date',
        'commission_paid_status',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'commission_payment_date' => 'date',
        'amount' => 'decimal:2',
        'commission_earned' => 'decimal:2',
        'commission_paid' => 'decimal:2',
        'is_active' => 'boolean',
        'commission_paid_status' => 'boolean',
    ];

    public function potentialCustomer()
    {
        return $this->belongsTo(PotentialCustomer::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPotentialCustomer($query, $potentialCustomerId)
    {
        return $query->where('potential_customer_id', $potentialCustomerId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('payment_date', '>=', now()->subDays($days));
    }

    public function scopeUpcoming($query, $days = 7)
    {
        $today = now()->startOfDay();
        $futureDate = now()->addDays($days)->endOfDay();
        
        return $query->whereBetween('payment_date', [$today, $futureDate])
                    ->orderBy('payment_date', 'asc');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_date', '<', now()->startOfDay())
                    ->orderBy('payment_date', 'asc');
    }

    public function scopeCommissionPaid($query)
    {
        return $query->where('commission_paid_status', true);
    }

    public function scopeCommissionPending($query)
    {
        return $query->where('commission_paid_status', false)
                    ->where('commission_earned', '>', 0);
    }

    // Attribute accessors
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('F d, Y') : 'N/A';
    }

    public function getShortDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('M d, Y') : 'N/A';
    }

    public function getCommissionFormattedAmountAttribute()
    {
        return '$' . number_format($this->commission_earned, 2);
    }

    public function getCommissionFormattedPaidAttribute()
    {
        return '$' . number_format($this->commission_paid, 2);
    }

    public function getStatusAttribute()
    {
        $today = now()->startOfDay();
        $paymentDate = $this->payment_date;
        
        if (!$paymentDate) {
            return 'unknown';
        }
        
        if ($paymentDate->lt($today)) {
            return 'overdue';
        } elseif ($paymentDate->eq($today)) {
            return 'due_today';
        } elseif ($paymentDate->diffInDays($today) <= 7) {
            return 'upcoming';
        } else {
            return 'future';
        }
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'overdue' => 'bg-red-100 text-red-800 border-red-200',
            'due_today' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'upcoming' => 'bg-blue-100 text-blue-800 border-blue-200',
            'future' => 'bg-green-100 text-green-800 border-green-200',
            'unknown' => 'bg-gray-100 text-gray-800 border-gray-200',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }

    public function getFormattedStatusAttribute()
    {
        $statusMap = [
            'overdue' => 'Overdue',
            'due_today' => 'Due Today',
            'upcoming' => 'Upcoming',
            'future' => 'Future',
            'unknown' => 'Unknown',
        ];

        return $statusMap[$this->status] ?? 'Unknown';
    }

    public function getCommissionStatusAttribute()
    {
        if ($this->commission_earned == 0) {
            return 'no_commission';
        } elseif ($this->commission_paid_status) {
            return 'paid';
        } else {
            return 'pending';
        }
    }

    public function getCommissionStatusBadgeClassAttribute()
    {
        $classes = [
            'no_commission' => 'bg-gray-100 text-gray-800 border-gray-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'paid' => 'bg-green-100 text-green-800 border-green-200',
        ];

        return $classes[$this->commission_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }

    public function getCommissionFormattedStatusAttribute()
    {
        $statusMap = [
            'no_commission' => 'No Commission',
            'pending' => 'Pending',
            'paid' => 'Paid',
        ];

        return $statusMap[$this->commission_status] ?? 'Unknown';
    }

    public function isOverdue()
    {
        return $this->payment_date && $this->payment_date->lt(now()->startOfDay());
    }

    public function isDueToday()
    {
        return $this->payment_date && $this->payment_date->eq(now()->startOfDay());
    }

    public function isUpcoming($days = 7)
    {
        return $this->payment_date && 
               $this->payment_date->gt(now()->startOfDay()) && 
               $this->payment_date->lte(now()->addDays($days)->endOfDay());
    }

    public function getDaysUntilDueAttribute()
    {
        if (!$this->payment_date) {
            return null;
        }
        
        return now()->startOfDay()->diffInDays($this->payment_date, false);
    }

    public function getRelatedCustomerAttribute()
    {
        if ($this->customer_id) {
            return $this->customer;
        } elseif ($this->potentialCustomer) {
            return $this->potentialCustomer;
        }
        
        return null;
    }

    public function getCustomerNameAttribute()
    {
        if ($this->customer) {
            return $this->customer->name;
        } elseif ($this->potentialCustomer) {
            return $this->potentialCustomer->potential_customer_name;
        }
        
        return 'Unknown Customer';
    }

    public function getPaymentMethodIconAttribute()
    {
        $icons = [
            'Cash' => '💰',
            'Bank Transfer' => '🏦',
            'Credit Card' => '💳',
            'Check' => '📝',
            'Digital Wallet' => '📱',
            'Other' => '💸',
        ];

        return $icons[$this->method] ?? '💸';
    }

    // Business logic methods
    public function calculateCommission(): float
    {
        if (!$this->customer_id) {
            $this->commission_earned = 0;
            $this->save();
            return 0;
        }
        
        $customer = $this->customer;
        $commissionRate = $customer->commission_rate ?? 0;
        
        $this->commission_earned = ($this->amount * $commissionRate) / 100;
        $this->save();
        
        return $this->commission_earned;
    }

    public function markCommissionAsPaid($paymentDate = null): bool
    {
        $this->commission_paid = $this->commission_earned;
        $this->commission_payment_date = $paymentDate ?? now();
        $this->commission_paid_status = true;
        
        return $this->save();
    }

    public function markCommissionAsUnpaid(): bool
    {
        $this->commission_paid = 0;
        $this->commission_payment_date = null;
        $this->commission_paid_status = false;
        
        return $this->save();
    }

    public function getRemainingCommissionAttribute(): float
    {
        return max(0, $this->commission_earned - $this->commission_paid);
    }

    public static function getTotalAmountByCustomer($customerId)
    {
        return self::where('customer_id', $customerId)
                    ->active()
                    ->sum('amount');
    }

    public static function getTotalCommissionByCustomer($customerId)
    {
        return self::where('customer_id', $customerId)
                    ->active()
                    ->sum('commission_earned');
    }

    public static function getPaidCommissionByCustomer($customerId)
    {
        return self::where('customer_id', $customerId)
                    ->active()
                    ->sum('commission_paid');
    }

    public static function getPaymentMethodsSummary($customerId = null)
    {
        $query = self::active();
        
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        
        return $query->select('method')
                    ->selectRaw('SUM(amount) as total_amount')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('method')
                    ->orderBy('total_amount', 'desc')
                    ->get();
    }
}