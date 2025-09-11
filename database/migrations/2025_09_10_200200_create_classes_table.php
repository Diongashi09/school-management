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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Grade 10A", "Class 5B"
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->integer('grade_level'); // e.g., 1, 2, 10, 11
            $table->string('section')->nullable(); // e.g., "A", "B", "Science"
            $table->integer('capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
