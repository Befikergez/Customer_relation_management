<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email', 
        'phone',
        'location',
        'remarks',
        'created_from_proposal_id',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'created_from_proposal_id');
    }
}