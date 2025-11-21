<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'profile_image', // Added profile_image
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = [
        'profile_image_url', // Add accessor to JSON
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the URL for the profile image
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            // If it's a full URL (from social login, etc), return as is
            if (filter_var($this->profile_image, FILTER_VALIDATE_URL)) {
                return $this->profile_image;
            }
            
            // Otherwise, assume it's stored in storage
            return asset('storage/' . $this->profile_image);
        }
        
        return null;
    }

    /**
     * Get the roles names as an array
     */
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name')->toArray();
    }
}