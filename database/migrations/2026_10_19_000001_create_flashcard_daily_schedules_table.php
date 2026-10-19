<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_daily_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('schedule_date');
            $table->string('kind', 24);
            $table->string('fingerprint', 64);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['user_id', 'schedule_date', 'kind'], 'flashcard_daily_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_daily_schedules');
    }
};
