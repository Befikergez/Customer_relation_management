<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use App\Models\RoleCommissionSetting;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'profile_image',
        'commission_rate',
        'has_commission',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'commission_rate' => 'decimal:2',
        'has_commission' => 'boolean',
    ];

    protected $appends = [
        'profile_image_url',
        'effective_commission_rate',
        'can_edit_commission',
        'is_commission_eligible',
    ];

    // Relationships for commission system
    public function commissionRules()
    {
        return $this->hasMany(CommissionRule::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'commission_user_id');
    }

    /**
     * Check if user has commission capability based on roles
     */
    public function getIsCommissionEligibleAttribute()
    {
        return $this->has_commission;
    }

    /**
     * Check if user's commission rate is editable
     */
    public function getCanEditCommissionAttribute()
    {
        if (!$this->has_commission) {
            return false;
        }

        $userRoles = $this->roles->pluck('name')->toArray();
        
        return RoleCommissionSetting::whereIn('role_name', $userRoles)
            ->where('has_commission', true)
            ->where('is_commission_editable', true)
            ->exists();
    }

    /**
     * Get commission settings applied to this user
     */
    public function getAppliedCommissionSettings()
    {
        $userRoles = $this->roles->pluck('name')->toArray();
        
        return RoleCommissionSetting::whereIn('role_name', $userRoles)
            ->where('has_commission', true)
            ->get()
            ->map(function($setting) {
                return [
                    'role_name' => $setting->role_name,
                    'default_commission_rate' => $setting->default_commission_rate,
                    'is_commission_editable' => $setting->is_commission_editable,
                ];
            });
    }

    /**
     * Calculate effective commission rate based on various factors
     */
    public function getEffectiveCommissionRateAttribute()
    {
        // Start with base commission rate
        $commissionRate = $this->commission_rate ?? 0;

        // Apply commission rules if any exist
        if ($this->relationLoaded('commissionRules')) {
            $commissionRate = $this->calculateDynamicCommissionRate($commissionRate);
        }

        return $commissionRate;
    }

    /**
     * Calculate commission for a specific sale amount
     */
    public function calculateCommissionForSale($saleAmount, $productCategory = null, $customerType = null)
    {
        if (!$this->has_commission) {
            return 0;
        }

        $commissionRate = $this->effective_commission_rate;

        // Apply specific commission rules if provided
        if ($productCategory || $customerType) {
            $commissionRate = $this->getCommissionRateForConditions($saleAmount, $productCategory, $customerType);
        }

        // Apply tiered commission based on sale amount
        $commissionRate = $this->applyTieredCommission($saleAmount, $commissionRate);

        return ($saleAmount * $commissionRate) / 100;
    }

    /**
     * Get commission rate for specific conditions
     */
    private function getCommissionRateForConditions($saleAmount, $productCategory, $customerType)
    {
        $rule = $this->commissionRules()
            ->where(function ($query) use ($productCategory, $customerType) {
                if ($productCategory) {
                    $query->where('product_category', $productCategory)
                          ->orWhereNull('product_category');
                }
                if ($customerType) {
                    $query->where('customer_type', $customerType)
                          ->orWhereNull('customer_type');
                }
            })
            ->where('min_amount', '<=', $saleAmount)
            ->orderBy('priority', 'desc')
            ->orderBy('commission_rate', 'desc')
            ->first();

        return $rule ? $rule->commission_rate : $this->commission_rate;
    }

    /**
     * Apply tiered commission based on sale amount
     */
    private function applyTieredCommission($saleAmount, $baseRate)
    {
        $tiers = config('commission.tiers', []);
        
        foreach ($tiers as $tier) {
            if ($saleAmount >= $tier['min_amount'] && 
                (empty($tier['max_amount']) || $saleAmount <= $tier['max_amount'])) {
                return $tier['commission_rate'];
            }
        }

        return $baseRate;
    }

    /**
     * Calculate dynamic commission rate based on rules
     */
    private function calculateDynamicCommissionRate($baseRate)
    {
        $rules = $this->commissionRules()->get();
        
        foreach ($rules as $rule) {
            if ($rule->is_active && $rule->appliesToUser($this)) {
                // Apply rule based on type
                switch ($rule->type) {
                    case 'fixed':
                        return $rule->commission_rate;
                    case 'percentage_increase':
                        return $baseRate * (1 + ($rule->commission_rate / 100));
                    case 'percentage_decrease':
                        return $baseRate * (1 - ($rule->commission_rate / 100));
                    case 'override':
                        return $rule->commission_rate;
                }
            }
        }

        return $baseRate;
    }

    /**
     * Get monthly commission summary
     */
    public function getMonthlyCommissionSummary($year = null, $month = null)
    {
        if (!$this->has_commission) {
            return [
                'total_sales' => 0,
                'total_commission' => 0,
                'average_commission_rate' => 0,
                'sales_count' => 0
            ];
        }

        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $sales = $this->sales()
            ->whereYear('sale_date', $year)
            ->whereMonth('sale_date', $month)
            ->where('status', 'completed')
            ->get();

        $totalCommission = 0;
        $totalSales = 0;

        foreach ($sales as $sale) {
            $commission = $this->calculateCommissionForSale(
                $sale->amount,
                $sale->product_category,
                $sale->customer_type
            );
            
            $totalCommission += $commission;
            $totalSales += $sale->amount;
        }

        return [
            'total_sales' => $totalSales,
            'total_commission' => $totalCommission,
            'average_commission_rate' => $totalSales > 0 ? ($totalCommission / $totalSales) * 100 : 0,
            'sales_count' => $sales->count()
        ];
    }

    /**
     * Check if user has custom commission rules
     */
    public function hasCustomCommissionRules()
    {
        return $this->commissionRules()->exists();
    }

    /**
     * Get formatted commission rate
     */
    public function getCommissionRateFormattedAttribute()
    {
        if (!$this->has_commission) {
            return 'N/A';
        }
        return $this->effective_commission_rate . '%';
    }

    /**
     * Scope for users with commission
     */
    public function scopeWithCommission($query)
    {
        return $query->where('has_commission', true)
            ->whereNotNull('commission_rate')
            ->where('commission_rate', '>', 0);
    }

    /**
     * Scope for users eligible for commission
     */
    public function scopeCommissionEligible($query)
    {
        return $query->where('has_commission', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            if (filter_var($this->profile_image, FILTER_VALIDATE_URL)) {
                return $this->profile_image;
            }
            return asset('storage/' . $this->profile_image);
        }
        return null;
    }

    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name')->toArray();
    }
}