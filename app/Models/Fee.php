<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'user_id',
        'desc',
    ];
    public function feeDetails(){
        return $this->hasMany(FeeDetail::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
