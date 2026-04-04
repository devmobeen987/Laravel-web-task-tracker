<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $rows = Task::with('user')
            ->whereBetween('work_date', [$from, $to])
            ->selectRaw('user_id, type, COUNT(*) as total')
            ->groupBy('user_id', 'type')
            ->get();

        $summary = $rows->groupBy('user_id')->map(function ($items) {
            $user = $items->first()->user;

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'new' => (int) ($items->firstWhere('type', 'new')->total ?? 0),
                'refurbished' => (int) ($items->firstWhere('type', 'refurbished')->total ?? 0),
            ];
        })->values();

        return Inertia::render('Reports/Overview', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
        ]);
    }
}
