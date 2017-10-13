<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeviceToken extends Model
{   
    protected $fillable = [
        'platform', 'device_token', 'arn'
    ];
}
