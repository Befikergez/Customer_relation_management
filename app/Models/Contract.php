<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'customer_id',
        'contract_title',
        'contract_description',
        'total_value',
        'start_date',
        'end_date',
        'payment_terms',
        'file',
        'status',
        'customer_signed_at',
        'customer_rejected_at',
    ];

    protected $attributes = [
        'status' => 'unsigned',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_value' => 'decimal:2',
        'customer_signed_at' => 'datetime',
        'customer_rejected_at' => 'datetime',
    ];

    public function proposal() {
        return $this->belongsTo(Proposal::class);
    }

    public function customer() {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Status scopes
    public function scopeUnsigned($query) {
        return $query->where('status', 'unsigned');
    }

    public function scopeSigned($query) {
        return $query->where('status', 'signed');
    }

    public function scopeRejected($query) {
        return $query->where('status', 'rejected');
    }

    // Status check methods
    public function isUnsigned() {
        return $this->status === 'unsigned';
    }

    public function isSigned() {
        return $this->status === 'signed';
    }

    public function isRejected() {
        return $this->status === 'rejected';
    }

    // Status badge class for UI
    public function getStatusBadgeClass() {
        $classes = [
            'unsigned' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'signed' => 'bg-green-100 text-green-800 border-green-200',
            'rejected' => 'bg-red-100 text-red-800 border-red-200',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }

    // Format status for display
    public function getFormattedStatus() {
        $statusMap = [
            'unsigned' => 'Unsigned',
            'signed' => 'Signed',
            'rejected' => 'Rejected',
        ];
        return $statusMap[$this->status] ?? $this->status;
    }
}