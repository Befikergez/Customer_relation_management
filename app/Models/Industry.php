<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Industry extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function customers() {
        return $this->hasMany(Customer::class);
    }

    public function opportunities() {
        return $this->hasMany(Opportunity::class);
    }
}

