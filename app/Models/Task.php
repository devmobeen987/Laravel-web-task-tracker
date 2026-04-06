<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'article_url',
        'article_key',
        'article_keys',
        'article_links',
        'site_label',
        'type',
        'auto_detected_type',
        'status',
        'work_date',
        'time_entry_mode',
        'clock_in_at',
        'clock_out_at',
        'break_start_at',
        'break_end_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'article_keys' => 'array',
        'article_links' => 'array',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'break_start_at' => 'datetime',
        'break_end_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
