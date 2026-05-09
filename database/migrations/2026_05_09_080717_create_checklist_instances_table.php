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
        Schema::create('checklist_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('checklist_templates');
            $table->foreignId('auditor_id')->constrained('users');
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_instances');
    }
};
