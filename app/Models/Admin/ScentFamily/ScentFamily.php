<?php

namespace App\Models\Admin\ScentFamily;

use Illuminate\Database\Eloquent\Model;

class ScentFamily extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}

