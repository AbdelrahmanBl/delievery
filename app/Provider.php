<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email','gender','mobile','device_type','password'
    ];
}
