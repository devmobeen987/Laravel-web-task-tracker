<?php

namespace App\Http\Controllers;

use App\Models\DailyTimeLog;
use Illuminate\Http\Request;

class DailyTimeLogController extends Controller
{
    private function todayLog(Request $request): ?DailyTimeLog
    {
        return DailyTimeLog::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('work_date', now()->toDateString())
            ->first();
    }

    public function startShift(Request $request)
    {
        $log = DailyTimeLog::firstOrNew([
            'user_id' => $request->user()->id,
            'work_date' => now()->toDateString(),
        ]);

        if ($log->clock_in_at) {
            return redirect()->route('dashboard')->with('error', 'You already started your day.');
        }

        $log->clock_in_at = now();
        $log->save();

        return redirect()->route('dashboard');
    }

    public function endShift(Request $request)
    {
        $log = $this->todayLog($request);

        if (! $log || ! $log->clock_in_at) {
            return redirect()->route('dashboard')->with('error', 'Start your day first.');
        }

        if ($log->clock_out_at) {
            return redirect()->route('dashboard')->with('error', 'Day already ended.');
        }

        if ($log->break_start_at && ! $log->break_end_at) {
            return redirect()->route('dashboard')->with('error', 'End your break before ending the day.');
        }

        $log->clock_out_at = now();
        $log->save();

        return redirect()->route('dashboard');
    }

    public function startBreak(Request $request)
    {
        $log = $this->todayLog($request);

        if (! $log || ! $log->clock_in_at || $log->clock_out_at) {
            return redirect()->route('dashboard')->with('error', 'Clock in and stay on shift to start a break.');
        }

        if ($log->break_start_at) {
            return redirect()->route('dashboard')->with('error', 'Break already started for today.');
        }

        $log->break_start_at = now();
        $log->save();

        return redirect()->route('dashboard');
    }

    public function endBreak(Request $request)
    {
        $log = $this->todayLog($request);

        if (! $log || ! $log->break_start_at || $log->break_end_at) {
            return redirect()->route('dashboard')->with('error', 'No active break to end.');
        }

        $log->break_end_at = now();
        $log->save();

        return redirect()->route('dashboard');
    }

    public function resetDay(Request $request)
    {
        $log = $this->todayLog($request);

        if ($log) {
            $log->delete();
        }

        return redirect()->route('dashboard');
    }
}
