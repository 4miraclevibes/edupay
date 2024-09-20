<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentFee extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_id',
        'name',
        'amount',
        'user_id',
    ];

    public function payment(){
        return $this->belongsTo(Payment::class);
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}
