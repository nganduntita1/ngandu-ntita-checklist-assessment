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
        Schema::create('checklist_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('checklist_instances')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('checklist_questions');
            $table->text('answer_value')->nullable();
            $table->timestamps();
            $table->unique(['instance_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_answers');
    }
};
