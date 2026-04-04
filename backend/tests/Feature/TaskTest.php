<?php

namespace Tests\Feature;

use App\Models\DailyTimeLog;
use App\Models\Task;
use App\Models\User;
use App\Support\ArticleUrlNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_create_daily_update_and_smart_detection_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/tasks', [
            'title' => 'First article',
            'work_date' => now()->toDateString(),
            'articles' => [
                [
                    'article_url' => 'https://example.com/article-1',
                    'type' => 'new',
                ],
            ],
        ])->assertRedirect('/tasks');

        $this->assertDatabaseCount('tasks', 1);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'article_url' => 'https://example.com/article-1',
            'article_key' => ArticleUrlNormalizer::key('https://example.com/article-1'),
            'auto_detected_type' => 'new',
        ]);

        $this->post('/tasks', [
            'title' => 'Refurbish article',
            'work_date' => now()->toDateString(),
            'articles' => [
                [
                    'article_url' => 'https://example.com/article-1',
                    'type' => 'refurbished',
                ],
            ],
        ])->assertRedirect('/tasks');

        $this->assertDatabaseCount('tasks', 2);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Refurbish article',
            'auto_detected_type' => 'refurbished',
        ]);
    }

    public function test_same_article_detected_when_only_date_path_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'article_url' => 'https://blog.test/2023/05/hello-world/',
            'article_key' => ArticleUrlNormalizer::key('https://blog.test/2023/05/hello-world/'),
            'type' => 'new',
            'auto_detected_type' => 'new',
            'work_date' => now()->toDateString(),
        ]);

        $this->post('/tasks', [
            'title' => 'Update',
            'work_date' => now()->toDateString(),
            'articles' => [
                [
                    'article_url' => 'https://blog.test/2025/01/hello-world/',
                    'type' => 'refurbished',
                ],
            ],
        ])->assertRedirect('/tasks');

        $this->assertDatabaseHas('tasks', [
            'article_url' => 'https://blog.test/2025/01/hello-world/',
            'auto_detected_type' => 'refurbished',
        ]);
    }

    public function test_multiple_links_are_saved_in_single_update_row(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/tasks', [
            'title' => 'Daily batch update',
            'work_date' => now()->toDateString(),
            'articles' => [
                ['article_url' => 'https://demo.test/2024/01/post-a', 'type' => 'new'],
                ['article_url' => 'https://demo.test/2023/11/post-b', 'type' => 'refurbished'],
            ],
        ])->assertRedirect('/tasks');

        $this->assertDatabaseCount('tasks', 1);
        $task = Task::first();
        $this->assertCount(2, $task->article_links);
        $this->assertSame('refurbished', $task->type);
    }

    public function test_task_copies_clock_times_from_daily_log_matching_work_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workDate = '2026-04-02';
        $clockIn = Carbon::parse('2026-04-02 09:15:00', config('app.timezone'));
        $clockOut = Carbon::parse('2026-04-02 17:30:00', config('app.timezone'));
        $breakStart = Carbon::parse('2026-04-02 12:00:00', config('app.timezone'));
        $breakEnd = Carbon::parse('2026-04-02 12:45:00', config('app.timezone'));

        DailyTimeLog::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'break_start_at' => $breakStart,
            'break_end_at' => $breakEnd,
        ]);

        $this->post('/tasks', [
            'title' => 'With dashboard times',
            'work_date' => $workDate,
            'articles' => [
                ['article_url' => 'https://example.com/a', 'type' => 'new'],
            ],
        ])->assertRedirect('/tasks');

        $task = Task::where('title', 'With dashboard times')->first();
        $this->assertNotNull($task);
        $this->assertSame('dashboard', $task->time_entry_mode);
        $this->assertTrue($task->clock_in_at->equalTo($clockIn));
        $this->assertTrue($task->clock_out_at->equalTo($clockOut));
        $this->assertTrue($task->break_start_at->equalTo($breakStart));
        $this->assertTrue($task->break_end_at->equalTo($breakEnd));
    }

    public function test_reports_endpoint_returns_summary(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'type' => 'new',
            'auto_detected_type' => 'new',
            'work_date' => now()->toDateString(),
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'type' => 'refurbished',
            'auto_detected_type' => 'refurbished',
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->get('/reports/tasks');
        $response->assertStatus(200);
    }
}
