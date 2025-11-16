<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'patient_id',
        'service_id',
        'counter_id',
        'staff_id',
        'queue_number',
        'number_in_queue',
        'status',
        'priority',
        'called_at',
        'serving_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'serving_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function getPositionInQueue()
    {
        return static::where('service_id', $this->service_id)
            ->where('status', 'waiting')
            ->where('created_at', '<', $this->created_at)
            ->count() + 1;
    }
}
