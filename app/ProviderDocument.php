<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderDocument extends Model
{
    protected $fillable = [
        'provider_id', 'document_id','url','unique_id','status',
    ];
}
