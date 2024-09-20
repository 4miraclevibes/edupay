<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'user_id',
        'status',
        'case',
        'amount',
    ];

    public function transaction(){
        return $this->belongsTo(Transaction::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
