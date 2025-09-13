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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Mid-term Exam", "Final Exam"
            $table->enum('exam_type', ['quiz', 'test', 'midterm', 'final', 'assignment', 'project']);
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 5, 2); // e.g., 100.00
            $table->decimal('passing_marks', 5, 2); // e.g., 40.00
            $table->date('exam_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
