<?php

namespace App\Http\Controllers;

use App\Models\DailyTimeLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $rows = Task::where('user_id', $user->id)
            ->whereBetween('work_date', [$from, $to])
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $stats = [
            'currentUser' => [
                'new' => (int) ($rows['new'] ?? 0),
                'refurbished' => (int) ($rows['refurbished'] ?? 0),
            ],
        ];

        $todayLog = DailyTimeLog::query()
            ->where('user_id', $user->id)
            ->whereDate('work_date', now()->toDateString())
            ->first();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'todayLog' => $todayLog,
            'flashError' => session('error'),
        ]);
    }
}
