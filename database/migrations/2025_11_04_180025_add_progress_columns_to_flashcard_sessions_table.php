<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('flashcard_sessions')) {
            return;
        }

        Schema::table('flashcard_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('flashcard_sessions', 'current_index')) {
                $table->unsignedInteger('current_index')->default(0)->after('incorrect_count');
            }

            if (! Schema::hasColumn('flashcard_sessions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('studied_at');
            }

            if (! Schema::hasColumn('flashcard_sessions', 'accuracy')) {
                $table->unsignedTinyInteger('accuracy')->default(0)->after('current_index');
            }
        });

        DB::table('flashcard_sessions')->whereNull('correct_count')->update(['correct_count' => 0]);
        DB::table('flashcard_sessions')->whereNull('incorrect_count')->update(['incorrect_count' => 0]);
        DB::table('flashcard_sessions')->whereNull('accuracy')->update(['accuracy' => 0]);
        DB::table('flashcard_sessions')->whereNull('current_index')->update(['current_index' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('flashcard_sessions')) {
            return;
        }

        Schema::table('flashcard_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('flashcard_sessions', 'current_index')) {
                $table->dropColumn('current_index');
            }

            if (Schema::hasColumn('flashcard_sessions', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

            if (Schema::hasColumn('flashcard_sessions', 'accuracy')) {
                $table->dropColumn('accuracy');
            }
        });
    }
};
