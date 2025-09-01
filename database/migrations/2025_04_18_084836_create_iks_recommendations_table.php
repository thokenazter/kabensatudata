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
        Schema::create('iks_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('indicator_code');
            $table->string('recommendation_type');
            $table->string('title');
            $table->text('description');
            $table->decimal('priority_score', 5, 2);
            $table->string('priority_level'); // High, Medium, Low
            $table->json('actions');
            $table->json('resources');
            $table->integer('expected_days_to_complete')->nullable();
            $table->string('difficulty_level')->nullable(); // Easy, Medium, Hard
            $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
            $table->date('target_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iks_recommendations');
    }
};
