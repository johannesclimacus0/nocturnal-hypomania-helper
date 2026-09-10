<?php

use App\Enums\SystemTaskType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        DB::table('task_types')->insert(array_map(
            static fn (SystemTaskType $type): array => [
                'uuid' => (string) Str::uuid(),
                'user_id' => null,
                'name' => Str::headline($type->value),
                'slug' => $type->value,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            SystemTaskType::cases(),
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('task_types');
    }
};
