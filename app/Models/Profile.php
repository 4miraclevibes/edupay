<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nik',
        'phone',
        'alamat',
        'pekerjaan',
        'pendidikan_terakhir',
        'status_perkawinan',
        'tgl_lahir',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
