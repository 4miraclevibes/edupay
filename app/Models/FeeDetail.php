<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_id',
        'price',
        'fee_id',
    ];

    public function service(){
        return $this->belongsTo(Service::class);
    }
    public function fee(){
        return $this->belongsTo(Fee::class);
    }

}
