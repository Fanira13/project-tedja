<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MortgageRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'house_id', 'duration', 'bank_name', 'interest', 'interest_id', 
        'dp_total_amount', 'loan_total_amount', 'monthly_amount', 'dp_percentage',
        'status', 'documents', 'house_price', 'loan_interest_total_amount'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class, 'mortgage_request_id');
    }

    public function getRemainingLoanAmountAttribute()
    {
        if ($this->installments()->count() === 0){
            return $this->loan_interest_total_amount;
        }

        $totalPaid = $this->installments()
        ->where('is_paid', true)
        ->sum('sub_total_amount');

        return max($this->loan_interest_total_amount - $totalPaid, 0);
    }
}
