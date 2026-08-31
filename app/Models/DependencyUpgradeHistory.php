<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DependencyUpgradeHistory extends Model
{
    protected $fillable = [
        'package_name',
        'old_version',
        'new_version',
        'status',
        'message',
    ];
}