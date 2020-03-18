<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'user_id', 'last_four', 'card_id','brand','is_default'
    ];
}
