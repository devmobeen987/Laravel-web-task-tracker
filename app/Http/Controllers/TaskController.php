<?php

namespace App\Http\Controllers;

use App\Models\DailyTimeLog;
use App\Models\Task;
use App\Support\ArticleUrlNormalizer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Task::with('user')
            ->where('user_id', $user->id)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->from, fn ($q, $from) => $q->whereDate('work_date', '>=', $from))
            ->when($request->to, fn ($q, $to) => $q->whereDate('work_date', '<=', $to))
            ->latest('work_date');

        return Inertia::render('Tasks/Index', [
            'tasks' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only('type', 'from', 'to'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tasks/Create');
    }

    /**
     * Return auto-detection per URL (normalized key ignores typical date path segments).
     */
    public function checkUrls(Request $request)
    {
        $validated = $request->validate([
            'urls' => ['required', 'array'],
            'urls.*' => ['required', 'string', 'max:2048'],
        ]);

        $items = [];
        foreach ($validated['urls'] as $raw) {
            $url = trim($raw);
            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $key = ArticleUrlNormalizer::key($url);
            $exists = Task::where('article_key', $key)
                ->orWhereJsonContains('article_keys', $key)
                ->exists();

            $items[] = [
                'url' => $url,
                'article_key' => $key,
                'auto_detected_type' => $exists ? 'refurbished' : 'new',
            ];
        }

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'work_date' => ['required', 'date'],
            'articles' => ['required', 'array', 'min:1'],
            'articles.*.article_url' => ['required', 'url', 'max:2048'],
            'articles.*.type' => ['required', 'in:new,refurbished'],
        ]);

        $log = DailyTimeLog::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('work_date', $data['work_date'])
            ->first();

        $prepared = [];
        $hasRefurbishedChoice = false;
        $hasAutoRefurbished = false;

        foreach ($data['articles'] as $article) {
            $url = $article['article_url'];
            $key = ArticleUrlNormalizer::key($url);
            $exists = Task::where('article_key', $key)
                ->orWhereJsonContains('article_keys', $key)
                ->exists();
            $auto = $exists ? 'refurbished' : 'new';

            if ($article['type'] === 'refurbished') {
                $hasRefurbishedChoice = true;
            }
            if ($auto === 'refurbished') {
                $hasAutoRefurbished = true;
            }

            $prepared[] = [
                'article_url' => $url,
                'article_key' => $key,
                'type' => $article['type'],
                'auto_detected_type' => $auto,
            ];
        }

        $first = $prepared[0];

        Task::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'article_url' => $first['article_url'],
            'article_key' => $first['article_key'],
            'article_keys' => array_values(array_unique(array_column($prepared, 'article_key'))),
            'article_links' => $prepared,
            'type' => $hasRefurbishedChoice ? 'refurbished' : 'new',
            'auto_detected_type' => $hasAutoRefurbished ? 'refurbished' : 'new',
            'status' => 'completed',
            'work_date' => $data['work_date'],
            'time_entry_mode' => $log ? 'dashboard' : null,
            'clock_in_at' => $log?->clock_in_at,
            'clock_out_at' => $log?->clock_out_at,
            'break_start_at' => $log?->break_start_at,
            'break_end_at' => $log?->break_end_at,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Daily update saved.');
    }
}
