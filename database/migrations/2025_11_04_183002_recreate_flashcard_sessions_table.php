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
        Schema::dropIfExists('flashcard_sessions');

        Schema::create('flashcard_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Discipline::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->unsignedInteger('total_cards')->default(0);
            $table->unsignedInteger('current_index')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('incorrect_count')->default(0);
            $table->unsignedTinyInteger('accuracy')->default(0);
            $table->json('note_ids');
            $table->timestamp('studied_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcard_sessions');
    }
};
