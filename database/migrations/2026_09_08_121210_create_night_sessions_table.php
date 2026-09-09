<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });

        DB::statement('
            ALTER TABLE night_sessions
            ADD CONSTRAINT night_sessions_time_range_check
            CHECK (ended_at IS NULL OR ended_at >= started_at)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('night_sessions');
    }
};
