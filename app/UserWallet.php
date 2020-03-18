<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    protected $fillable = [
        'user_id', 'amount','transaction_id','transaction_alias','open_balance','close_balance','type'
    ];
}
