<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_type_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('estimated_time_minutes');
            $table->string('difficulty', 20);
            $table->string('status', 20)->default('active');
            $table->timestamp('last_selected_at')->nullable();
            $table->timestamp('last_skipped_at')->nullable();
            $table->timestamp('last_completed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
        DB::statement("
            ALTER TABLE tasks
            ADD CONSTRAINT tasks_difficulty_check
            CHECK (difficulty IN ('easy', 'normal', 'hard'))
        ");

        DB::statement("
            ALTER TABLE tasks
            ADD CONSTRAINT tasks_status_check
            CHECK (status IN ('active', 'completed', 'archived'))
        ");

        DB::statement('
            ALTER TABLE tasks
            ADD CONSTRAINT tasks_estimated_time_minutes_check
            CHECK (estimated_time_minutes > 0)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
