<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\StaffQueueController;
use App\Http\Controllers\DisplayBoardController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Patient Kiosk Routes (Public)
Route::prefix('kiosk')->group(function () {
    Route::get('/', [QueueController::class, 'kiosk'])->name('queue.kiosk');
    Route::post('/register', [QueueController::class, 'register'])->name('queue.register');
    Route::get('/ticket/{id}', [QueueController::class, 'ticket'])->name('queue.ticket');
    Route::get('/status/{id}', [QueueController::class, 'checkStatus'])->name('queue.status');
});

// Display Board Routes (Public)
Route::prefix('display')->group(function () {
    Route::get('/', [DisplayBoardController::class, 'index'])->name('display.board');
    Route::get('/data', [DisplayBoardController::class, 'getData'])->name('display.data');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Staff Queue Management
    Route::prefix('staff')->middleware('can:isStaff')->group(function () {
        Route::get('/queue', [StaffQueueController::class, 'dashboard'])->name('staff.queue');
        Route::post('/queue/call-next', [StaffQueueController::class, 'callNext'])->name('staff.queue.call');
        Route::post('/queue/{id}/serving', [StaffQueueController::class, 'startServing'])->name('staff.queue.serving');
        Route::post('/queue/{id}/complete', [StaffQueueController::class, 'complete'])->name('staff.queue.complete');
        Route::post('/queue/{id}/no-show', [StaffQueueController::class, 'noShow'])->name('staff.queue.noshow');
        Route::post('/counter/status', [StaffQueueController::class, 'updateCounterStatus'])->name('staff.counter.status');
    });

    // Admin Routes
    Route::prefix('admin')->middleware('can:isAdmin')->group(function () {
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
        Route::resource('counters', \App\Http\Controllers\Admin\CounterController::class);
        Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class);
    });
});

require __DIR__.'/auth.php';
