<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'image',
    ];
    
    public function topUpTransaction(){
        return $this->hasMany(TopUpTransaction::class);
}
}
