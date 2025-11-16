<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStats = [
            'total_patients' => Queue::whereDate('created_at', today())->count(),
            'completed' => Queue::whereDate('created_at', today())->where('status', 'completed')->count(),
            'waiting' => Queue::where('status', 'waiting')->count(),
            'average_wait_time' => $this->getAverageWaitTime(),
        ];

        $queuesByService = Queue::with('service')
            ->whereDate('created_at', today())
            ->selectRaw('service_id, status, COUNT(*) as count')
            ->groupBy('service_id', 'status')
            ->get()
            ->groupBy('service_id');

        $recentQueues = Queue::with(['patient', 'service', 'counter'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $serviceStats = Service::withCount([
            'queues as today_count' => function ($query) {
                $query->whereDate('created_at', today());
            },
            'queues as completed_count' => function ($query) {
                $query->whereDate('created_at', today())->where('status', 'completed');
            },
        ])->get();

        return Inertia::render('Dashboard', [
            'stats' => $todayStats,
            'queuesByService' => $queuesByService,
            'recentQueues' => $recentQueues,
            'serviceStats' => $serviceStats,
        ]);
    }

    private function getAverageWaitTime()
    {
        return Queue::whereDate('created_at', today())
            ->where('status', 'completed')
            ->whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_time')
            ->value('avg_time') ?? 0;
    }
}
