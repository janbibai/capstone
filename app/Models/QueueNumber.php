<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueNumber extends Model
{
    protected $fillable = [
        'service_id',
        'date',
        'current_number',
        'last_number',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getNextNumber()
    {
        $this->increment('last_number');
        return $this->last_number;
    }
}   
