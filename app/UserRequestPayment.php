<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRequestPayment extends Model
{
    protected $fillable = [
        'request_id','user_id','provider_id','fleet_id','promocode_id','payment_id','payment_mode','minute','hour','fixed','distance','commision','commision_per','fleet','fleet_per','discount','discount_per','tax','tax_per','govt','govt_per','tax_marketing','tax_marketing_tax_per','total','surge'
    ];
}
