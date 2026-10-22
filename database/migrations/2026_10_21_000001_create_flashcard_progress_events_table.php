<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_progress_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_event_id');
            $table->foreignId('item_id')->constrained('flashcard_items')->cascadeOnDelete();
            $table->string('response_level', 10);
            $table->json('result');
            $table->timestamps();
            $table->unique(['user_id', 'client_event_id'], 'flashcard_progress_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_progress_events');
    }
};
