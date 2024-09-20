<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_id',
        'user_id',
        'total',
        'code',
        'subtotal',
        'status',
    ];

    public function service(){
        return $this->belongsTo(Service::class);
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function transaction(){
        return $this->hasOne(Transaction::class);
    }

    public function paymentFee(){
        return $this->hasMany(PaymentFee::class);
    }
}
