<?php

use App\Models\Discipline;
use App\Models\Notebook;
use App\Models\User;
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
        Schema::create('simulated_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('scope_type', 20)->default('notebook');
            $table->foreignIdFor(Notebook::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Discipline::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('question_count');
            $table->unsignedInteger('answered_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('incorrect_count')->default(0);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('status', 50)->default('completed');
            $table->json('questions')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulated_exams');
    }
};
