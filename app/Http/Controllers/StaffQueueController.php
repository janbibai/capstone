<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Counter;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffQueueController extends Controller
{
    public function dashboard()
    {
        $staff = auth()->user()->staff;
        
        if (!$staff || !$staff->counter_id) {
            return Inertia::render('Staff/NoCounter');
        }

        $counter = Counter::with('service')->findOrFail($staff->counter_id);
        
        $currentQueue = Queue::with(['patient', 'service'])
            ->where('counter_id', $counter->id)
            ->where('status', 'serving')
            ->first();

        $waitingQueues = Queue::with(['patient', 'service'])
            ->where('service_id', $counter->service_id)
            ->where('status', 'waiting')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $todayStats = [
            'completed' => Queue::where('counter_id', $counter->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->count(),
            'waiting' => Queue::where('service_id', $counter->service_id)
                ->where('status', 'waiting')
                ->count(),
            'average_time' => Queue::where('counter_id', $counter->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->whereNotNull('serving_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, serving_at, completed_at)) as avg_time')
                ->value('avg_time') ?? 0,
        ];

        return Inertia::render('Staff/Dashboard', [
            'counter' => $counter,
            'currentQueue' => $currentQueue,
            'waitingQueues' => $waitingQueues,
            'stats' => $todayStats,
        ]);
    }

    public function callNext(Request $request)
    {
        $staff = auth()->user()->staff;
        $counter = Counter::findOrFail($staff->counter_id);

        // Complete current queue if any
        $current = Queue::where('counter_id', $counter->id)
            ->where('status', 'serving')
            ->first();

        if ($current) {
            $current->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // Get next in queue (priority first)
        $nextQueue = Queue::where('service_id', $counter->service_id)
            ->where('status', 'waiting')
            ->orderByRaw("FIELD(priority, 'emergency', 'senior', 'pwd', 'pregnant', 'normal')")
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextQueue) {
            return back()->with('message', 'No patients in queue');
        }

        $nextQueue->update([
            'status' => 'called',
            'counter_id' => $counter->id,
            'staff_id' => $staff->id,
            'called_at' => now(),
        ]);

        // Update counter status
        $counter->update(['status' => 'busy']);

        return back()->with('message', 'Called: ' . $nextQueue->queue_number);
    }

    public function startServing($id)
    {
        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'serving',
            'serving_at' => now(),
        ]);

        return back();
    }

    public function complete($id, Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('message', 'Queue completed successfully');
    }

    public function noShow($id)
    {
        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'no_show',
        ]);

        return back()->with('message', 'Marked as no show');
    }

    public function updateCounterStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,busy,closed',
        ]);

        $staff = auth()->user()->staff;
        $counter = Counter::findOrFail($staff->counter_id);
        
        $counter->update(['status' => $validated['status']]);

        return back();
    }
}