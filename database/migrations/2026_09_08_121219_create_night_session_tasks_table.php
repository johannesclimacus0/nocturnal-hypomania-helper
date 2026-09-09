<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_session_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('night_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('selected');
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('selected_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamps();

            $table->unique(['night_session_id', 'task_id']);
            $table->index(['night_session_id', 'status']);
        });

        DB::statement("
            ALTER TABLE night_session_tasks
            ADD CONSTRAINT night_session_tasks_status_check
            CHECK (status IN ('selected', 'completed', 'skipped'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('night_session_tasks');
    }
};
