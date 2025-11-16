<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Patient;
use App\Models\Service;
use App\Models\QueueNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function kiosk()
    {
        $services = Service::with('department')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Queue/Kiosk', [
            'services' => $services
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'priority' => 'required|in:normal,senior,pwd,pregnant,emergency',
        ]);

        try {
            DB::beginTransaction();

            // Create or find patient
            $patient = Patient::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'patient_number' => 'P' . now()->format('Y') . '-' . str_pad(Patient::count() + 1, 4, '0', STR_PAD_LEFT),
            ]);

            // Get or create queue number for today
            $service = Service::findOrFail($validated['service_id']);
            $queueNumber = QueueNumber::firstOrCreate(
                [
                    'service_id' => $service->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'current_number' => 0,
                    'last_number' => 0,
                ]
            );

            $nextNumber = $queueNumber->getNextNumber();
            $queueNumberString = $service->code . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Create queue entry
            $queue = Queue::create([
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'queue_number' => $queueNumberString,
                'number_in_queue' => $nextNumber,
                'status' => 'waiting',
                'priority' => $validated['priority'],
            ]);

            DB::commit();

            return redirect()->route('queue.ticket', $queue->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to register. Please try again.']);
        }
    }

    public function ticket($id)
    {
        $queue = Queue::with(['patient', 'service.department'])
            ->findOrFail($id);

        $position = $queue->getPositionInQueue();

        return Inertia::render('Queue/Ticket', [
            'queue' => $queue,
            'position' => $position,
        ]);
    }

    public function checkStatus($id)
    {
        $queue = Queue::with(['service', 'counter'])
            ->findOrFail($id);

        return response()->json([
            'queue' => $queue,
            'position' => $queue->getPositionInQueue(),
        ]);
    }
}
