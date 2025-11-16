<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function services(){
        return $this->hasMany(Service::class);
    }
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
