<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_session_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_schedule_item_id')->constrained('student_schedule_items')->cascadeOnDelete();
            $table->foreignId('study_session_column_id')->constrained('study_session_columns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action_type', 20)->default('increment');
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['student_schedule_item_id', 'user_id', 'occurred_at']);
            $table->index(['study_session_column_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_session_actions');
    }
};
