<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleCommissionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'has_commission',
        'default_commission_rate',
        'is_commission_editable'
    ];

    protected $casts = [
        'has_commission' => 'boolean',
        'is_commission_editable' => 'boolean',
        'default_commission_rate' => 'decimal:2'
    ];
}