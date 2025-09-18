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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'partial'])->default('present');
            $table->text('remarks')->nullable();
            $table->boolean('is_half_day')->default(false);
            $table->enum('period_type', ['full_day', 'morning', 'afternoon', 'subject_wise'])->default('full_day');
            $table->integer('period_number')->nullable(); // For subject-wise attendance
            $table->timestamps();

            // Ensure one attendance record per student per day per class
            $table->unique(['student_id', 'class_id', 'attendance_date', 'subject_id', 'period_number'], 'unique_attendance_record');
            
            // Indexes for better performance
            $table->index(['attendance_date', 'class_id']);
            $table->index(['student_id', 'attendance_date']);
            $table->index(['academic_year_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};