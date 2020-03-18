<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderWallet extends Model
{
    protected $fillable = [
        'provider_id', 'amount','transaction_id','transaction_alias','open_balance','close_balance','type'
    ];
}
