<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class user_devices extends Model
{
    protected $fillable = [
        'mobile', 'otp'
    ];
}
