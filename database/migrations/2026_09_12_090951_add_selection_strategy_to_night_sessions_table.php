<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_sessions', function (Blueprint $table) {
            $table->string('selection_strategy', 40)->default('shortest');
        });
    }

    public function down(): void
    {
        Schema::table('night_sessions', function (Blueprint $table) {
            $table->dropColumn('selection_strategy');
        });
    }
};
