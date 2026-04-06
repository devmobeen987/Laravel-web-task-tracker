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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('article_url');
            $table->string('site_label')->nullable();
            $table->enum('type', ['new', 'refurbished']);
            $table->enum('auto_detected_type', ['new', 'refurbished', 'unknown'])->default('unknown');
            $table->enum('status', ['pending', 'completed'])->default('completed');
            $table->date('work_date');
            $table->timestamps();

            $table->index('article_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
