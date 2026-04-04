<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('time_entry_mode', 16)->default('manual')->after('work_date');
            $table->dateTime('clock_in_at')->nullable()->after('time_entry_mode');
            $table->dateTime('clock_out_at')->nullable()->after('clock_in_at');
            $table->dateTime('break_start_at')->nullable()->after('clock_out_at');
            $table->dateTime('break_end_at')->nullable()->after('break_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'time_entry_mode',
                'clock_in_at',
                'clock_out_at',
                'break_start_at',
                'break_end_at',
            ]);
        });
    }
};
