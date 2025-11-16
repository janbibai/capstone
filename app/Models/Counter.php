<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    protected $fillable = [
        'name',
        'counter_number',
        'service_id',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active'=>'boolean',
    ];

    public function service(){
        return $this->belongsTo(Service::class);
    }
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function currentQueue()
    {
        return $this->hasOne(Queue::class)->where('status', 'serving')->latest();
    }
}
