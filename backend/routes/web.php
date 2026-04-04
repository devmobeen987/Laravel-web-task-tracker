<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DailyTimeLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\TaskReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/dashboard/daily-time/start-shift', [DailyTimeLogController::class, 'startShift'])
        ->name('dashboard.daily-time.start-shift');
    Route::post('/dashboard/daily-time/end-shift', [DailyTimeLogController::class, 'endShift'])
        ->name('dashboard.daily-time.end-shift');
    Route::post('/dashboard/daily-time/start-break', [DailyTimeLogController::class, 'startBreak'])
        ->name('dashboard.daily-time.start-break');
    Route::post('/dashboard/daily-time/end-break', [DailyTimeLogController::class, 'endBreak'])
        ->name('dashboard.daily-time.end-break');
    Route::post('/dashboard/daily-time/reset', [DailyTimeLogController::class, 'resetDay'])
        ->name('dashboard.daily-time.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks/check-urls', [TaskController::class, 'checkUrls'])->name('tasks.check-urls');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

    Route::get('/reports/tasks', TaskReportController::class)->name('reports.tasks');
});

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
});

require __DIR__.'/auth.php';
