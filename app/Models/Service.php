<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'description',
        'estimated_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function queues(){
        return $this->hasMany(Queue::class);
    }

    public function counters()
    {
        return $this->hasMany(Counter::class);
    }
     public function queueNumbers()
    {
        return $this->hasMany(QueueNumber::class);
    }

    public function getCurrentQueueNumber($date = null)
    {
        $date = $date ?? now()->toDateString();
        return $this->queueNumbers()->where('date', $date)->first();
    }
}
