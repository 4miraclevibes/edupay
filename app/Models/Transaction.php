<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_code',
        'status',
        'case',
        'total',
        'payment_id',
    ];

    public function payment(){
        return $this->belongsTo(Payment::class);
    }
    
    public function transactionDetail(){
        return $this->hasMany(TransactionDetail::class);
    }
}
