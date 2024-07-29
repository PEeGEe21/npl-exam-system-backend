<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = ["id"];

    public static $Admin = 1;

    public static $AdminRole = [
        'admin',
        'adminsa',
    ];

    public function scopeGetRoleByName($query, $name)
    {
        return $query->where('name', '=', $name)->first();
    }
}

