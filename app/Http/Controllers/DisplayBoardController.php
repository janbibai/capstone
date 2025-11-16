<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use App\Models\Counter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisplayBoardController extends Controller
{
    public function index()
    {
        $services = Service::with(['department'])
            ->where('is_active', true)
            ->get();

        return Inertia::render('Display/Board', [
            'services' => $services
        ]);
    }

    public function getData()
    {
        $currentQueues = Queue::with(['service', 'counter', 'patient'])
            ->whereIn('status', ['called', 'serving'])
            ->whereDate('created_at', today())
            ->orderBy('called_at', 'desc')
            ->take(10)
            ->get();

        $waitingCounts = Queue::where('status', 'waiting')
            ->whereDate('created_at', today())
            ->selectRaw('service_id, COUNT(*) as count')
            ->groupBy('service_id')
            ->pluck('count', 'service_id');

        $counters = Counter::with('service')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'currentQueues' => $currentQueues,
            'waitingCounts' => $waitingCounts,
            'counters' => $counters,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
