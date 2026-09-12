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
        Schema::table('night_sessions', function (Blueprint $table) {
            $table->unsignedSmallInteger('available_time_minutes');
            $table->string('difficulty', 20)->nullable();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_type_id')->nullable()->constrained()->nullOnDelete();
        });
        DB::statement("
            ALTER TABLE night_sessions
            ADD CONSTRAINT night_sessions_difficulty_check
            CHECK (difficulty IN ('easy', 'normal', 'hard'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('night_sessions', function (Blueprint $table) {
            DB::statement('
                ALTER TABLE night_sessions
                DROP CONSTRAINT IF EXISTS night_sessions_difficulty_check
            ');
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('task_type_id');

            $table->dropColumn(['available_time_minutes', 'difficulty']);
        });
    }
};
