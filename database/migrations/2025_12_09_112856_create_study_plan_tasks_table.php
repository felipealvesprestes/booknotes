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
        Schema::create('study_plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_plan_discipline_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('discipline_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('study_mode');
            $table->unsignedSmallInteger('quantity')->default(0);
            $table->string('unit_label')->default('items');
            $table->date('scheduled_for');
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'scheduled_for']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_plan_tasks');
    }
};
