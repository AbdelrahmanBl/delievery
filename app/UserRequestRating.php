<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRequestRating extends Model
{
    protected $fillable = [
        'request_id', 'user_id','provider_id','user_rating','user_comment'
    ];
}
